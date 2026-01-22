<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\StockEntry;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Display the reports dashboard with overview statistics.
     */
    public function index()
    {
        // Stock overview
        $stockStats = Product::selectRaw('
            COUNT(*) as total_products,
            SUM(quantity) as total_units,
            SUM(quantity * purchase_price) as stock_value_purchase,
            SUM(quantity * sale_price) as stock_value_sale,
            SUM(CASE WHEN quantity <= 5 THEN 1 ELSE 0 END) as low_stock_count,
            SUM(CASE WHEN quantity = 0 THEN 1 ELSE 0 END) as out_of_stock_count
        ')->first();

        // Stock entries this month
        $stockEntriesThisMonth = StockEntry::whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('quantity');

        // Stock adjustments this month
        $adjustmentsThisMonth = StockAdjustment::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->selectRaw('
                SUM(CASE WHEN quantity > 0 THEN quantity ELSE 0 END) as added,
                SUM(CASE WHEN quantity < 0 THEN ABS(quantity) ELSE 0 END) as removed
            ')->first();

        // Categories distribution
        $categoriesStats = Category::withCount('products')
            ->orderByDesc('products_count')
            ->limit(5)
            ->get();

        // Top suppliers by entries this month
        $topSuppliers = Supplier::select('suppliers.id', 'suppliers.name')
            ->join('stock_entries', 'suppliers.id', '=', 'stock_entries.supplier_id')
            ->whereMonth('stock_entries.date', now()->month)
            ->whereYear('stock_entries.date', now()->year)
            ->selectRaw('SUM(stock_entries.quantity) as total_quantity')
            ->groupBy('suppliers.id', 'suppliers.name')
            ->orderByDesc('total_quantity')
            ->limit(5)
            ->get();

        return view('reports.index', compact(
            'stockStats',
            'stockEntriesThisMonth',
            'adjustmentsThisMonth',
            'categoriesStats',
            'topSuppliers'
        ));
    }

    /**
     * Display products with low stock.
     */
    public function lowStock(Request $request)
    {
        $threshold = $request->input('threshold', 5);

        $query = Product::with('category:id,name')
            ->where('quantity', '<=', $threshold);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $search = str_replace(['%', '_'], ['\\%', '\\_'], $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        $sortBy = $request->input('sort', 'quantity');
        $sortDir = $request->input('dir', 'asc');

        if (in_array($sortBy, ['quantity', 'name', 'sku'])) {
            $query->orderBy($sortBy, $sortDir === 'desc' ? 'desc' : 'asc');
        } else {
            $query->orderBy('quantity', 'asc');
        }

        $products = $query->paginate(20)->withQueryString();
        $categories = Category::orderBy('name')->get(['id', 'name']);

        // Summary stats
        $summary = Product::where('quantity', '<=', $threshold)
            ->selectRaw('
                COUNT(*) as count,
                SUM(CASE WHEN quantity = 0 THEN 1 ELSE 0 END) as out_of_stock,
                SUM(quantity * purchase_price) as value_at_risk
            ')->first();

        return view('reports.low-stock', compact('products', 'categories', 'threshold', 'summary'));
    }

    /**
     * Display stock entries by supplier.
     */
    public function stockEntriesBySupplier(Request $request)
    {
        $query = Supplier::select('suppliers.*')
            ->withCount(['stockEntries as total_entries'])
            ->withSum('stockEntries as total_quantity', 'quantity');

        if ($request->filled('date_from')) {
            $query->whereHas('stockEntries', function ($q) use ($request) {
                $q->whereDate('date', '>=', $request->date_from);
            });
        }

        if ($request->filled('date_to')) {
            $query->whereHas('stockEntries', function ($q) use ($request) {
                $q->whereDate('date', '<=', $request->date_to);
            });
        }

        if ($request->filled('search')) {
            $search = str_replace(['%', '_'], ['\\%', '\\_'], $request->search);
            $query->where('name', 'like', "%{$search}%");
        }

        $suppliers = $query->orderByDesc('total_quantity')
            ->paginate(15)
            ->withQueryString();

        // If a specific supplier is selected, show their entries
        $selectedSupplier = null;
        $supplierEntries = null;

        if ($request->filled('supplier_id')) {
            $selectedSupplier = Supplier::find($request->supplier_id);
            if ($selectedSupplier) {
                $entriesQuery = StockEntry::with('product:id,name,sku')
                    ->where('supplier_id', $selectedSupplier->id);

                if ($request->filled('date_from')) {
                    $entriesQuery->whereDate('date', '>=', $request->date_from);
                }
                if ($request->filled('date_to')) {
                    $entriesQuery->whereDate('date', '<=', $request->date_to);
                }

                $supplierEntries = $entriesQuery->orderByDesc('date')
                    ->paginate(15, ['*'], 'entries_page')
                    ->withQueryString();
            }
        }

        // Summary
        $summary = StockEntry::selectRaw('
            COUNT(*) as total_entries,
            SUM(quantity) as total_quantity,
            COUNT(DISTINCT supplier_id) as unique_suppliers
        ');

        if ($request->filled('date_from')) {
            $summary->whereDate('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $summary->whereDate('date', '<=', $request->date_to);
        }

        $summary = $summary->first();

        return view('reports.stock-entries-by-supplier', compact(
            'suppliers',
            'selectedSupplier',
            'supplierEntries',
            'summary'
        ));
    }

    /**
     * Display stock adjustments history.
     */
    public function stockAdjustments(Request $request)
    {
        // Summary by type
        $summaryByType = StockAdjustment::select('type')
            ->selectRaw('
                COUNT(*) as count,
                SUM(ABS(quantity)) as total_quantity
            ')
            ->groupBy('type')
            ->get()
            ->keyBy('type');

        // Filter adjustments
        $query = StockAdjustment::with(['product:id,name,sku', 'user:id,name']);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        $adjustments = $query->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $products = Product::orderBy('name')->get(['id', 'name', 'sku']);
        $types = StockAdjustment::TYPES;

        // Period summary
        $periodSummary = (clone $query)->selectRaw('
            SUM(CASE WHEN quantity > 0 THEN quantity ELSE 0 END) as added,
            SUM(CASE WHEN quantity < 0 THEN ABS(quantity) ELSE 0 END) as removed,
            COUNT(*) as total_adjustments
        ')->first();

        return view('reports.stock-adjustments', compact(
            'adjustments',
            'summaryByType',
            'products',
            'types',
            'periodSummary'
        ));
    }
}

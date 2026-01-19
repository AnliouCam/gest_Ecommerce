<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockEntry;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockEntryController extends Controller
{
    /**
     * Display a listing of stock entries.
     */
    public function index(Request $request)
    {
        $query = StockEntry::with(['supplier:id,name', 'product:id,name,sku']);

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = str_replace(['%', '_'], ['\\%', '\\_'], $request->search);
            $query->where(function ($q) use ($search) {
                $q->whereHas('supplier', function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%");
                })->orWhereHas('product', function ($pq) use ($search) {
                    $pq->where('name', 'like', "%{$search}%")
                       ->orWhere('sku', 'like', "%{$search}%");
                });
            });
        }

        $stockEntries = $query->orderByDesc('date')
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $suppliers = Supplier::orderBy('name')->get(['id', 'name']);
        $products = Product::orderBy('name')->get(['id', 'name', 'sku']);

        return view('stock-entries.index', compact('stockEntries', 'suppliers', 'products'));
    }

    /**
     * Show the form for creating a new stock entry.
     */
    public function create()
    {
        $suppliers = Supplier::orderBy('name')->get(['id', 'name']);
        $products = Product::orderBy('name')->get(['id', 'name', 'sku', 'quantity']);

        return view('stock-entries.create', compact('suppliers', 'products'));
    }

    /**
     * Store a newly created stock entry.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'date' => ['required', 'date', 'before_or_equal:today'],
        ]);

        DB::transaction(function () use ($validated) {
            StockEntry::create($validated);

            Product::where('id', $validated['product_id'])
                ->increment('quantity', $validated['quantity']);
        });

        return redirect()->route('gerant.stock-entries.index')
            ->with('success', 'Entree de stock enregistree avec succes. Le stock du produit a ete mis a jour.');
    }

    /**
     * Display the specified stock entry.
     */
    public function show(StockEntry $stockEntry)
    {
        $stockEntry->load(['supplier', 'product']);

        return view('stock-entries.show', compact('stockEntry'));
    }

    /**
     * Show the form for editing the specified stock entry.
     */
    public function edit(StockEntry $stockEntry)
    {
        $suppliers = Supplier::orderBy('name')->get(['id', 'name']);
        $products = Product::orderBy('name')->get(['id', 'name', 'sku', 'quantity']);
        $stockEntry->load(['supplier', 'product']);

        return view('stock-entries.edit', compact('stockEntry', 'suppliers', 'products'));
    }

    /**
     * Update the specified stock entry.
     */
    public function update(Request $request, StockEntry $stockEntry)
    {
        $validated = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'date' => ['required', 'date', 'before_or_equal:today'],
        ]);

        DB::transaction(function () use ($validated, $stockEntry) {
            $oldProductId = $stockEntry->product_id;
            $oldQuantity = $stockEntry->quantity;
            $newProductId = $validated['product_id'];
            $newQuantity = $validated['quantity'];

            if ($oldProductId === (int) $newProductId) {
                $difference = $newQuantity - $oldQuantity;
                if ($difference !== 0) {
                    Product::where('id', $oldProductId)
                        ->increment('quantity', $difference);
                }
            } else {
                Product::where('id', $oldProductId)
                    ->decrement('quantity', $oldQuantity);

                Product::where('id', $newProductId)
                    ->increment('quantity', $newQuantity);
            }

            $stockEntry->update($validated);
        });

        return redirect()->route('gerant.stock-entries.index')
            ->with('success', 'Entree de stock mise a jour avec succes.');
    }

    /**
     * Remove the specified stock entry.
     */
    public function destroy(StockEntry $stockEntry)
    {
        $product = Product::find($stockEntry->product_id);

        if ($product && $product->quantity < $stockEntry->quantity) {
            return back()->with('error', 'Impossible de supprimer cette entree car le stock actuel du produit est insuffisant.');
        }

        DB::transaction(function () use ($stockEntry) {
            Product::where('id', $stockEntry->product_id)
                ->decrement('quantity', $stockEntry->quantity);

            $stockEntry->delete();
        });

        return redirect()->route('gerant.stock-entries.index')
            ->with('success', 'Entree de stock supprimee avec succes. Le stock du produit a ete mis a jour.');
    }
}

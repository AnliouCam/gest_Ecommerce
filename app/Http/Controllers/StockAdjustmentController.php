<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockAdjustment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StockAdjustmentController extends Controller
{
    /**
     * Display a listing of stock adjustments.
     */
    public function index(Request $request)
    {
        $query = StockAdjustment::with(['product:id,name,sku', 'user:id,name']);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = str_replace(['%', '_'], ['\\%', '\\_'], $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('reason', 'like', "%{$search}%")
                  ->orWhereHas('product', function ($pq) use ($search) {
                      $pq->where('name', 'like', "%{$search}%")
                         ->orWhere('sku', 'like', "%{$search}%");
                  })
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $stockAdjustments = $query->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $products = Product::orderBy('name')->get(['id', 'name', 'sku']);
        $types = StockAdjustment::TYPES;

        return view('stock-adjustments.index', compact('stockAdjustments', 'products', 'types'));
    }

    /**
     * Show the form for creating a new stock adjustment.
     */
    public function create()
    {
        $products = Product::orderBy('name')->get(['id', 'name', 'sku', 'quantity']);
        $types = StockAdjustment::TYPE_LABELS;

        return view('stock-adjustments.create', compact('products', 'types'));
    }

    /**
     * Store a newly created stock adjustment.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'type' => ['required', Rule::in(StockAdjustment::TYPES)],
            'quantity' => ['required', 'integer', 'not_in:0'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $product = Product::findOrFail($validated['product_id']);

        // Pour perte et casse, la quantité doit être négative (retrait du stock)
        $quantity = (int) $validated['quantity'];
        if (in_array($validated['type'], ['perte', 'casse']) && $quantity > 0) {
            $quantity = -$quantity;
        }

        // Vérifier que le stock ne devient pas négatif
        if ($quantity < 0 && $product->quantity + $quantity < 0) {
            return back()->withInput()->withErrors([
                'quantity' => 'La quantite a retirer (' . abs($quantity) . ') depasse le stock disponible (' . $product->quantity . ').',
            ]);
        }

        DB::transaction(function () use ($validated, $quantity, $product) {
            StockAdjustment::create([
                'product_id' => $validated['product_id'],
                'user_id' => Auth::id(),
                'type' => $validated['type'],
                'quantity' => $quantity,
                'reason' => $validated['reason'] ?? null,
            ]);

            $product->increment('quantity', $quantity);
        });

        $action = $quantity > 0 ? 'ajoute' : 'retire';
        return redirect()->route('gerant.stock-adjustments.index')
            ->with('success', 'Ajustement enregistre. ' . abs($quantity) . ' unite(s) ' . $action . '(s) du stock.');
    }

    /**
     * Display the specified stock adjustment.
     */
    public function show(StockAdjustment $stockAdjustment)
    {
        $stockAdjustment->load(['product', 'user']);

        return view('stock-adjustments.show', compact('stockAdjustment'));
    }
}

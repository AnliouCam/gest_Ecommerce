<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    /**
     * Display a listing of products.
     */
    public function index(Request $request)
    {
        $query = Product::with('category');

        // Search by name or SKU (escape LIKE special characters)
        if ($request->filled('search')) {
            $search = str_replace(['%', '_'], ['\\%', '\\_'], $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Filter by stock status
        if ($request->filled('stock')) {
            if ($request->stock === 'low') {
                $query->whereColumn('quantity', '<=', 'stock_alert');
            } elseif ($request->stock === 'out') {
                $query->where('quantity', 0);
            }
        }

        $products = $query->orderBy('name')->paginate(15)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('products.index', compact('products', 'categories'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('products.create', compact('categories'));
    }

    /**
     * Store a newly created product.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:50', 'unique:products,sku'],
            'category_id' => ['required', 'exists:categories,id'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['required', 'numeric', 'min:0', 'gte:purchase_price'],
            'quantity' => ['required', 'integer', 'min:0'],
            'max_discount' => ['required', 'integer', 'min:0', 'max:20'],
            'stock_alert' => ['required', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        return DB::transaction(function () use ($request, $validated) {
            if ($request->hasFile('image')) {
                $validated['image'] = $request->file('image')->store('products', 'public');
            }

            Product::create($validated);

            return redirect()->route('gerant.products.index')
                ->with('success', 'Produit cree avec succes.');
        });
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product)
    {
        $product->load([
            'category',
            'stockEntries' => fn($q) => $q->with('supplier')->orderByDesc('date')->limit(10),
            'saleItems' => fn($q) => $q->with('sale')->orderByDesc('created_at')->limit(10),
        ]);

        // Pre-calculate stats with SQL aggregates instead of loading all records
        $stats = [
            'total_sold' => $product->saleItems()->sum('quantity'),
            'total_entries' => $product->stockEntries()->sum('quantity'),
            'sales_count' => $product->saleItems()->count(),
        ];

        return view('products.show', compact('product', 'stats'));
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();
        return view('products.edit', compact('product', 'categories'));
    }

    /**
     * Update the specified product.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:50', Rule::unique('products')->ignore($product->id)],
            'category_id' => ['required', 'exists:categories,id'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['required', 'numeric', 'min:0', 'gte:purchase_price'],
            'quantity' => ['required', 'integer', 'min:0'],
            'max_discount' => ['required', 'integer', 'min:0', 'max:20'],
            'stock_alert' => ['required', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        return DB::transaction(function () use ($request, $validated, $product) {
            if ($request->hasFile('image')) {
                if ($product->image) {
                    Storage::disk('public')->delete($product->image);
                }
                $validated['image'] = $request->file('image')->store('products', 'public');
            }

            $product->update($validated);

            return redirect()->route('gerant.products.index')
                ->with('success', 'Produit mis a jour avec succes.');
        });
    }

    /**
     * Remove the specified product.
     */
    public function destroy(Product $product)
    {
        if ($product->saleItems()->exists()) {
            return back()->with('error', 'Impossible de supprimer ce produit car il a des ventes associees.');
        }

        return DB::transaction(function () use ($product) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }

            $product->delete();

            return redirect()->route('gerant.products.index')
                ->with('success', 'Produit supprime avec succes.');
        });
    }
}

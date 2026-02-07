<?php

namespace App\Http\Controllers\Vendeur;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of products (read-only for vendeur).
     */
    public function index(Request $request)
    {
        $query = Product::with('category');

        if ($request->filled('search')) {
            $search = str_replace(['%', '_'], ['\\%', '\\_'], $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('stock')) {
            if ($request->stock === 'low') {
                $query->whereRaw('quantity <= stock_alert AND quantity > 0');
            } elseif ($request->stock === 'out') {
                $query->where('quantity', 0);
            } elseif ($request->stock === 'available') {
                $query->where('quantity', '>', 0);
            }
        }

        $products = $query->orderBy('name')->paginate(15)->withQueryString();
        $categories = Category::orderBy('name')->get(['id', 'name']);

        return view('vendeur.products.index', compact('products', 'categories'));
    }

    /**
     * Display the specified product (read-only for vendeur).
     */
    public function show(Product $product)
    {
        $product->load('category');

        return view('vendeur.products.show', compact('product'));
    }
}

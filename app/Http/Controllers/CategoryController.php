<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories.
     */
    public function index(Request $request)
    {
        $query = Category::withCount('products');

        if ($request->filled('search')) {
            $search = str_replace(['%', '_'], ['\\%', '\\_'], $request->search);
            $query->where('name', 'like', "%{$search}%");
        }

        $categories = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new category.
     */
    public function create()
    {
        return view('categories.create');
    }

    /**
     * Store a newly created category.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name'],
        ]);

        Category::create($validated);

        return redirect()->route('gerant.categories.index')
            ->with('success', 'Categorie creee avec succes.');
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(Category $category)
    {
        $category->loadCount('products');

        return view('categories.edit', compact('category'));
    }

    /**
     * Update the specified category.
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name,' . $category->id],
        ]);

        $category->update($validated);

        return redirect()->route('gerant.categories.index')
            ->with('success', 'Categorie mise a jour avec succes.');
    }

    /**
     * Remove the specified category.
     */
    public function destroy(Category $category)
    {
        if ($category->products()->exists()) {
            return back()->with('error', 'Impossible de supprimer cette categorie car elle contient des produits.');
        }

        $category->delete();

        return redirect()->route('gerant.categories.index')
            ->with('success', 'Categorie supprimee avec succes.');
    }
}

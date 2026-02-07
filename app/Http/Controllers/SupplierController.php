<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * Display a listing of suppliers.
     */
    public function index(Request $request)
    {
        $query = Supplier::withCount('stockEntries');

        if ($request->filled('search')) {
            $search = str_replace(['%', '_'], ['\\%', '\\_'], $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $suppliers = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('suppliers.index', compact('suppliers'));
    }

    /**
     * Show the form for creating a new supplier.
     */
    public function create()
    {
        return view('suppliers.create');
    }

    /**
     * Store a newly created supplier.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
        ]);

        Supplier::create($validated);

        return redirect()->route('gerant.suppliers.index')
            ->with('success', 'Fournisseur cree avec succes.');
    }

    /**
     * Display the specified supplier with stock entries history.
     */
    public function show(Supplier $supplier)
    {
        $supplier->loadCount('stockEntries');

        $stockEntries = $supplier->stockEntries()
            ->with('product:id,name,sku')
            ->orderByDesc('date')
            ->paginate(15);

        return view('suppliers.show', compact('supplier', 'stockEntries'));
    }

    /**
     * Show the form for editing the specified supplier.
     */
    public function edit(Supplier $supplier)
    {
        $supplier->loadCount('stockEntries');

        return view('suppliers.edit', compact('supplier'));
    }

    /**
     * Update the specified supplier.
     */
    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
        ]);

        $supplier->update($validated);

        return redirect()->route('gerant.suppliers.index')
            ->with('success', 'Fournisseur mis a jour avec succes.');
    }

    /**
     * Remove the specified supplier.
     */
    public function destroy(Supplier $supplier)
    {
        if ($supplier->stockEntries()->exists()) {
            return back()->with('error', 'Impossible de supprimer ce fournisseur car il a des entrees de stock associees.');
        }

        $supplier->delete();

        return redirect()->route('gerant.suppliers.index')
            ->with('success', 'Fournisseur supprime avec succes.');
    }
}

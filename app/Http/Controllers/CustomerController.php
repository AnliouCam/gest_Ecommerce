<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers.
     */
    public function index(Request $request)
    {
        $query = Customer::withCount('sales');

        if ($request->filled('search')) {
            $search = str_replace(['%', '_'], ['\\%', '\\_'], $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $customers = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('customers.index', compact('customers'));
    }

    /**
     * Show the form for creating a new customer.
     */
    public function create()
    {
        return view('customers.create');
    }

    /**
     * Store a newly created customer.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
        ], [
            'name.required' => 'Le nom est obligatoire.',
            'phone.required' => 'Le telephone est obligatoire.',
            'email.email' => 'L\'email doit etre une adresse valide.',
        ]);

        Customer::create($validated);

        return redirect()->route('ventes.customers.index')
            ->with('success', 'Client cree avec succes.');
    }

    /**
     * Display the specified customer with purchase history.
     */
    public function show(Customer $customer)
    {
        $customer->loadCount('sales');

        $sales = $customer->sales()
            ->with('user:id,name')
            ->orderByDesc('created_at')
            ->paginate(15);

        $stats = [
            'total_spent' => $customer->sales()->where('status', 'completed')->sum('total'),
            'total_orders' => $customer->sales()->where('status', 'completed')->count(),
            'cancelled_orders' => $customer->sales()->where('status', 'cancelled')->count(),
        ];

        return view('customers.show', compact('customer', 'sales', 'stats'));
    }

    /**
     * Show the form for editing the specified customer.
     */
    public function edit(Customer $customer)
    {
        $customer->loadCount('sales');

        return view('customers.edit', compact('customer'));
    }

    /**
     * Update the specified customer.
     */
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
        ], [
            'name.required' => 'Le nom est obligatoire.',
            'phone.required' => 'Le telephone est obligatoire.',
            'email.email' => 'L\'email doit etre une adresse valide.',
        ]);

        $customer->update($validated);

        return redirect()->route('ventes.customers.index')
            ->with('success', 'Client mis a jour avec succes.');
    }

    /**
     * Remove the specified customer.
     */
    public function destroy(Customer $customer)
    {
        if ($customer->sales()->exists()) {
            return back()->with('error', 'Impossible de supprimer ce client car il a des ventes associees.');
        }

        $customer->delete();

        return redirect()->route('ventes.customers.index')
            ->with('success', 'Client supprime avec succes.');
    }
}

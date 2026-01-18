@extends('layouts.app')

@section('title', $supplier->name . ' - Historique')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <a href="{{ route('gerant.suppliers.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-primary-600 transition-colors">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Retour aux fournisseurs
            </a>
            <h1 class="mt-2 text-2xl font-bold text-gray-900">{{ $supplier->name }}</h1>
            <p class="mt-1 text-sm text-gray-500">Historique des approvisionnements</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route('gerant.suppliers.edit', $supplier) }}" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Modifier
            </a>
        </div>
    </div>

    <!-- Supplier Info Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <h3 class="text-sm font-medium text-gray-500">Telephone</h3>
                <p class="mt-1 text-sm text-gray-900">
                    @if($supplier->phone)
                        <a href="tel:{{ $supplier->phone }}" class="text-primary-600 hover:text-primary-700">{{ $supplier->phone }}</a>
                    @else
                        <span class="text-gray-400">Non renseigne</span>
                    @endif
                </p>
            </div>
            <div>
                <h3 class="text-sm font-medium text-gray-500">Email</h3>
                <p class="mt-1 text-sm text-gray-900">
                    @if($supplier->email)
                        <a href="mailto:{{ $supplier->email }}" class="text-primary-600 hover:text-primary-700">{{ $supplier->email }}</a>
                    @else
                        <span class="text-gray-400">Non renseigne</span>
                    @endif
                </p>
            </div>
            <div>
                <h3 class="text-sm font-medium text-gray-500">Total entrees de stock</h3>
                <p class="mt-1 text-sm font-semibold text-gray-900">{{ $supplier->stock_entries_count }}</p>
            </div>
        </div>
    </div>

    <!-- Stock Entries Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Historique des entrees de stock</h2>
        </div>

        @if($stockEntries->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produit</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Quantite</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($stockEntries as $entry)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-gray-900">{{ \Carbon\Carbon::parse($entry->date)->format('d/m/Y') }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-medium text-gray-900">{{ $entry->product->name ?? 'Produit supprime' }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-gray-500">{{ $entry->product->sku ?? '-' }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                +{{ $entry->quantity }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $stockEntries->links() }}
        </div>
        @else
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Aucune entree de stock</h3>
            <p class="mt-1 text-sm text-gray-500">Ce fournisseur n'a pas encore d'entrees de stock enregistrees.</p>
        </div>
        @endif
    </div>
</div>
@endsection

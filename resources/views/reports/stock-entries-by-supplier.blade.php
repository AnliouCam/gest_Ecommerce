@extends('layouts.app')

@section('title', 'Entrees stock par fournisseur')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div>
        <a href="{{ route('gerant.reports.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-primary-600 transition-colors">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Retour aux rapports
        </a>
        <h1 class="mt-2 text-2xl font-bold text-gray-900">Entrees stock par fournisseur</h1>
        <p class="mt-1 text-sm text-gray-500">Historique des approvisionnements par fournisseur</p>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total entrees</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($summary->total_entries ?? 0) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Unites recues</p>
                    <p class="text-2xl font-bold text-green-600">+{{ number_format($summary->total_quantity ?? 0) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Fournisseurs actifs</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $summary->unique_suppliers ?? 0 }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
        <form method="GET" action="{{ route('gerant.reports.stock-entries-by-supplier') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Date From -->
                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Date debut</label>
                    <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>

                <!-- Date To -->
                <div>
                    <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Date fin</label>
                    <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>

                <!-- Search -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Recherche fournisseur</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Nom du fournisseur..."
                        class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
            </div>

            <div class="flex justify-end gap-2">
                @if(request()->hasAny(['date_from', 'date_to', 'search', 'supplier_id']))
                    <a href="{{ route('gerant.reports.stock-entries-by-supplier') }}" class="px-4 py-2 text-gray-500 hover:text-gray-700">
                        Reinitialiser
                    </a>
                @endif
                <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                    Filtrer
                </button>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 {{ $selectedSupplier ? 'lg:grid-cols-2' : '' }} gap-6">
        <!-- Suppliers List -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-900">Fournisseurs</h2>
            </div>
            @if($suppliers->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fournisseur</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Entrees</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Unites</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($suppliers as $supplier)
                        <tr class="hover:bg-gray-50 transition-colors {{ $selectedSupplier && $selectedSupplier->id === $supplier->id ? 'bg-primary-50' : '' }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-medium text-gray-900">{{ $supplier->name }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-700">
                                {{ number_format($supplier->total_entries ?? 0) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    +{{ number_format($supplier->total_quantity ?? 0) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('gerant.reports.stock-entries-by-supplier', array_merge(request()->except('supplier_id', 'entries_page'), ['supplier_id' => $supplier->id])) }}"
                                   class="text-primary-600 hover:text-primary-800">
                                    Details
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $suppliers->links() }}
            </div>
            @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun fournisseur trouve</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Aucune entree de stock pour cette periode.
                </p>
            </div>
            @endif
        </div>

        <!-- Supplier Details -->
        @if($selectedSupplier && $supplierEntries)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-primary-50">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="font-semibold text-gray-900">{{ $selectedSupplier->name }}</h2>
                        <p class="text-sm text-gray-500">Historique des entrees</p>
                    </div>
                    <a href="{{ route('gerant.suppliers.show', $selectedSupplier) }}" class="text-sm text-primary-600 hover:text-primary-800">
                        Voir fiche
                    </a>
                </div>
            </div>
            @if($supplierEntries->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produit</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Quantite</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($supplierEntries as $entry)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                {{ $entry->date->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $entry->product->name ?? 'Produit supprime' }}</div>
                                <div class="text-xs text-gray-500">{{ $entry->product->sku ?? '-' }}</div>
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
                {{ $supplierEntries->links() }}
            </div>
            @else
            <div class="text-center py-12">
                <p class="text-sm text-gray-500">Aucune entree pour ce fournisseur sur cette periode.</p>
            </div>
            @endif
        </div>
        @endif
    </div>
</div>
@endsection

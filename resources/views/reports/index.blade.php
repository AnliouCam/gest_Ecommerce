@extends('layouts.app')

@section('title', 'Rapports')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Rapports</h1>
        <p class="mt-1 text-sm text-gray-500">Vue d'ensemble de l'activite de la boutique</p>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Products -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-primary-100 text-primary-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Produits</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stockStats->total_products ?? 0) }}</p>
                </div>
            </div>
        </div>

        <!-- Total Stock Units -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-emerald-100 text-emerald-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Unites en stock</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stockStats->total_units ?? 0) }}</p>
                </div>
            </div>
        </div>

        <!-- Stock Value -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-amber-100 text-amber-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Valeur stock (achat)</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stockStats->stock_value_purchase ?? 0, 0, ',', ' ') }} F</p>
                </div>
            </div>
        </div>

        <!-- Low Stock Alert -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center">
                <div class="p-3 rounded-full {{ ($stockStats->low_stock_count ?? 0) > 0 ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-600' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Stock faible</p>
                    <p class="text-2xl font-bold {{ ($stockStats->low_stock_count ?? 0) > 0 ? 'text-red-600' : 'text-gray-900' }}">
                        {{ number_format($stockStats->low_stock_count ?? 0) }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Secondary Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Stock Entries This Month -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Entrees stock ce mois</p>
                    <p class="text-xl font-bold text-gray-900 mt-1">+{{ number_format($stockEntriesThisMonth ?? 0) }} unites</p>
                </div>
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Adjustments Added This Month -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Ajustements + ce mois</p>
                    <p class="text-xl font-bold text-green-600 mt-1">+{{ number_format($adjustmentsThisMonth->added ?? 0) }} unites</p>
                </div>
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Adjustments Removed This Month -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Ajustements - ce mois</p>
                    <p class="text-xl font-bold text-red-600 mt-1">-{{ number_format($adjustmentsThisMonth->removed ?? 0) }} unites</p>
                </div>
                <div class="p-3 rounded-full bg-red-100 text-red-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Reports Links -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <!-- Low Stock Report -->
        <a href="{{ route('gerant.reports.low-stock') }}" class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:border-primary-300 hover:shadow-md transition-all group">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-red-100 text-red-600 group-hover:bg-red-200 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="font-semibold text-gray-900">Produits en stock faible</h3>
                        <p class="text-sm text-gray-500">{{ $stockStats->low_stock_count ?? 0 }} produit(s) en alerte</p>
                    </div>
                </div>
                <svg class="w-5 h-5 text-gray-400 group-hover:text-primary-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </div>
        </a>

        <!-- Stock Entries by Supplier -->
        <a href="{{ route('gerant.reports.stock-entries-by-supplier') }}" class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:border-primary-300 hover:shadow-md transition-all group">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600 group-hover:bg-blue-200 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="font-semibold text-gray-900">Entrees par fournisseur</h3>
                        <p class="text-sm text-gray-500">Historique des approvisionnements</p>
                    </div>
                </div>
                <svg class="w-5 h-5 text-gray-400 group-hover:text-primary-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </div>
        </a>

        <!-- Stock Adjustments -->
        <a href="{{ route('gerant.reports.stock-adjustments') }}" class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:border-primary-300 hover:shadow-md transition-all group">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-orange-100 text-orange-600 group-hover:bg-orange-200 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="font-semibold text-gray-900">Ajustements de stock</h3>
                        <p class="text-sm text-gray-500">Pertes, casses, inventaires</p>
                    </div>
                </div>
                <svg class="w-5 h-5 text-gray-400 group-hover:text-primary-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </div>
        </a>
    </div>

    <!-- Categories & Top Suppliers -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Categories Distribution -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-900">Produits par categorie</h2>
            </div>
            <div class="p-6">
                @if($categoriesStats->count() > 0)
                <div class="space-y-4">
                    @foreach($categoriesStats as $category)
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="font-medium text-gray-700">{{ $category->name }}</span>
                            <span class="text-gray-500">{{ $category->products_count }} produit(s)</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            @php
                                $percentage = $stockStats->total_products > 0
                                    ? ($category->products_count / $stockStats->total_products) * 100
                                    : 0;
                            @endphp
                            <div class="bg-primary-600 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-gray-500 text-center py-4">Aucune categorie</p>
                @endif
            </div>
        </div>

        <!-- Top Suppliers This Month -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-900">Top fournisseurs ce mois</h2>
            </div>
            <div class="p-6">
                @if($topSuppliers->count() > 0)
                <div class="space-y-4">
                    @foreach($topSuppliers as $index => $supplier)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <span class="w-6 h-6 flex items-center justify-center rounded-full text-xs font-bold
                                {{ $index === 0 ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ $index + 1 }}
                            </span>
                            <span class="ml-3 font-medium text-gray-700">{{ $supplier->name }}</span>
                        </div>
                        <span class="text-sm text-gray-500">{{ number_format($supplier->total_quantity) }} unites</span>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-gray-500 text-center py-4">Aucune entree ce mois</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Future Reports Notice -->
    <div class="bg-blue-50 rounded-lg p-4 border border-blue-100">
        <div class="flex">
            <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="ml-3">
                <p class="text-sm text-blue-700">
                    <strong>Rapports ventes</strong> : Les rapports de ventes, benefices et marges seront disponibles apres l'activation du module Ventes.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

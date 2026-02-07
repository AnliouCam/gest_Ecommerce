@extends('layouts.app')

@section('title', $product->name)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between">
        <div>
            <a href="{{ route('ventes.products.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-primary-600 transition-colors">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Retour au catalogue
            </a>
            <h1 class="mt-2 text-2xl font-bold text-gray-900">{{ $product->name }}</h1>
            <p class="text-sm text-gray-500">SKU: {{ $product->sku }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Product Image -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                @if($product->image)
                    <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}" class="w-full h-64 object-cover rounded-lg">
                @else
                    <div class="w-full h-64 bg-gray-100 rounded-lg flex items-center justify-center">
                        <svg class="h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                @endif
            </div>
        </div>

        <!-- Product Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Price & Stock Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Prix de vente</p>
                        <p class="mt-1 text-2xl font-bold text-primary-600">{{ number_format($product->sale_price, 0, ',', ' ') }} F</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Stock disponible</p>
                        <p class="mt-1 text-2xl font-bold {{ $product->quantity == 0 ? 'text-red-600' : ($product->isLowStock() ? 'text-amber-600' : 'text-emerald-600') }}">
                            {{ $product->quantity }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Remise max</p>
                        <p class="mt-1 text-2xl font-bold text-gray-900">{{ $product->max_discount }}%</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Categorie</p>
                        <p class="mt-1">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                {{ $product->category->name ?? 'N/A' }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Status Alert -->
            @if($product->quantity == 0)
                <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                    <div class="flex">
                        <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Produit en rupture de stock</h3>
                            <p class="mt-1 text-sm text-red-700">Ce produit n'est pas disponible a la vente actuellement.</p>
                        </div>
                    </div>
                </div>
            @elseif($product->isLowStock())
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
                    <div class="flex">
                        <svg class="h-5 w-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-amber-800">Stock faible</h3>
                            <p class="mt-1 text-sm text-amber-700">Il reste seulement {{ $product->quantity }} unite(s) en stock.</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Price Calculation Helper -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Calcul de prix avec remise</h3>
                <div class="space-y-4" x-data="{ quantity: 1, discount: 0, maxDiscount: {{ $product->max_discount }}, price: {{ $product->sale_price }} }">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Quantite</label>
                            <input type="number" x-model="quantity" min="1" max="{{ $product->quantity }}"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Remise (%)</label>
                            <input type="number" x-model="discount" min="0" :max="maxDiscount"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <p class="mt-1 text-xs text-gray-500">Max: <span x-text="maxDiscount"></span>%</p>
                        </div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>Sous-total:</span>
                            <span x-text="(quantity * price).toLocaleString('fr-FR') + ' F'"></span>
                        </div>
                        <div class="flex justify-between text-sm text-gray-600 mt-1">
                            <span>Remise:</span>
                            <span class="text-red-600" x-text="'-' + Math.round(quantity * price * discount / 100).toLocaleString('fr-FR') + ' F'"></span>
                        </div>
                        <hr class="my-2 border-gray-300">
                        <div class="flex justify-between text-lg font-bold text-gray-900">
                            <span>Total:</span>
                            <span class="text-primary-600" x-text="Math.round(quantity * price * (1 - discount / 100)).toLocaleString('fr-FR') + ' F'"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

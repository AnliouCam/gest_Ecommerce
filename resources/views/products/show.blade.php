@extends('layouts.app')

@section('title', $product->name)

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-6 flex items-center justify-between">
        <div>
            <a href="{{ route('gerant.products.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-primary-600 transition-colors">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Retour aux produits
            </a>
            <h1 class="mt-2 text-2xl font-bold text-gray-900">{{ $product->name }}</h1>
            <p class="mt-1 text-sm text-gray-500 font-mono">SKU: {{ $product->sku }}</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('gerant.products.edit', $product) }}" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Modifier
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Product Details Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informations du produit</h3>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <span class="text-sm text-gray-500">Categorie</span>
                        <p class="font-medium text-gray-900">{{ $product->category->name }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Date de creation</span>
                        <p class="font-medium text-gray-900">{{ $product->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>

            <!-- Pricing Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Prix et marge</h3>

                <div class="grid grid-cols-3 gap-4">
                    <div class="bg-gray-50 rounded-lg p-4 text-center">
                        <span class="text-sm text-gray-500 block">Prix d'achat</span>
                        <p class="text-xl font-bold text-gray-900">{{ number_format($product->purchase_price, 0, ',', ' ') }}</p>
                        <span class="text-xs text-gray-500">FCFA</span>
                    </div>
                    <div class="bg-primary-50 rounded-lg p-4 text-center">
                        <span class="text-sm text-primary-600 block">Prix de vente</span>
                        <p class="text-xl font-bold text-primary-700">{{ number_format($product->sale_price, 0, ',', ' ') }}</p>
                        <span class="text-xs text-primary-600">FCFA</span>
                    </div>
                    <div class="bg-green-50 rounded-lg p-4 text-center">
                        <span class="text-sm text-green-600 block">Marge</span>
                        <p class="text-xl font-bold text-green-700">{{ number_format($product->marge, 0, ',', ' ') }}</p>
                        <span class="text-xs text-green-600">{{ number_format($product->marge_percent, 1) }}%</span>
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Remise maximum autorisee</span>
                        <span class="font-medium text-gray-900">{{ $product->max_discount }}%</span>
                    </div>
                </div>
            </div>

            <!-- Stock Entries History -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Historique des entrees de stock</h3>

                @if($product->stockEntries->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fournisseur</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Quantite</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($product->stockEntries as $entry)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ \Carbon\Carbon::parse($entry->date)->format('d/m/Y') }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ $entry->supplier->name }}</td>
                                        <td class="px-4 py-3 text-sm text-right font-medium text-green-600">+{{ $entry->quantity }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-sm text-gray-500 text-center py-4">Aucune entree de stock enregistree</p>
                @endif
            </div>

            <!-- Sales History -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Dernieres ventes</h3>

                @if($product->saleItems->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vente #</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Quantite</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Prix unitaire</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($product->saleItems as $item)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ $item->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900">#{{ $item->sale_id }}</td>
                                        <td class="px-4 py-3 text-sm text-right font-medium text-red-600">-{{ $item->quantity }}</td>
                                        <td class="px-4 py-3 text-sm text-right text-gray-900">{{ number_format($item->unit_price, 0, ',', ' ') }} FCFA</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-sm text-gray-500 text-center py-4">Aucune vente enregistree pour ce produit</p>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Image -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                @if($product->image)
                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-48 object-cover rounded-lg">
                @else
                    <div class="w-full h-48 bg-gray-100 rounded-lg flex items-center justify-center">
                        <svg class="w-16 h-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                @endif
            </div>

            <!-- Stock Status -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Etat du stock</h3>

                <div class="text-center mb-4">
                    <span class="text-4xl font-bold @if($product->quantity == 0) text-red-600 @elseif($product->isLowStock()) text-amber-600 @else text-green-600 @endif">
                        {{ $product->quantity }}
                    </span>
                    <p class="text-sm text-gray-500 mt-1">unites en stock</p>
                </div>

                @if($product->quantity == 0)
                    <div class="bg-red-50 border border-red-200 rounded-lg p-3 text-center">
                        <span class="text-sm font-medium text-red-700">Rupture de stock</span>
                    </div>
                @elseif($product->isLowStock())
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 text-center">
                        <span class="text-sm font-medium text-amber-700">Stock faible (seuil: {{ $product->stock_alert }})</span>
                    </div>
                @else
                    <div class="bg-green-50 border border-green-200 rounded-lg p-3 text-center">
                        <span class="text-sm font-medium text-green-700">Stock suffisant</span>
                    </div>
                @endif

                <div class="mt-4 pt-4 border-t border-gray-200">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">Seuil d'alerte</span>
                        <span class="font-medium text-gray-900">{{ $product->stock_alert }} unites</span>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Statistiques</h3>

                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Total vendu</span>
                        <span class="font-medium text-gray-900">{{ $stats['total_sold'] }} unites</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Entrees stock</span>
                        <span class="font-medium text-gray-900">{{ $stats['total_entries'] }} unites</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Nb de ventes</span>
                        <span class="font-medium text-gray-900">{{ $stats['sales_count'] }}</span>
                    </div>
                </div>
            </div>

            <!-- Danger Zone -->
            <div class="bg-white rounded-xl shadow-sm border border-red-100 p-6">
                <h3 class="text-lg font-medium text-red-600 mb-4">Zone de danger</h3>

                <form action="{{ route('gerant.products.destroy', $product) }}" method="POST"
                    onsubmit="return confirm('Etes-vous sur de vouloir supprimer ce produit ? Cette action est irreversible.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm">
                        Supprimer le produit
                    </button>
                </form>
                <p class="mt-2 text-xs text-gray-500 text-center">Cette action est irreversible</p>
            </div>
        </div>
    </div>
</div>
@endsection

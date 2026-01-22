@extends('layouts.app')

@section('title', 'Nouvel ajustement de stock')

@section('content')
<div class="max-w-xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('gerant.stock-adjustments.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-primary-600 transition-colors">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Retour aux ajustements
        </a>
        <h1 class="mt-2 text-2xl font-bold text-gray-900">Nouvel ajustement de stock</h1>
        <p class="mt-1 text-sm text-gray-500">Corrigez manuellement le stock d'un produit (perte, casse, inventaire)</p>
    </div>

    <!-- Form -->
    <form action="{{ route('gerant.stock-adjustments.store') }}" method="POST" class="space-y-6" x-data="{ type: '{{ old('type', '') }}' }">
        @csrf

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
            <!-- Type -->
            <div>
                <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Type d'ajustement *</label>
                <select name="type" id="type" required x-model="type"
                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('type') border-red-500 @enderror">
                    <option value="">Selectionnez un type</option>
                    @foreach($types as $value => $label)
                        <option value="{{ $value }}" {{ old('type') === $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('type')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Product -->
            <div>
                <label for="product_id" class="block text-sm font-medium text-gray-700 mb-1">Produit *</label>
                <select name="product_id" id="product_id" required
                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('product_id') border-red-500 @enderror">
                    <option value="">Selectionnez un produit</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                            {{ $product->name }} ({{ $product->sku }}) - Stock: {{ $product->quantity }}
                        </option>
                    @endforeach
                </select>
                @error('product_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                @if($products->isEmpty())
                    <p class="mt-1 text-sm text-amber-600">
                        Aucun produit disponible. <a href="{{ route('gerant.products.create') }}" class="underline">Creer un produit</a>
                    </p>
                @endif
            </div>

            <!-- Quantity -->
            <div>
                <label for="quantity" class="block text-sm font-medium text-gray-700 mb-1">
                    <span x-show="type === 'perte' || type === 'casse'">Quantite a retirer *</span>
                    <span x-show="type === 'inventaire' || type === 'autre'">Quantite (+/-) *</span>
                    <span x-show="!type">Quantite *</span>
                </label>
                <input type="number" name="quantity" id="quantity" value="{{ old('quantity') }}" required
                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('quantity') border-red-500 @enderror"
                    :placeholder="(type === 'perte' || type === 'casse') ? 'Ex: 5 (sera retire du stock)' : 'Ex: -3 pour retirer, +5 pour ajouter'">
                @error('quantity')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500" x-show="type === 'perte' || type === 'casse'">
                    Entrez un nombre positif. Il sera automatiquement retire du stock.
                </p>
                <p class="mt-1 text-xs text-gray-500" x-show="type === 'inventaire' || type === 'autre'">
                    Entrez un nombre negatif pour retirer, positif pour ajouter.
                </p>
            </div>

            <!-- Reason -->
            <div>
                <label for="reason" class="block text-sm font-medium text-gray-700 mb-1">Raison / Commentaire</label>
                <textarea name="reason" id="reason" rows="3"
                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('reason') border-red-500 @enderror"
                    placeholder="Decrivez la raison de cet ajustement...">{{ old('reason') }}</textarea>
                @error('reason')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Info -->
        <div class="bg-amber-50 rounded-lg p-4 border border-amber-100">
            <div class="flex">
                <svg class="h-5 w-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div class="ml-3">
                    <p class="text-sm text-amber-700">
                        <strong>Attention :</strong> Les ajustements ne peuvent pas etre modifies ou supprimes apres enregistrement (tracabilite).
                    </p>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end space-x-4">
            <a href="{{ route('gerant.stock-adjustments.index') }}" class="px-4 py-2 text-gray-700 hover:text-gray-900 transition-colors">
                Annuler
            </a>
            <button type="submit" class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors shadow-sm">
                Enregistrer l'ajustement
            </button>
        </div>
    </form>
</div>
@endsection

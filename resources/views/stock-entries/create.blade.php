@extends('layouts.app')

@section('title', 'Nouvelle entree de stock')

@section('content')
<div class="max-w-xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('gerant.stock-entries.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-primary-600 transition-colors">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Retour aux entrees de stock
        </a>
        <h1 class="mt-2 text-2xl font-bold text-gray-900">Nouvelle entree de stock</h1>
        <p class="mt-1 text-sm text-gray-500">Enregistrez un approvisionnement recu d'un fournisseur</p>
    </div>

    <!-- Form -->
    <form action="{{ route('gerant.stock-entries.store') }}" method="POST" class="space-y-6">
        @csrf

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
            <!-- Date -->
            <div>
                <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Date de reception *</label>
                <input type="date" name="date" id="date" value="{{ old('date', date('Y-m-d')) }}" required max="{{ date('Y-m-d') }}"
                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('date') border-red-500 @enderror">
                @error('date')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Supplier -->
            <div>
                <label for="supplier_id" class="block text-sm font-medium text-gray-700 mb-1">Fournisseur *</label>
                <select name="supplier_id" id="supplier_id" required
                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('supplier_id') border-red-500 @enderror">
                    <option value="">Selectionnez un fournisseur</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                            {{ $supplier->name }}
                        </option>
                    @endforeach
                </select>
                @error('supplier_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                @if($suppliers->isEmpty())
                    <p class="mt-1 text-sm text-amber-600">
                        Aucun fournisseur disponible. <a href="{{ route('gerant.suppliers.create') }}" class="underline">Creer un fournisseur</a>
                    </p>
                @endif
            </div>

            <!-- Product -->
            <div>
                <label for="product_id" class="block text-sm font-medium text-gray-700 mb-1">Produit *</label>
                <select name="product_id" id="product_id" required
                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('product_id') border-red-500 @enderror">
                    <option value="">Selectionnez un produit</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                            {{ $product->name }} ({{ $product->sku }}) - Stock actuel: {{ $product->quantity }}
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
                <label for="quantity" class="block text-sm font-medium text-gray-700 mb-1">Quantite recue *</label>
                <input type="number" name="quantity" id="quantity" value="{{ old('quantity') }}" required min="1"
                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('quantity') border-red-500 @enderror"
                    placeholder="Ex: 50">
                @error('quantity')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Info -->
        <div class="bg-blue-50 rounded-lg p-4 border border-blue-100">
            <div class="flex">
                <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        Le stock du produit sera automatiquement mis a jour apres l'enregistrement de cette entree.
                    </p>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end space-x-4">
            <a href="{{ route('gerant.stock-entries.index') }}" class="px-4 py-2 text-gray-700 hover:text-gray-900 transition-colors">
                Annuler
            </a>
            <button type="submit" class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors shadow-sm">
                Enregistrer l'entree
            </button>
        </div>
    </form>
</div>
@endsection

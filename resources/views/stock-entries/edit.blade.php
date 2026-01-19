@extends('layouts.app')

@section('title', 'Modifier entree de stock')

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
        <h1 class="mt-2 text-2xl font-bold text-gray-900">Modifier l'entree de stock</h1>
        <p class="mt-1 text-sm text-gray-500">Modifiez les informations de cette entree</p>
    </div>

    <!-- Current Info -->
    <div class="bg-gray-50 rounded-lg p-4 mb-6 border border-gray-200">
        <h3 class="text-sm font-medium text-gray-700 mb-2">Informations actuelles</h3>
        <dl class="grid grid-cols-2 gap-2 text-sm">
            <dt class="text-gray-500">Fournisseur:</dt>
            <dd class="text-gray-900">{{ $stockEntry->supplier->name }}</dd>
            <dt class="text-gray-500">Produit:</dt>
            <dd class="text-gray-900">{{ $stockEntry->product->name }}</dd>
            <dt class="text-gray-500">Quantite:</dt>
            <dd class="text-gray-900 font-medium">{{ $stockEntry->quantity }}</dd>
            <dt class="text-gray-500">Date:</dt>
            <dd class="text-gray-900">{{ $stockEntry->date->format('d/m/Y') }}</dd>
        </dl>
    </div>

    <!-- Form -->
    <form action="{{ route('gerant.stock-entries.update', $stockEntry) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
            <!-- Date -->
            <div>
                <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Date de reception *</label>
                <input type="date" name="date" id="date" value="{{ old('date', $stockEntry->date->format('Y-m-d')) }}" required max="{{ date('Y-m-d') }}"
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
                        <option value="{{ $supplier->id }}" {{ old('supplier_id', $stockEntry->supplier_id) == $supplier->id ? 'selected' : '' }}>
                            {{ $supplier->name }}
                        </option>
                    @endforeach
                </select>
                @error('supplier_id')
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
                        <option value="{{ $product->id }}" {{ old('product_id', $stockEntry->product_id) == $product->id ? 'selected' : '' }}>
                            {{ $product->name }} ({{ $product->sku }}) - Stock actuel: {{ $product->quantity }}
                        </option>
                    @endforeach
                </select>
                @error('product_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Quantity -->
            <div>
                <label for="quantity" class="block text-sm font-medium text-gray-700 mb-1">Quantite recue *</label>
                <input type="number" name="quantity" id="quantity" value="{{ old('quantity', $stockEntry->quantity) }}" required min="1"
                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('quantity') border-red-500 @enderror"
                    placeholder="Ex: 50">
                @error('quantity')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Warning -->
        <div class="bg-amber-50 rounded-lg p-4 border border-amber-100">
            <div class="flex">
                <svg class="h-5 w-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div class="ml-3">
                    <p class="text-sm text-amber-700">
                        La modification de cette entree ajustera automatiquement le stock du produit concerne.
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
                Mettre a jour
            </button>
        </div>
    </form>
</div>
@endsection

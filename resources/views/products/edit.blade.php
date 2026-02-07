@extends('layouts.app')

@section('title', 'Modifier ' . $product->name)

@section('content')
<div class="max-w-3xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('gerant.products.show', $product) }}" class="inline-flex items-center text-sm text-gray-500 hover:text-primary-600 transition-colors">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Retour au produit
        </a>
        <h1 class="mt-2 text-2xl font-bold text-gray-900">Modifier le produit</h1>
        <p class="mt-1 text-sm text-gray-500">{{ $product->name }} ({{ $product->sku }})</p>
    </div>

    <!-- Form -->
    <form action="{{ route('gerant.products.update', $product) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-6">
            <!-- Basic Info -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informations generales</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Name -->
                    <div class="md:col-span-2">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nom du produit *</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $product->name) }}" required
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('name') border-red-500 @enderror"
                            placeholder="Ex: PC Portable HP ProBook 450">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- SKU -->
                    <div>
                        <label for="sku" class="block text-sm font-medium text-gray-700 mb-1">Reference SKU *</label>
                        <input type="text" name="sku" id="sku" value="{{ old('sku', $product->sku) }}" required
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 font-mono @error('sku') border-red-500 @enderror"
                            placeholder="Ex: HP-PB450-001">
                        @error('sku')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Category -->
                    <div>
                        <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Categorie *</label>
                        <select name="category_id" id="category_id" required
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('category_id') border-red-500 @enderror">
                            <option value="">Selectionner une categorie</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <hr class="border-gray-200">

            <!-- Pricing -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Prix</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Purchase Price -->
                    <div>
                        <label for="purchase_price" class="block text-sm font-medium text-gray-700 mb-1">Prix d'achat (FCFA) *</label>
                        <input type="number" name="purchase_price" id="purchase_price" value="{{ old('purchase_price', $product->purchase_price) }}" required min="0" step="1"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('purchase_price') border-red-500 @enderror"
                            placeholder="0">
                        @error('purchase_price')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Sale Price -->
                    <div>
                        <label for="sale_price" class="block text-sm font-medium text-gray-700 mb-1">Prix de vente (FCFA) *</label>
                        <input type="number" name="sale_price" id="sale_price" value="{{ old('sale_price', $product->sale_price) }}" required min="0" step="1"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('sale_price') border-red-500 @enderror"
                            placeholder="0">
                        @error('sale_price')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Max Discount -->
                    <div>
                        <label for="max_discount" class="block text-sm font-medium text-gray-700 mb-1">Remise max (%) *</label>
                        <input type="number" name="max_discount" id="max_discount" value="{{ old('max_discount', $product->max_discount) }}" required min="0" max="20"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('max_discount') border-red-500 @enderror"
                            placeholder="0">
                        <p class="mt-1 text-xs text-gray-500">Maximum 20%</p>
                        @error('max_discount')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <hr class="border-gray-200">

            <!-- Stock -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Stock</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Quantity -->
                    <div>
                        <label for="quantity" class="block text-sm font-medium text-gray-700 mb-1">Quantite en stock *</label>
                        <input type="number" name="quantity" id="quantity" value="{{ old('quantity', $product->quantity) }}" required min="0"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('quantity') border-red-500 @enderror"
                            placeholder="0">
                        @error('quantity')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Stock Alert -->
                    <div>
                        <label for="stock_alert" class="block text-sm font-medium text-gray-700 mb-1">Seuil d'alerte *</label>
                        <input type="number" name="stock_alert" id="stock_alert" value="{{ old('stock_alert', $product->stock_alert) }}" required min="0"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('stock_alert') border-red-500 @enderror"
                            placeholder="5">
                        <p class="mt-1 text-xs text-gray-500">Alerte si stock inferieur ou egal a ce seuil</p>
                        @error('stock_alert')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <hr class="border-gray-200">

            <!-- Image -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Image</h3>
                <div x-data="{ preview: null, hasExisting: {{ $product->image ? 'true' : 'false' }} }">
                    <!-- Current Image -->
                    @if($product->image)
                        <div class="mb-4" x-show="hasExisting && !preview">
                            <p class="text-sm text-gray-500 mb-2">Image actuelle :</p>
                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="h-32 w-32 object-cover rounded-lg border border-gray-200">
                        </div>
                    @endif

                    <label class="block">
                        <span class="sr-only">Choisir une nouvelle image</span>
                        <input type="file" name="image" accept="image/jpeg,image/png,image/jpg,image/webp"
                            @change="preview = URL.createObjectURL($event.target.files[0])"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 cursor-pointer">
                    </label>
                    <p class="mt-1 text-xs text-gray-500">JPEG, PNG ou WebP. Max 2 Mo. Laissez vide pour conserver l'image actuelle.</p>
                    @error('image')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    <!-- New Preview -->
                    <div x-show="preview" x-cloak class="mt-4">
                        <p class="text-sm text-gray-500 mb-2">Nouvelle image :</p>
                        <img :src="preview" class="h-32 w-32 object-cover rounded-lg border border-gray-200">
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end space-x-4">
            <a href="{{ route('gerant.products.show', $product) }}" class="px-4 py-2 text-gray-700 hover:text-gray-900 transition-colors">
                Annuler
            </a>
            <button type="submit" class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors shadow-sm">
                Enregistrer les modifications
            </button>
        </div>
    </form>
</div>
@endsection

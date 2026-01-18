@extends('layouts.app')

@section('title', 'Modifier ' . $supplier->name)

@section('content')
<div class="max-w-xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('gerant.suppliers.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-primary-600 transition-colors">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Retour aux fournisseurs
        </a>
        <h1 class="mt-2 text-2xl font-bold text-gray-900">Modifier le fournisseur</h1>
        <p class="mt-1 text-sm text-gray-500">{{ $supplier->name }}</p>
    </div>

    <!-- Form -->
    <form action="{{ route('gerant.suppliers.update', $supplier) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
            <!-- Nom -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nom du fournisseur *</label>
                <input type="text" name="name" id="name" value="{{ old('name', $supplier->name) }}" required
                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('name') border-red-500 @enderror"
                    placeholder="Ex: Tech Distributors SARL">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Telephone -->
            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Telephone</label>
                <input type="text" name="phone" id="phone" value="{{ old('phone', $supplier->phone) }}"
                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('phone') border-red-500 @enderror"
                    placeholder="Ex: +33 1 23 45 67 89">
                @error('phone')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email', $supplier->email) }}"
                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('email') border-red-500 @enderror"
                    placeholder="Ex: contact@techdistributors.com">
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Info -->
            <div class="mt-4 pt-4 border-t border-gray-200">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500">Entrees de stock</span>
                    <span class="font-medium text-gray-900">{{ $supplier->stock_entries_count }}</span>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end space-x-4">
            <a href="{{ route('gerant.suppliers.index') }}" class="px-4 py-2 text-gray-700 hover:text-gray-900 transition-colors">
                Annuler
            </a>
            <button type="submit" class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors shadow-sm">
                Enregistrer les modifications
            </button>
        </div>
    </form>
</div>
@endsection

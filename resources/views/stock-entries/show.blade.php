@extends('layouts.app')

@section('title', 'Detail entree de stock')

@section('content')
<div class="max-w-2xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('gerant.stock-entries.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-primary-600 transition-colors">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Retour aux entrees de stock
        </a>
        <div class="flex items-center justify-between mt-2">
            <h1 class="text-2xl font-bold text-gray-900">Detail de l'entree de stock</h1>
            <div class="flex space-x-2">
                <a href="{{ route('gerant.stock-entries.edit', $stockEntry) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Modifier
                </a>
                <form action="{{ route('gerant.stock-entries.destroy', $stockEntry) }}" method="POST" x-data
                    @submit.prevent="if(confirm('Etes-vous sur de vouloir supprimer cette entree ? Le stock du produit sera ajuste.')) $el.submit()">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-white border border-red-300 text-red-600 rounded-lg hover:bg-red-50 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Supprimer
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Stock Entry Details -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <!-- Header with quantity badge -->
        <div class="bg-gradient-to-r from-green-500 to-emerald-600 px-6 py-8 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm">Entree de stock</p>
                    <p class="text-3xl font-bold mt-1">+{{ $stockEntry->quantity }} unites</p>
                </div>
                <div class="text-right">
                    <p class="text-green-100 text-sm">Date de reception</p>
                    <p class="text-xl font-semibold mt-1">{{ $stockEntry->date->format('d/m/Y') }}</p>
                </div>
            </div>
        </div>

        <!-- Details -->
        <div class="p-6 space-y-6">
            <!-- Supplier -->
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 rounded-lg bg-primary-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Fournisseur</p>
                    <a href="{{ route('gerant.suppliers.show', $stockEntry->supplier) }}" class="text-lg font-medium text-primary-600 hover:text-primary-800">
                        {{ $stockEntry->supplier->name }}
                    </a>
                    @if($stockEntry->supplier->phone || $stockEntry->supplier->email)
                        <div class="mt-1 text-sm text-gray-500">
                            @if($stockEntry->supplier->phone)
                                {{ $stockEntry->supplier->phone }}
                            @endif
                            @if($stockEntry->supplier->phone && $stockEntry->supplier->email)
                                •
                            @endif
                            @if($stockEntry->supplier->email)
                                {{ $stockEntry->supplier->email }}
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Product -->
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Produit</p>
                    <a href="{{ route('gerant.products.show', $stockEntry->product) }}" class="text-lg font-medium text-primary-600 hover:text-primary-800">
                        {{ $stockEntry->product->name }}
                    </a>
                    <p class="text-sm text-gray-500">SKU: {{ $stockEntry->product->sku }}</p>
                    <p class="mt-1 text-sm">
                        <span class="text-gray-500">Stock actuel:</span>
                        <span class="font-medium {{ $stockEntry->product->isLowStock() ? 'text-red-600' : 'text-gray-900' }}">
                            {{ $stockEntry->product->quantity }} unites
                        </span>
                    </p>
                </div>
            </div>

            <!-- Timestamps -->
            <div class="pt-4 border-t border-gray-200">
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500">Cree le</p>
                        <p class="text-gray-900">{{ $stockEntry->created_at->format('d/m/Y a H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Derniere modification</p>
                        <p class="text-gray-900">{{ $stockEntry->updated_at->format('d/m/Y a H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

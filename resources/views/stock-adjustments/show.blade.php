@extends('layouts.app')

@section('title', 'Detail ajustement #' . $stockAdjustment->id)

@section('content')
<div class="max-w-2xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('gerant.stock-adjustments.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-primary-600 transition-colors">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Retour aux ajustements
        </a>
        <h1 class="mt-2 text-2xl font-bold text-gray-900">Ajustement #{{ $stockAdjustment->id }}</h1>
        <p class="mt-1 text-sm text-gray-500">Detail de l'ajustement de stock</p>
    </div>

    <!-- Adjustment Details -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Informations</h2>
                @php
                    $typeColors = [
                        'perte' => 'bg-red-100 text-red-800',
                        'casse' => 'bg-orange-100 text-orange-800',
                        'inventaire' => 'bg-blue-100 text-blue-800',
                        'autre' => 'bg-gray-100 text-gray-800',
                    ];
                @endphp
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $typeColors[$stockAdjustment->type] ?? 'bg-gray-100 text-gray-800' }}">
                    {{ ucfirst($stockAdjustment->type) }}
                </span>
            </div>
        </div>

        <dl class="divide-y divide-gray-100">
            <!-- Date -->
            <div class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                <dt class="text-sm font-medium text-gray-500">Date</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    {{ $stockAdjustment->created_at->format('d/m/Y a H:i') }}
                </dd>
            </div>

            <!-- Product -->
            <div class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                <dt class="text-sm font-medium text-gray-500">Produit</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    <a href="{{ route('gerant.products.show', $stockAdjustment->product) }}" class="text-primary-600 hover:text-primary-800 font-medium">
                        {{ $stockAdjustment->product->name }}
                    </a>
                    <span class="text-gray-500">({{ $stockAdjustment->product->sku }})</span>
                </dd>
            </div>

            <!-- Quantity -->
            <div class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                <dt class="text-sm font-medium text-gray-500">Quantite ajustee</dt>
                <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">
                    @if($stockAdjustment->quantity > 0)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            +{{ $stockAdjustment->quantity }} unite(s) ajoutee(s)
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                            {{ $stockAdjustment->quantity }} unite(s) retiree(s)
                        </span>
                    @endif
                </dd>
            </div>

            <!-- Current Stock -->
            <div class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                <dt class="text-sm font-medium text-gray-500">Stock actuel du produit</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    {{ $stockAdjustment->product->quantity }} unite(s)
                </dd>
            </div>

            <!-- User -->
            <div class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                <dt class="text-sm font-medium text-gray-500">Effectue par</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    {{ $stockAdjustment->user->name }}
                </dd>
            </div>

            <!-- Reason -->
            <div class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                <dt class="text-sm font-medium text-gray-500">Raison</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    @if($stockAdjustment->reason)
                        {{ $stockAdjustment->reason }}
                    @else
                        <span class="text-gray-400 italic">Non specifiee</span>
                    @endif
                </dd>
            </div>
        </dl>
    </div>

    <!-- Info -->
    <div class="mt-6 bg-gray-50 rounded-lg p-4 border border-gray-200">
        <div class="flex">
            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="ml-3">
                <p class="text-sm text-gray-600">
                    Les ajustements de stock sont enregistres de maniere permanente pour assurer la tracabilite.
                </p>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="mt-6 flex justify-end">
        <a href="{{ route('gerant.stock-adjustments.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
            Retour a la liste
        </a>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Annuler vente #' . $sale->id)

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-3">
        <a href="{{ route('gerant.sales.show', $sale) }}" class="text-gray-500 hover:text-gray-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <h1 class="text-2xl font-bold text-gray-900">Annuler la vente #{{ $sale->id }}</h1>
    </div>

    <!-- Warning -->
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-amber-800">Attention : cette action est irreversible</h3>
                <div class="mt-2 text-sm text-amber-700">
                    <ul class="list-disc list-inside space-y-1">
                        <li>La vente sera marquee comme annulee</li>
                        <li>Le stock des produits sera automatiquement restaure</li>
                        <li>Cette action sera tracee (qui, quand, pourquoi)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Sale Summary -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h2 class="text-lg font-semibold text-gray-900">Resume de la vente</h2>
        </div>
        <div class="px-6 py-4">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                <div>
                    <span class="text-xs text-gray-500 uppercase">Date</span>
                    <p class="text-sm font-medium text-gray-900">{{ $sale->created_at->format('d/m/Y H:i') }}</p>
                </div>
                <div>
                    <span class="text-xs text-gray-500 uppercase">Vendeur</span>
                    <p class="text-sm font-medium text-gray-900">{{ $sale->user->name ?? 'N/A' }}</p>
                </div>
                <div>
                    <span class="text-xs text-gray-500 uppercase">Client</span>
                    <p class="text-sm font-medium text-gray-900">{{ $sale->customer->name ?? 'Anonyme' }}</p>
                </div>
                <div>
                    <span class="text-xs text-gray-500 uppercase">Total</span>
                    <p class="text-lg font-bold text-primary-600">{{ number_format($sale->total, 0, ',', ' ') }} F</p>
                </div>
            </div>

            <!-- Items -->
            <div class="border-t border-gray-200 pt-4">
                <h3 class="text-sm font-medium text-gray-700 mb-2">Articles a restaurer en stock :</h3>
                <div class="space-y-2">
                    @foreach($sale->items as $item)
                    <div class="flex justify-between items-center py-2 px-3 bg-gray-50 rounded-lg">
                        <div>
                            <span class="text-sm font-medium text-gray-900">
                                {{ $item->product->name ?? 'Produit supprime' }}
                            </span>
                            @if($item->product)
                                <span class="text-xs text-gray-500 ml-2">({{ $item->product->sku }})</span>
                            @endif
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            +{{ $item->quantity }} unite(s)
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Cancel Form -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Motif d'annulation</h2>
            <p class="text-sm text-gray-500">Le motif est obligatoire pour la tracabilite</p>
        </div>
        <form action="{{ route('gerant.sales.cancel', $sale) }}" method="POST" class="px-6 py-4">
            @csrf

            <div class="mb-4">
                <label for="cancel_reason" class="block text-sm font-medium text-gray-700 mb-2">
                    Motif de l'annulation <span class="text-red-500">*</span>
                </label>
                <textarea name="cancel_reason" id="cancel_reason" rows="4"
                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 @error('cancel_reason') border-red-500 @enderror"
                    placeholder="Expliquez pourquoi cette vente doit etre annulee (minimum 10 caracteres)...">{{ old('cancel_reason') }}</textarea>
                @error('cancel_reason')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Exemples : Erreur de saisie, Client a change d'avis, Double saisie, Produit defectueux...</p>
            </div>

            <!-- Quick reasons -->
            <div class="mb-6">
                <p class="text-xs text-gray-500 mb-2">Motifs rapides :</p>
                <div class="flex flex-wrap gap-2">
                    <button type="button" onclick="document.getElementById('cancel_reason').value = 'Erreur de saisie du vendeur - mauvais produit selectionne'"
                        class="px-3 py-1 text-xs bg-gray-100 text-gray-700 rounded-full hover:bg-gray-200 transition-colors">
                        Erreur de saisie
                    </button>
                    <button type="button" onclick="document.getElementById('cancel_reason').value = 'Le client a change d\'avis apres validation de la vente'"
                        class="px-3 py-1 text-xs bg-gray-100 text-gray-700 rounded-full hover:bg-gray-200 transition-colors">
                        Client a change d'avis
                    </button>
                    <button type="button" onclick="document.getElementById('cancel_reason').value = 'Double saisie - cette vente a ete enregistree deux fois par erreur'"
                        class="px-3 py-1 text-xs bg-gray-100 text-gray-700 rounded-full hover:bg-gray-200 transition-colors">
                        Double saisie
                    </button>
                    <button type="button" onclick="document.getElementById('cancel_reason').value = 'Produit defectueux constate apres la vente - retour client'"
                        class="px-3 py-1 text-xs bg-gray-100 text-gray-700 rounded-full hover:bg-gray-200 transition-colors">
                        Produit defectueux
                    </button>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('gerant.sales.show', $sale) }}" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                    Annuler
                </a>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                    Confirmer l'annulation
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

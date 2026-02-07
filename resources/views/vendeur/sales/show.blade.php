@extends('layouts.app')

@section('title', 'Vente #' . $sale->id)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('ventes.sales.index') }}" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <h1 class="text-2xl font-bold text-gray-900">Vente #{{ $sale->id }}</h1>
                @if($sale->status === 'completed')
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                        Completee
                    </span>
                @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        Annulee
                    </span>
                @endif
            </div>
            <p class="mt-1 text-sm text-gray-500">{{ $sale->created_at->format('d/m/Y H:i') }}</p>
        </div>
        <div class="mt-4 sm:mt-0 flex flex-wrap gap-2">
            <!-- Invoice buttons -->
            <a href="{{ route('ventes.invoices.show', $sale) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors shadow-sm">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                Voir facture
            </a>
            <a href="{{ route('ventes.invoices.download', $sale) }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors shadow-sm">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Telecharger PDF
            </a>
            <a href="{{ route('ventes.sales.create') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors shadow-sm">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Nouvelle vente
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Sale Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Items -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900">Articles</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produit</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prix unitaire</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantite</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remise</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($sale->items as $item)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        @if($item->product && $item->product->image)
                                            <img src="{{ asset('storage/' . $item->product->image) }}" alt="{{ $item->product->name }}" class="w-10 h-10 rounded-lg object-cover mr-3">
                                        @else
                                            <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center mr-3">
                                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                                </svg>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $item->product->name ?? 'Produit supprime' }}
                                            </div>
                                            @if($item->product)
                                                <div class="text-sm text-gray-500">{{ $item->product->sku }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ number_format($item->unit_price, 0, ',', ' ') }} F
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $item->quantity }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($item->discount > 0)
                                        <span class="text-emerald-600">-{{ number_format($item->discount, 0, ',', ' ') }} F</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right">
                                    {{ number_format(($item->unit_price * $item->quantity) - $item->discount, 0, ',', ' ') }} F
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50">
                            @php
                                $subtotal = $sale->items->sum(fn($item) => $item->unit_price * $item->quantity);
                            @endphp
                            <tr>
                                <td colspan="4" class="px-6 py-3 text-right text-sm font-medium text-gray-500">Sous-total</td>
                                <td class="px-6 py-3 text-right text-sm font-medium text-gray-900">{{ number_format($subtotal, 0, ',', ' ') }} F</td>
                            </tr>
                            @if($sale->discount > 0)
                            <tr>
                                <td colspan="4" class="px-6 py-3 text-right text-sm font-medium text-gray-500">Remise totale</td>
                                <td class="px-6 py-3 text-right text-sm font-medium text-emerald-600">-{{ number_format($sale->discount, 0, ',', ' ') }} F</td>
                            </tr>
                            @endif
                            <tr>
                                <td colspan="4" class="px-6 py-3 text-right text-sm font-bold text-gray-900">Total</td>
                                <td class="px-6 py-3 text-right text-lg font-bold text-primary-600">{{ number_format($sale->total, 0, ',', ' ') }} F</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Cancellation Info -->
            @if($sale->status === 'cancelled')
            <div class="bg-red-50 rounded-xl border border-red-200 p-6">
                <div class="flex items-start">
                    <svg class="w-6 h-6 text-red-600 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div>
                        <h3 class="text-lg font-semibold text-red-800">Vente annulee</h3>
                        <p class="mt-1 text-sm text-red-700">
                            <strong>Motif :</strong> {{ $sale->cancel_reason ?? 'Non specifie' }}
                        </p>
                        @if($sale->cancelledBy)
                        <p class="mt-1 text-sm text-red-700">
                            <strong>Annulee par :</strong> {{ $sale->cancelledBy->name }}
                        </p>
                        @endif
                        @if($sale->cancelled_at)
                        <p class="mt-1 text-sm text-red-700">
                            <strong>Date :</strong> {{ $sale->cancelled_at->format('d/m/Y H:i') }}
                        </p>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Sale Info -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Informations</h2>
                <dl class="space-y-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Vendeur</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $sale->user->name ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Date de vente</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $sale->created_at->format('d/m/Y H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Mode de paiement</dt>
                        <dd class="mt-1">
                            @php
                                $paymentLabels = [
                                    'especes' => 'Especes',
                                    'mobile_money' => 'Mobile Money',
                                    'carte' => 'Carte bancaire'
                                ];
                                $paymentIcons = [
                                    'especes' => '<svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>',
                                    'mobile_money' => '<svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>',
                                    'carte' => '<svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>'
                                ];
                            @endphp
                            <span class="inline-flex items-center space-x-2">
                                {!! $paymentIcons[$sale->payment_method] ?? '' !!}
                                <span class="text-sm text-gray-900">{{ $paymentLabels[$sale->payment_method] ?? $sale->payment_method }}</span>
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Nombre d'articles</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $sale->items->sum('quantity') }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Customer Info -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Client</h2>
                @if($sale->customer)
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 rounded-full bg-primary-100 flex items-center justify-center">
                            <span class="text-lg font-bold text-primary-600">
                                {{ strtoupper(substr($sale->customer->name, 0, 1)) }}
                            </span>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">{{ $sale->customer->name }}</p>
                            <p class="text-sm text-gray-500">{{ $sale->customer->phone }}</p>
                        </div>
                    </div>
                    @if($sale->customer->email)
                    <p class="text-sm text-gray-500 mb-2">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        {{ $sale->customer->email }}
                    </p>
                    @endif
                    <a href="{{ route('ventes.customers.show', $sale->customer) }}" class="text-sm text-primary-600 hover:text-primary-800">
                        Voir le profil client &rarr;
                    </a>
                @else
                    <div class="text-center py-4">
                        <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <p class="mt-2 text-sm text-gray-500">Client anonyme</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

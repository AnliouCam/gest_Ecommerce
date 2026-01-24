@extends('layouts.app')

@section('title', 'Vente #' . $sale->id)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-3">
                <a href="{{ route('gerant.sales.index') }}" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <h1 class="text-2xl font-bold text-gray-900">Vente #{{ $sale->id }}</h1>
                @if($sale->isCancelled())
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                        Annulee
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                        Completee
                    </span>
                @endif
            </div>
            <p class="mt-1 text-sm text-gray-500">Creee le {{ $sale->created_at->format('d/m/Y a H:i') }}</p>
        </div>
        @if(!$sale->isCancelled())
        <div class="mt-4 sm:mt-0">
            <a href="{{ route('gerant.sales.cancel.form', $sale) }}" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                </svg>
                Annuler cette vente
            </a>
        </div>
        @endif
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Sale Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Items -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Articles ({{ $sale->items->count() }})</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produit</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Prix unit.</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Qte</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Remise</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Sous-total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($sale->items as $item)
                            <tr>
                                <td class="px-6 py-4">
                                    @if($item->product)
                                        <div class="text-sm font-medium text-gray-900">{{ $item->product->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $item->product->sku }}</div>
                                    @else
                                        <span class="text-sm text-gray-400 italic">Produit supprime</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right text-sm text-gray-900">
                                    {{ number_format($item->unit_price, 0, ',', ' ') }} F
                                </td>
                                <td class="px-6 py-4 text-right text-sm text-gray-900">
                                    {{ $item->quantity }}
                                </td>
                                <td class="px-6 py-4 text-right text-sm">
                                    @if($item->discount > 0)
                                        <span class="text-orange-600">-{{ number_format($item->discount, 0, ',', ' ') }} F</span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-semibold text-gray-900">
                                    {{ number_format($item->subtotal, 0, ',', ' ') }} F
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-right text-sm font-medium text-gray-700">Sous-total</td>
                                <td class="px-6 py-4 text-right text-sm font-semibold text-gray-900">
                                    {{ number_format($sale->total + $sale->discount, 0, ',', ' ') }} F
                                </td>
                            </tr>
                            @if($sale->discount > 0)
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-right text-sm font-medium text-orange-600">Remise globale</td>
                                <td class="px-6 py-4 text-right text-sm font-semibold text-orange-600">
                                    -{{ number_format($sale->discount, 0, ',', ' ') }} F
                                </td>
                            </tr>
                            @endif
                            <tr class="border-t-2 border-gray-300">
                                <td colspan="4" class="px-6 py-4 text-right text-base font-bold text-gray-900">TOTAL</td>
                                <td class="px-6 py-4 text-right text-lg font-bold text-primary-600">
                                    {{ number_format($sale->total, 0, ',', ' ') }} F
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Cancellation Info (if cancelled) -->
            @if($sale->isCancelled())
            <div class="bg-red-50 rounded-xl shadow-sm border border-red-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-red-200 bg-red-100">
                    <h2 class="text-lg font-semibold text-red-800 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        Informations d'annulation
                    </h2>
                </div>
                <div class="px-6 py-4 space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm text-red-700">Annulee par</span>
                        <span class="text-sm font-medium text-red-900">{{ $sale->cancelledBy->name ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-red-700">Date d'annulation</span>
                        <span class="text-sm font-medium text-red-900">{{ $sale->cancelled_at->format('d/m/Y a H:i') }}</span>
                    </div>
                    <div>
                        <span class="text-sm text-red-700">Motif</span>
                        <p class="mt-1 text-sm text-red-900 bg-red-100 p-3 rounded-lg">{{ $sale->cancel_reason }}</p>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Sale Info -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Informations</h2>
                </div>
                <div class="px-6 py-4 space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Vendeur</span>
                        <span class="text-sm font-medium text-gray-900">{{ $sale->user->name ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Mode de paiement</span>
                        @php
                            $paymentLabels = [
                                'especes' => 'Especes',
                                'mobile_money' => 'Mobile Money',
                                'carte' => 'Carte bancaire',
                            ];
                        @endphp
                        <span class="text-sm font-medium text-gray-900">{{ $paymentLabels[$sale->payment_method] ?? $sale->payment_method }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Date</span>
                        <span class="text-sm font-medium text-gray-900">{{ $sale->created_at->format('d/m/Y') }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Heure</span>
                        <span class="text-sm font-medium text-gray-900">{{ $sale->created_at->format('H:i') }}</span>
                    </div>
                </div>
            </div>

            <!-- Customer Info -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Client</h2>
                </div>
                <div class="px-6 py-4">
                    @if($sale->customer)
                        <div class="space-y-3">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center">
                                    <span class="text-primary-600 font-semibold">{{ strtoupper(substr($sale->customer->name, 0, 1)) }}</span>
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900">{{ $sale->customer->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $sale->customer->phone }}</div>
                                </div>
                            </div>
                            @if($sale->customer->email)
                            <div class="text-sm text-gray-600">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                {{ $sale->customer->email }}
                            </div>
                            @endif
                        </div>
                    @else
                        <div class="text-center py-4">
                            <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <p class="mt-2 text-sm text-gray-500 italic">Vente anonyme</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

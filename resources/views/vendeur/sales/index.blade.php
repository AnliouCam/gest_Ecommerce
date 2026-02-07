@extends('layouts.app')

@section('title', 'Mes Ventes')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                @if(auth()->user()->isGerant())
                    Toutes les ventes
                @else
                    Mes ventes
                @endif
            </h1>
            <p class="mt-1 text-sm text-gray-500">Historique de vos ventes</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route('ventes.sales.create') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors shadow-sm">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Nouvelle vente
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-sm font-medium text-gray-500">Total ventes</p>
            <p class="text-2xl font-bold text-gray-900">{{ $summary->total_sales ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-sm font-medium text-gray-500">Completees</p>
            <p class="text-2xl font-bold text-emerald-600">{{ $summary->completed_count ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-sm font-medium text-gray-500">Annulees</p>
            <p class="text-2xl font-bold text-red-600">{{ $summary->cancelled_count ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-sm font-medium text-gray-500">Chiffre d'affaires</p>
            <p class="text-2xl font-bold text-primary-600">{{ number_format($summary->total_revenue ?? 0, 0, ',', ' ') }} F</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
        <form method="GET" action="{{ route('ventes.sales.index') }}" class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher par numero ou client..."
                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>
            <div class="w-full md:w-40">
                <select name="status" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="">Tous les statuts</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completees</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Annulees</option>
                </select>
            </div>
            <div class="w-full md:w-40">
                <select name="payment_method" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="">Tous les paiements</option>
                    <option value="especes" {{ request('payment_method') === 'especes' ? 'selected' : '' }}>Especes</option>
                    <option value="mobile_money" {{ request('payment_method') === 'mobile_money' ? 'selected' : '' }}>Mobile Money</option>
                    <option value="carte" {{ request('payment_method') === 'carte' ? 'selected' : '' }}>Carte</option>
                </select>
            </div>
            <div class="w-full md:w-40">
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>
            <div class="w-full md:w-40">
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                    Filtrer
                </button>
                @if(request()->hasAny(['search', 'status', 'payment_method', 'date_from', 'date_to']))
                    <a href="{{ route('ventes.sales.index') }}" class="px-4 py-2 text-gray-500 hover:text-gray-700">
                        Reinitialiser
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Sales Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        @if($sales->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">N°</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Articles</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Paiement</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @php
                        $paymentLabels = [
                            'especes' => 'Especes',
                            'mobile_money' => 'Mobile Money',
                            'carte' => 'Carte'
                        ];
                    @endphp
                    @foreach($sales as $sale)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-medium text-gray-900">#{{ $sale->id }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $sale->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($sale->customer)
                                <div class="text-sm font-medium text-gray-900">{{ $sale->customer->name }}</div>
                                <div class="text-sm text-gray-500">{{ $sale->customer->phone }}</div>
                            @else
                                <span class="text-sm text-gray-400">Anonyme</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                {{ $sale->items->count() }} article{{ $sale->items->count() > 1 ? 's' : '' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $paymentLabels[$sale->payment_method] ?? $sale->payment_method }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-medium text-gray-900">{{ number_format($sale->total, 0, ',', ' ') }} F</span>
                            @if($sale->discount > 0)
                                <span class="text-xs text-emerald-600 block">-{{ number_format($sale->discount, 0, ',', ' ') }} F</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($sale->status === 'completed')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                    Completee
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Annulee
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('ventes.sales.show', $sale) }}" class="text-primary-600 hover:text-primary-800">
                                Voir
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $sales->links() }}
        </div>
        @else
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Aucune vente</h3>
            <p class="mt-1 text-sm text-gray-500">
                @if(request()->hasAny(['search', 'status', 'payment_method', 'date_from', 'date_to']))
                    Aucune vente ne correspond a vos criteres.
                @else
                    Commencez par creer une nouvelle vente.
                @endif
            </p>
            <div class="mt-6">
                <a href="{{ route('ventes.sales.create') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Nouvelle vente
                </a>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

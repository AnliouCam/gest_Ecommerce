@extends('layouts.app')

@section('title', 'Historique des annulations')

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
                <h1 class="text-2xl font-bold text-gray-900">Historique des annulations</h1>
            </div>
            <p class="mt-1 text-sm text-gray-500">Suivi des ventes annulees pour tracabilite anti-fraude</p>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
            <div class="text-sm text-gray-500">Total annulations</div>
            <div class="text-2xl font-bold text-red-600">{{ number_format($summary->total_cancelled ?? 0) }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
            <div class="text-sm text-gray-500">Valeur totale annulee</div>
            <div class="text-2xl font-bold text-gray-900">{{ number_format($summary->total_value ?? 0, 0, ',', ' ') }} F</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
            <div class="text-sm text-gray-500">Gerants impliques</div>
            <div class="text-2xl font-bold text-gray-900">{{ number_format($summary->unique_cancellers ?? 0) }}</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
        <form method="GET" action="{{ route('gerant.sales.cancelled') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Cancelled By Filter -->
                <div>
                    <label for="cancelled_by" class="block text-sm font-medium text-gray-700 mb-1">Annule par</label>
                    <select name="cancelled_by" id="cancelled_by" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="">Tous les gerants</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('cancelled_by') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Date From -->
                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Date debut</label>
                    <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>

                <!-- Date To -->
                <div>
                    <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Date fin</label>
                    <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
            </div>

            <div class="flex justify-end gap-2">
                @if(request()->hasAny(['cancelled_by', 'date_from', 'date_to']))
                    <a href="{{ route('gerant.sales.cancelled') }}" class="px-4 py-2 text-gray-500 hover:text-gray-700">
                        Reinitialiser
                    </a>
                @endif
                <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                    Filtrer
                </button>
            </div>
        </form>
    </div>

    <!-- Cancelled Sales Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        @if($sales->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vente</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date vente</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vendeur</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Montant</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Annule par</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date annulation</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Motif</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($sales as $sale)
                    <tr class="hover:bg-red-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-medium text-gray-900">#{{ $sale->id }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-gray-900">{{ $sale->created_at->format('d/m/Y H:i') }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-gray-900">{{ $sale->user->name ?? 'N/A' }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <span class="text-sm font-semibold text-gray-900">{{ number_format($sale->total, 0, ',', ' ') }} F</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-medium text-red-600">{{ $sale->cancelledBy->name ?? 'N/A' }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-gray-900">{{ $sale->cancelled_at->format('d/m/Y H:i') }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm text-gray-700 line-clamp-2" title="{{ $sale->cancel_reason }}">
                                {{ Str::limit($sale->cancel_reason, 50) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('gerant.sales.show', $sale) }}" class="text-gray-500 hover:text-primary-600 transition-colors" title="Voir details">
                                <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
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
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Aucune annulation</h3>
            <p class="mt-1 text-sm text-gray-500">
                @if(request()->hasAny(['cancelled_by', 'date_from', 'date_to']))
                    Aucune annulation ne correspond a vos criteres.
                @else
                    Aucune vente n'a ete annulee.
                @endif
            </p>
        </div>
        @endif
    </div>
</div>
@endsection

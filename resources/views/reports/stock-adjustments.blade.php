@extends('layouts.app')

@section('title', 'Rapport ajustements de stock')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div>
        <a href="{{ route('gerant.reports.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-primary-600 transition-colors">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Retour aux rapports
        </a>
        <h1 class="mt-2 text-2xl font-bold text-gray-900">Rapport des ajustements de stock</h1>
        <p class="mt-1 text-sm text-gray-500">Analyse des pertes, casses et corrections d'inventaire</p>
    </div>

    <!-- Summary by Type -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        @php
            $typeConfig = [
                'perte' => ['icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z', 'bg' => 'bg-red-100', 'text' => 'text-red-600', 'label' => 'Pertes'],
                'casse' => ['icon' => 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16', 'bg' => 'bg-orange-100', 'text' => 'text-orange-600', 'label' => 'Casses'],
                'inventaire' => ['icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01', 'bg' => 'bg-blue-100', 'text' => 'text-blue-600', 'label' => 'Inventaires'],
                'autre' => ['icon' => 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'bg' => 'bg-gray-100', 'text' => 'text-gray-600', 'label' => 'Autres'],
            ];
        @endphp

        @foreach($typeConfig as $type => $config)
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center">
                <div class="p-3 rounded-full {{ $config['bg'] }} {{ $config['text'] }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $config['icon'] }}"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">{{ $config['label'] }}</p>
                    <p class="text-xl font-bold text-gray-900">
                        {{ number_format($summaryByType[$type]->total_quantity ?? 0) }} unites
                    </p>
                    <p class="text-xs text-gray-500">{{ $summaryByType[$type]->count ?? 0 }} ajustement(s)</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Period Summary -->
    @if($periodSummary)
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <h3 class="font-semibold text-gray-900 mb-4">Resume de la periode filtree</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="text-center p-4 bg-gray-50 rounded-lg">
                <p class="text-sm text-gray-500">Total ajustements</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($periodSummary->total_adjustments ?? 0) }}</p>
            </div>
            <div class="text-center p-4 bg-green-50 rounded-lg">
                <p class="text-sm text-gray-500">Unites ajoutees</p>
                <p class="text-2xl font-bold text-green-600">+{{ number_format($periodSummary->added ?? 0) }}</p>
            </div>
            <div class="text-center p-4 bg-red-50 rounded-lg">
                <p class="text-sm text-gray-500">Unites retirees</p>
                <p class="text-2xl font-bold text-red-600">-{{ number_format($periodSummary->removed ?? 0) }}</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
        <form method="GET" action="{{ route('gerant.reports.stock-adjustments') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Type -->
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                    <select name="type" id="type" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="">Tous les types</option>
                        @foreach($types as $type)
                            <option value="{{ $type }}" {{ request('type') === $type ? 'selected' : '' }}>
                                {{ ucfirst($type) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Product -->
                <div>
                    <label for="product_id" class="block text-sm font-medium text-gray-700 mb-1">Produit</label>
                    <select name="product_id" id="product_id" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="">Tous les produits</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                                {{ $product->name }} ({{ $product->sku }})
                            </option>
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
                @if(request()->hasAny(['type', 'product_id', 'date_from', 'date_to']))
                    <a href="{{ route('gerant.reports.stock-adjustments') }}" class="px-4 py-2 text-gray-500 hover:text-gray-700">
                        Reinitialiser
                    </a>
                @endif
                <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                    Filtrer
                </button>
            </div>
        </form>
    </div>

    <!-- Adjustments Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        @if($adjustments->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produit</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Quantite</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Par</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Raison</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($adjustments as $adjustment)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            {{ $adjustment->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $typeColors = [
                                    'perte' => 'bg-red-100 text-red-800',
                                    'casse' => 'bg-orange-100 text-orange-800',
                                    'inventaire' => 'bg-blue-100 text-blue-800',
                                    'autre' => 'bg-gray-100 text-gray-800',
                                ];
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $typeColors[$adjustment->type] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst($adjustment->type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $adjustment->product->name ?? 'Produit supprime' }}</div>
                            <div class="text-xs text-gray-500">{{ $adjustment->product->sku ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            @if($adjustment->quantity > 0)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    +{{ $adjustment->quantity }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    {{ $adjustment->quantity }}
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            {{ $adjustment->user->name ?? 'Utilisateur supprime' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate" title="{{ $adjustment->reason }}">
                            {{ $adjustment->reason ?? '-' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $adjustments->links() }}
        </div>
        @else
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun ajustement trouve</h3>
            <p class="mt-1 text-sm text-gray-500">
                Aucun ajustement ne correspond aux criteres selectionnes.
            </p>
        </div>
        @endif
    </div>
</div>
@endsection

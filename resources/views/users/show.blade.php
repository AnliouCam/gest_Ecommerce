@extends('layouts.app')

@section('title', 'Detail utilisateur')

@section('content')
<div class="max-w-3xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('gerant.users.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-primary-600 transition-colors">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Retour aux utilisateurs
        </a>
        <div class="flex items-center justify-between mt-2">
            <h1 class="text-2xl font-bold text-gray-900">Detail de l'utilisateur</h1>
            <div class="flex space-x-2">
                <a href="{{ route('gerant.users.edit', $user) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Modifier
                </a>
                @if($user->id !== auth()->id())
                <form action="{{ route('gerant.users.destroy', $user) }}" method="POST" x-data
                    @submit.prevent="if(confirm('Etes-vous sur de vouloir supprimer cet utilisateur ?')) $el.submit()">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-white border border-red-300 text-red-600 rounded-lg hover:bg-red-50 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Supprimer
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- User Info Card -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-primary-500 to-indigo-600 px-6 py-8 text-white text-center">
                    <div class="w-20 h-20 mx-auto rounded-full bg-white/20 flex items-center justify-center">
                        <span class="text-3xl font-bold">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </span>
                    </div>
                    <h2 class="mt-4 text-xl font-semibold">{{ $user->name }}</h2>
                    <p class="text-primary-100">{{ $user->email }}</p>
                    <div class="mt-3">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-white/20">
                            {{ ucfirst($user->role) }}
                        </span>
                    </div>
                </div>

                <div class="p-6 space-y-4">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider">Inscrit depuis</p>
                        <p class="text-sm text-gray-900">{{ $user->created_at->format('d/m/Y') }}</p>
                        <p class="text-xs text-gray-500">{{ $user->created_at->diffForHumans() }}</p>
                    </div>

                    @if($user->id === auth()->id())
                        <div class="bg-primary-50 rounded-lg p-3 border border-primary-100">
                            <p class="text-sm text-primary-700">C'est votre compte</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Stats & Activity -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Statistics -->
            <div class="grid grid-cols-3 gap-4">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
                    <div class="w-10 h-10 mx-auto rounded-lg bg-green-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ $user->sales_count }}</p>
                    <p class="text-xs text-gray-500">Ventes</p>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
                    <div class="w-10 h-10 mx-auto rounded-lg bg-blue-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </div>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ $user->stock_adjustments_count }}</p>
                    <p class="text-xs text-gray-500">Ajustements</p>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
                    <div class="w-10 h-10 mx-auto rounded-lg bg-red-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ $user->cancelled_sales_count }}</p>
                    <p class="text-xs text-gray-500">Annulations</p>
                </div>
            </div>

            <!-- Recent Sales -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-sm font-medium text-gray-900">Dernieres ventes</h3>
                </div>

                @if($recentSales->count() > 0)
                <div class="divide-y divide-gray-200">
                    @foreach($recentSales as $sale)
                    <div class="px-6 py-4 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-900">
                                Vente #{{ $sale->id }}
                                @if($sale->customer)
                                    - {{ $sale->customer->name }}
                                @else
                                    - Client anonyme
                                @endif
                            </p>
                            <p class="text-xs text-gray-500">{{ $sale->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-900">{{ number_format($sale->total, 0, ',', ' ') }} FCFA</p>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $sale->status === 'completed' ? 'bg-green-100 text-green-800' : ($sale->status === 'cancelled' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                {{ ucfirst($sale->status) }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="px-6 py-8 text-center text-gray-500">
                    <p class="text-sm">Aucune vente enregistree</p>
                </div>
                @endif
            </div>

            <!-- Role Permissions -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-sm font-medium text-gray-900">Permissions du role {{ ucfirst($user->role) }}</h3>
                </div>
                <div class="p-6">
                    @if($user->isGerant())
                        <ul class="space-y-2 text-sm text-gray-600">
                            <li class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Gestion complete des produits, categories, fournisseurs
                            </li>
                            <li class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Gestion des entrees de stock
                            </li>
                            <li class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Gestion des utilisateurs
                            </li>
                            <li class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Acces aux rapports et statistiques
                            </li>
                            <li class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Annulation des ventes
                            </li>
                        </ul>
                    @else
                        <ul class="space-y-2 text-sm text-gray-600">
                            <li class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Creation de ventes
                            </li>
                            <li class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Gestion des clients
                            </li>
                            <li class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Consultation des produits
                            </li>
                            <li class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Voir ses propres ventes
                            </li>
                            <li class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                <span class="text-gray-400">Pas d'acces a la gestion (produits, stock, utilisateurs)</span>
                            </li>
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

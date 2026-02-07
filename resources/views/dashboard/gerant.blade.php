@extends('layouts.app')

@section('title', 'Dashboard Gerant')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Tableau de bord</h1>
            <p class="mt-1 text-sm text-gray-500">Bienvenue, {{ auth()->user()->name }}</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-primary-100 text-primary-800">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                </svg>
                Gerant
            </span>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Ventes du jour -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-emerald-100">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Ventes du jour</p>
                    <p class="text-2xl font-bold text-gray-900">0 FCFA</p>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-emerald-600 font-medium">0 ventes</span>
                <span class="text-gray-400 ml-2">aujourd'hui</span>
            </div>
        </div>

        <!-- Ventes du mois -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Ventes du mois</p>
                    <p class="text-2xl font-bold text-gray-900">0 FCFA</p>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-blue-600 font-medium">0 ventes</span>
                <span class="text-gray-400 ml-2">ce mois</span>
            </div>
        </div>

        <!-- Produits -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Produits</p>
                    <p class="text-2xl font-bold text-gray-900">0</p>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-purple-600 font-medium">0 en stock</span>
            </div>
        </div>

        <!-- Alertes Stock -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-amber-100">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Alertes stock</p>
                    <p class="text-2xl font-bold text-gray-900">0</p>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-amber-600 font-medium">Produits en rupture</span>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Actions rapides -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Actions rapides</h2>
            <div class="grid grid-cols-2 gap-4">
                <a href="#" class="flex flex-col items-center p-4 bg-gray-50 rounded-lg hover:bg-primary-50 hover:text-primary-600 transition-colors duration-200 group">
                    <div class="p-3 bg-white rounded-full shadow-sm group-hover:shadow-md transition-shadow">
                        <svg class="w-6 h-6 text-gray-600 group-hover:text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <span class="mt-2 text-sm font-medium">Nouvelle vente</span>
                </a>

                <a href="#" class="flex flex-col items-center p-4 bg-gray-50 rounded-lg hover:bg-primary-50 hover:text-primary-600 transition-colors duration-200 group">
                    <div class="p-3 bg-white rounded-full shadow-sm group-hover:shadow-md transition-shadow">
                        <svg class="w-6 h-6 text-gray-600 group-hover:text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                    </div>
                    <span class="mt-2 text-sm font-medium">Ajouter produit</span>
                </a>

                <a href="#" class="flex flex-col items-center p-4 bg-gray-50 rounded-lg hover:bg-primary-50 hover:text-primary-600 transition-colors duration-200 group">
                    <div class="p-3 bg-white rounded-full shadow-sm group-hover:shadow-md transition-shadow">
                        <svg class="w-6 h-6 text-gray-600 group-hover:text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                    </div>
                    <span class="mt-2 text-sm font-medium">Entree stock</span>
                </a>

                <a href="#" class="flex flex-col items-center p-4 bg-gray-50 rounded-lg hover:bg-primary-50 hover:text-primary-600 transition-colors duration-200 group">
                    <div class="p-3 bg-white rounded-full shadow-sm group-hover:shadow-md transition-shadow">
                        <svg class="w-6 h-6 text-gray-600 group-hover:text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <span class="mt-2 text-sm font-medium">Voir rapports</span>
                </a>
            </div>
        </div>

        <!-- Dernieres ventes -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Dernieres ventes</h2>
                <a href="#" class="text-sm text-primary-600 hover:text-primary-700 font-medium">Voir tout</a>
            </div>
            <div class="text-center py-8 text-gray-500">
                <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p>Aucune vente pour le moment</p>
            </div>
        </div>
    </div>
</div>
@endsection

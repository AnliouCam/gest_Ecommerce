@extends('layouts.app')

@section('title', 'Dashboard Vendeur')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Tableau de bord</h1>
            <p class="mt-1 text-sm text-gray-500">Bienvenue, {{ auth()->user()->name }}</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-emerald-100 text-emerald-800">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                </svg>
                Vendeur
            </span>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Mes ventes du jour -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-emerald-100">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Mes ventes du jour</p>
                    <p class="text-2xl font-bold text-gray-900">0 FCFA</p>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-emerald-600 font-medium">0 ventes</span>
                <span class="text-gray-400 ml-2">aujourd'hui</span>
            </div>
        </div>

        <!-- Nombre de ventes -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total ventes</p>
                    <p class="text-2xl font-bold text-gray-900">0</p>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-blue-600 font-medium">Ce mois</span>
            </div>
        </div>

        <!-- Produits en rupture -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-amber-100">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Produits en rupture</p>
                    <p class="text-2xl font-bold text-gray-900">0</p>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-amber-600 font-medium">A signaler</span>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Nouvelle vente (CTA principal) -->
        <div class="lg:col-span-1">
            <a href="#" class="block bg-gradient-to-br from-primary-500 to-primary-700 rounded-xl shadow-lg p-6 text-white hover:shadow-xl transition-all duration-200 transform hover:scale-[1.02]">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-bold">Nouvelle vente</h3>
                        <p class="mt-1 text-primary-100">Demarrer une transaction</p>
                    </div>
                    <div class="p-4 bg-white/20 rounded-full">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-6 flex items-center text-primary-100">
                    <span>Cliquez pour commencer</span>
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>
                </div>
            </a>

            <!-- Autres actions -->
            <div class="mt-6 bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Autres actions</h3>
                <div class="space-y-3">
                    <a href="#" class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-primary-50 transition-colors duration-200 group">
                        <svg class="w-5 h-5 text-gray-500 group-hover:text-primary-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <span class="text-sm font-medium text-gray-700 group-hover:text-primary-600">Gerer les clients</span>
                    </a>
                    <a href="#" class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-primary-50 transition-colors duration-200 group">
                        <svg class="w-5 h-5 text-gray-500 group-hover:text-primary-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <span class="text-sm font-medium text-gray-700 group-hover:text-primary-600">Mes ventes</span>
                    </a>
                    <a href="#" class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-primary-50 transition-colors duration-200 group">
                        <svg class="w-5 h-5 text-gray-500 group-hover:text-primary-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        <span class="text-sm font-medium text-gray-700 group-hover:text-primary-600">Consulter produits</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Mes dernieres ventes -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Mes dernieres ventes</h2>
                <a href="#" class="text-sm text-primary-600 hover:text-primary-700 font-medium">Voir tout</a>
            </div>
            <div class="text-center py-12 text-gray-500">
                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p class="text-lg font-medium">Aucune vente pour le moment</p>
                <p class="text-sm mt-1">Commencez par effectuer votre premiere vente</p>
                <a href="#" class="mt-4 inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Nouvelle vente
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

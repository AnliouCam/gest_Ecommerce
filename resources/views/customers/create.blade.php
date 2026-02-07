@extends('layouts.app')

@section('title', 'Nouveau client')

@section('content')
<div class="max-w-xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('ventes.customers.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-primary-600 transition-colors">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Retour aux clients
        </a>
        <h1 class="mt-2 text-2xl font-bold text-gray-900">Nouveau client</h1>
        <p class="mt-1 text-sm text-gray-500">Ajoutez un nouveau client a votre base</p>
    </div>

    <!-- Form -->
    <form action="{{ route('ventes.customers.store') }}" method="POST" class="space-y-6">
        @csrf

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
            <!-- Nom -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nom du client *</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('name') border-red-500 @enderror"
                    placeholder="Ex: Jean Dupont">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Telephone -->
            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Telephone *</label>
                <input type="text" name="phone" id="phone" value="{{ old('phone') }}" required
                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('phone') border-red-500 @enderror"
                    placeholder="Ex: +33 6 12 34 56 78">
                @error('phone')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}"
                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('email') border-red-500 @enderror"
                    placeholder="Ex: jean.dupont@email.com">
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Adresse -->
            <div>
                <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Adresse</label>
                <textarea name="address" id="address" rows="3"
                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('address') border-red-500 @enderror"
                    placeholder="Ex: 123 Rue de la Paix, 75001 Paris">{{ old('address') }}</textarea>
                @error('address')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end space-x-4">
            <a href="{{ route('ventes.customers.index') }}" class="px-4 py-2 text-gray-700 hover:text-gray-900 transition-colors">
                Annuler
            </a>
            <button type="submit" class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors shadow-sm">
                Creer le client
            </button>
        </div>
    </form>
</div>
@endsection

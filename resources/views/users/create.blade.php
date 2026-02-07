@extends('layouts.app')

@section('title', 'Nouvel utilisateur')

@section('content')
<div class="max-w-xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('gerant.users.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-primary-600 transition-colors">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Retour aux utilisateurs
        </a>
        <h1 class="mt-2 text-2xl font-bold text-gray-900">Nouvel utilisateur</h1>
        <p class="mt-1 text-sm text-gray-500">Creez un nouveau compte pour un employe</p>
    </div>

    <!-- Form -->
    <form action="{{ route('gerant.users.store') }}" method="POST" class="space-y-6">
        @csrf

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
            <!-- Nom -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nom complet *</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('name') border-red-500 @enderror"
                    placeholder="Ex: Jean Dupont">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Adresse email *</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required
                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('email') border-red-500 @enderror"
                    placeholder="Ex: jean.dupont@boutique.com">
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Role -->
            <div>
                <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Role *</label>
                <select name="role" id="role" required
                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('role') border-red-500 @enderror">
                    <option value="">Selectionnez un role</option>
                    <option value="vendeur" {{ old('role') === 'vendeur' ? 'selected' : '' }}>Vendeur</option>
                    <option value="gerant" {{ old('role') === 'gerant' ? 'selected' : '' }}>Gerant</option>
                </select>
                @error('role')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">
                    <strong>Vendeur :</strong> Peut creer des ventes et gerer les clients<br>
                    <strong>Gerant :</strong> Acces complet a toutes les fonctionnalites
                </p>
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Mot de passe *</label>
                <input type="password" name="password" id="password" required
                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('password') border-red-500 @enderror"
                    placeholder="Minimum 8 caracteres">
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password Confirmation -->
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirmer le mot de passe *</label>
                <input type="password" name="password_confirmation" id="password_confirmation" required
                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                    placeholder="Repetez le mot de passe">
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end space-x-4">
            <a href="{{ route('gerant.users.index') }}" class="px-4 py-2 text-gray-700 hover:text-gray-900 transition-colors">
                Annuler
            </a>
            <button type="submit" class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors shadow-sm">
                Creer l'utilisateur
            </button>
        </div>
    </form>
</div>
@endsection

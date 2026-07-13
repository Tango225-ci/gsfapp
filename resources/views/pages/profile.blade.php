@extends('layouts.app')

@section('content')

<div class="mb-6">
    <h1 class="text-xl font-bold text-gray-900 dark:text-white">Mon profil</h1>
    <p class="text-xs text-gray-400 mt-0.5">Informations de votre compte et sécurité</p>
</div>

@if(session('success'))
<div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700 dark:bg-green-900/20 dark:border-green-800 dark:text-green-400">
    {{ session('success') }}
</div>
@endif

@php $user = auth('utilisateurs')->user(); @endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Carte identité --}}
    <div class="lg:col-span-1">
        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex flex-col items-center text-center">
                <div class="flex items-center justify-center w-20 h-20 rounded-full bg-red-100 text-red-700 font-bold text-3xl mb-4">
                    {{ strtoupper(substr($user->nom, 0, 1)) }}
                </div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $user->nom }}</h2>
                <span class="mt-1 inline-flex items-center rounded-full px-3 py-0.5 text-xs font-medium
                    @if($user->role === 'ADMIN') bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400
                    @elseif($user->role === 'DIRECTEUR') bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400
                    @else bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400
                    @endif">
                    {{ $user->role_label }}
                </span>
            </div>

            <div class="mt-6 space-y-3 border-t border-gray-100 pt-4 dark:border-gray-800">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500 dark:text-gray-400">Identifiant</span>
                    <span class="font-medium text-gray-800 dark:text-gray-200">{{ $user->login }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500 dark:text-gray-400">Email</span>
                    <span class="font-medium text-gray-800 dark:text-gray-200 break-all text-right">{{ $user->email ?? '—' }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500 dark:text-gray-400">Statut</span>
                    <span class="font-medium {{ $user->statut ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                        {{ $user->statut_label }}
                    </span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500 dark:text-gray-400">Membre depuis</span>
                    <span class="font-medium text-gray-800 dark:text-gray-200">{{ $user->date_creation }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Formulaire de modification --}}
    <div class="lg:col-span-2 space-y-6" x-data="{ showPassword: false }">

        {{-- Infos personnelles --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-5">Informations personnelles</h3>

            <form method="POST" action="{{ route('profile.update') }}">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">Nom complet</label>
                        <input type="text" name="nom" value="{{ old('nom', $user->nom) }}"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-red-500 focus:ring-2 focus:ring-red-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:focus:ring-red-900 @error('nom') border-red-500 @enderror"
                            placeholder="Votre nom">
                        @error('nom')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">Adresse email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-red-500 focus:ring-2 focus:ring-red-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:focus:ring-red-900 @error('email') border-red-500 @enderror"
                            placeholder="votre@email.com">
                        @error('email')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Section mot de passe (optionnelle) --}}
                <div class="border-t border-gray-100 pt-4 dark:border-gray-800">
                    <button type="button" @click="showPassword = !showPassword"
                        class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-red-600 dark:hover:text-red-400 transition-colors">
                        <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-90': showPassword }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                        <span x-text="showPassword ? 'Masquer le changement de mot de passe' : 'Changer le mot de passe'"></span>
                    </button>

                    <div x-show="showPassword" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4" style="display:none">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">Nouveau mot de passe</label>
                            <input type="password" name="password" autocomplete="new-password"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-red-500 focus:ring-2 focus:ring-red-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:focus:ring-red-900 @error('password') border-red-500 @enderror"
                                placeholder="8 caractères minimum">
                            @error('password')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">Confirmer le mot de passe</label>
                            <input type="password" name="password_confirmation" autocomplete="new-password"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-red-500 focus:ring-2 focus:ring-red-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:focus:ring-red-900"
                                placeholder="Répétez le mot de passe">
                        </div>

                        <div class="sm:col-span-2">
                            <p class="text-xs text-gray-400 dark:text-gray-500">Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule et un chiffre.</p>
                        </div>
                    </div>
                </div>

                <div class="mt-5 flex justify-end">
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-5 py-2 text-sm font-semibold text-white hover:bg-red-700 transition-colors focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>

@endsection

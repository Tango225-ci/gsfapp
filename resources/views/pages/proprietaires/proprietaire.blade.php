@extends('layouts.app')

@section('content')

<div x-data="proprietairesApp()" x-init="init()" class="space-y-6">

{{-- En-tête page --}}
<div class="flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-xl font-bold text-gray-900 dark:text-white">Gestion des Propriétaires</h1>
        <p class="text-xs text-gray-400 mt-0.5">Propriétaires fonciers, lots associés et suivi des versements</p>
    </div>
    <button @click="openCreate()"
        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-brand-500 rounded-lg hover:bg-brand-600 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Nouveau propriétaire
    </button>
</div>

{{-- Stats globales --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4" x-show="proprietaires.length > 0">
    <div class="rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        </div>
        <div>
            <p class="text-xs text-gray-500 dark:text-gray-400">Propriétaires</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white" x-text="proprietaires.length"></p>
        </div>
    </div>
    <div class="rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-purple-50 dark:bg-purple-900/20 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
        </div>
        <div>
            <p class="text-xs text-gray-500 dark:text-gray-400">Total lots</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white" x-text="totaux.lots"></p>
        </div>
    </div>
    <div class="rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-orange-50 dark:bg-orange-900/20 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 002 2v6a2 2 0 002 2z"/></svg>
        </div>
        <div>
            <p class="text-xs text-gray-500 dark:text-gray-400">Total dû (prix achat)</p>
            <p class="text-sm font-bold text-orange-600 dark:text-orange-400 mt-0.5" x-text="fmt(totaux.prix_achat)"></p>
        </div>
    </div>
    <div class="rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-green-50 dark:bg-green-900/20 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
            <p class="text-xs text-gray-500 dark:text-gray-400">Total versé</p>
            <p class="text-sm font-bold text-green-600 dark:text-green-400 mt-0.5" x-text="fmt(totaux.verse)"></p>
        </div>
    </div>
</div>

{{-- Liste des propriétaires --}}
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">

    <template x-if="loading">
        <div class="col-span-full flex justify-center py-16">
            <div class="w-8 h-8 border-4 border-brand-500 border-t-transparent rounded-full animate-spin"></div>
        </div>
    </template>

    <template x-if="!loading && proprietaires.length === 0">
        <div class="col-span-full text-center py-16 text-gray-400 dark:text-gray-600">
            <svg class="w-12 h-12 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <p class="text-sm">Aucun propriétaire terrien enregistré</p>
        </div>
    </template>

    <template x-for="p in proprietaires" :key="p.id_proprietaire">
        <div class="rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 overflow-hidden hover:shadow-md transition-shadow">

            {{-- Header carte --}}
            <div class="p-4 flex items-start justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-full bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center shrink-0">
                        <span class="text-white font-bold text-base" x-text="p.nom.charAt(0).toUpperCase()"></span>
                    </div>
                    <div>
                        <p class="font-semibold text-sm text-gray-900 dark:text-white" x-text="p.nom"></p>
                        <p class="text-xs text-gray-400 mt-0.5" x-text="p.contact || 'Aucun contact'"></p>
                    </div>
                </div>
                <div class="flex items-center gap-0.5 shrink-0 ml-2">
                    <button @click="openEdit(p)" title="Modifier"
                        class="p-1.5 rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </button>
                    <button @click="openDelete(p)" title="Supprimer"
                        class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </div>

            {{-- Statuts lots --}}
            <div class="mx-4 mb-3 rounded-lg bg-gray-50 dark:bg-gray-800/50 grid grid-cols-3 divide-x divide-gray-200 dark:divide-gray-700 text-center py-2">
                <div>
                    <p class="text-xs text-gray-400 dark:text-gray-500">Disponible</p>
                    <p class="text-base font-bold text-green-600 dark:text-green-400" x-text="p.stats.disponible"></p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 dark:text-gray-500">Réservé</p>
                    <p class="text-base font-bold text-orange-500 dark:text-orange-400" x-text="p.stats.reserve"></p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 dark:text-gray-500">Vendu</p>
                    <p class="text-base font-bold text-red-600 dark:text-red-400" x-text="p.stats.vendu"></p>
                </div>
            </div>

            {{-- Financier + progression --}}
            <div class="mx-4 mb-4 rounded-lg border border-gray-100 dark:border-gray-800 bg-gray-50/70 dark:bg-gray-800/40 p-3">
                <div class="space-y-1.5">
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-gray-500 dark:text-gray-400">Total dû</span>
                        <span class="font-semibold text-gray-700 dark:text-gray-300" x-text="fmt(p.stats.total_prix_achat)"></span>
                    </div>
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-gray-500 dark:text-gray-400">Versé</span>
                        <span class="font-semibold text-green-600 dark:text-green-400" x-text="fmt(p.stats.total_verse)"></span>
                    </div>
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-gray-500 dark:text-gray-400">Reste</span>
                        <span class="font-bold"
                            :class="p.stats.reste_a_verser > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400'"
                            x-text="fmt(p.stats.reste_a_verser)"></span>
                    </div>
                </div>

                {{-- Barre progression --}}
                <div class="mt-2.5" x-show="p.stats.total_prix_achat > 0">
                    <div class="flex justify-between text-xs text-gray-400 mb-1">
                        <span class="font-medium">Progression</span>
                        <span class="font-semibold text-gray-600 dark:text-gray-300" x-text="Math.min(100, Math.round(p.stats.total_verse / p.stats.total_prix_achat * 100)) + '%'"></span>
                    </div>
                    <div class="h-2.5 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden shadow-inner">
                        <div class="h-full rounded-full transition-all duration-500"
                             :class="Math.round(p.stats.total_verse / p.stats.total_prix_achat * 100) >= 100 ? 'bg-gradient-to-r from-green-500 to-emerald-500' : 'bg-gradient-to-r from-brand-400 to-brand-600'"
                             :style="'width:' + Math.min(100, Math.round(p.stats.total_verse / p.stats.total_prix_achat * 100)) + '%'">
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-4 pb-4">
                <button @click="openDetail(p)" :disabled="showDetail && currentDetailId === p.id_proprietaire && !detail"
                    class="w-full flex items-center justify-center gap-1.5 py-2.5 text-xs font-semibold text-white bg-brand-600 rounded-lg shadow-sm hover:bg-brand-700 hover:shadow-md active:scale-[0.98] disabled:opacity-80 transition-all">
                    <template x-if="!(showDetail && currentDetailId === p.id_proprietaire && !detail)">
                        <span class="flex items-center gap-1.5">
                            Voir détails & versements
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </span>
                    </template>
                    <template x-if="showDetail && currentDetailId === p.id_proprietaire && !detail">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
                            Chargement…
                        </span>
                    </template>
                </button>
            </div>
        </div>
    </template>
</div>

{{-- ════════════════════════ MODAL CRÉER / MODIFIER ════════════════════════ --}}
<template x-teleport="body">
<div x-show="showForm" x-cloak class="fixed inset-0 z-[100000] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showForm = false"></div>
    <div class="relative w-full max-w-lg bg-white dark:bg-gray-900 rounded-2xl shadow-2xl overflow-hidden" @click.stop>
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-800">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white"
                x-text="editId ? 'Modifier le propriétaire' : 'Nouveau propriétaire'"></h2>
            <button @click="showForm = false" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form @submit.prevent="saveForm()" class="p-6 space-y-4">
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Nom complet <span class="text-red-500">*</span></label>
                <input type="text" x-model="form.nom" required placeholder="Nom du propriétaire"
                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none"/>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Téléphone</label>
                    <input type="text" x-model="form.contact" placeholder="07 XX XX XX XX"
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none"/>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                    <input type="email" x-model="form.email" placeholder="email@exemple.com"
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none"/>
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Adresse</label>
                <input type="text" x-model="form.adresse" placeholder="Adresse complète"
                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none"/>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Note</label>
                <textarea x-model="form.note" rows="2" placeholder="Remarques…"
                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none resize-none"></textarea>
            </div>
            <p x-show="formError" x-text="formError" class="text-xs text-red-600"></p>
            <div class="flex gap-3 pt-2">
                <button type="button" @click="showForm = false"
                    class="flex-1 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                    Annuler
                </button>
                <button type="submit" :disabled="saving"
                    class="flex-1 py-2 text-sm font-medium text-white bg-brand-500 rounded-lg hover:bg-brand-600 disabled:opacity-50 transition-colors">
                    <span x-show="!saving" x-text="editId ? 'Enregistrer' : 'Créer'"></span>
                    <span x-show="saving">Enregistrement…</span>
                </button>
            </div>
        </form>
    </div>
</div>
</template>

{{-- ════════════════════════ MODAL DÉTAIL ════════════════════════ --}}
<template x-teleport="body">
<div x-show="showDetail" x-cloak class="fixed inset-0 z-[100000] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showDetail = false; showVForm = false"></div>

    <div class="relative w-full max-w-4xl bg-white dark:bg-gray-900 rounded-2xl shadow-2xl flex flex-col overflow-hidden"
         style="max-height: 90vh"
         @click.stop>

        {{-- ── Chargement ── --}}
        <div x-show="!detail" class="flex flex-col items-center justify-center py-20 gap-3">
            <div class="w-8 h-8 border-4 border-brand-500 border-t-transparent rounded-full animate-spin"></div>
            <p class="text-sm text-gray-400">Chargement…</p>
        </div>

        {{-- ── Contenu ── --}}
        <template x-if="detail">
            <div class="flex flex-col min-h-0 flex-1">

                {{-- Header --}}
                <div class="flex flex-col sm:flex-row items-start justify-between gap-3 px-4 sm:px-6 py-5 border-b border-gray-100 dark:border-gray-800 shrink-0">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center shrink-0">
                            <span class="text-white font-bold text-xl" x-text="detail.proprietaire?.nom?.charAt(0)?.toUpperCase()"></span>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white" x-text="detail.proprietaire?.nom"></h2>
                            <div class="flex flex-wrap items-center gap-3 mt-1">
                                <span x-show="detail.proprietaire?.contact" class="inline-flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                    <span x-text="detail.proprietaire?.contact"></span>
                                </span>
                                <span x-show="detail.proprietaire?.email" class="inline-flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    <span x-text="detail.proprietaire?.email"></span>
                                </span>
                                <span x-show="detail.proprietaire?.adresse" class="inline-flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <span x-text="detail.proprietaire?.adresse"></span>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 shrink-0">
                        <button @click="printFicheProprietaire()"
                            class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-300 border border-gray-300 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                            title="Fiche à remettre au propriétaire (sans données financières internes)">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            Fiche propriétaire
                        </button>
                        <button @click="printFicheComplete()"
                            class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-white bg-brand-500 border border-brand-500 rounded-lg hover:bg-brand-600 transition-colors"
                            title="Fiche interne complète avec analyse financière GSF">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            Fiche interne
                        </button>
                        <button @click="showDetail = false; showVForm = false"
                            class="p-2 rounded-xl text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>

                {{-- Stats financières --}}
                <div class="grid grid-cols-2 md:grid-cols-4 gap-0 border-b border-gray-100 dark:border-gray-800 shrink-0 divide-x divide-gray-100 dark:divide-gray-800">
                    <div class="px-5 py-4 text-center">
                        <p class="text-xs text-gray-400 dark:text-gray-500">Lots totaux</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-0.5" x-text="detail.stats?.nb_lots ?? 0"></p>
                        <div class="flex justify-center gap-2 mt-1">
                            <span class="text-xs text-green-600 dark:text-green-400" x-text="(detail.stats?.disponible ?? 0) + ' dispo'"></span>
                            <span class="text-xs text-orange-500" x-text="(detail.stats?.reserve ?? 0) + ' rés.'"></span>
                            <span class="text-xs text-red-600" x-text="(detail.stats?.vendu ?? 0) + ' vdus'"></span>
                        </div>
                    </div>
                    <div class="px-5 py-4 text-center">
                        <p class="text-xs text-gray-400 dark:text-gray-500">Total dû</p>
                        <p class="text-base font-bold text-gray-900 dark:text-white mt-0.5" x-text="fmt(detail.stats?.total_prix_achat ?? 0)"></p>
                        <div class="h-1.5 bg-gray-100 dark:bg-gray-800 rounded-full mt-2 overflow-hidden" x-show="(detail.stats?.total_prix_achat ?? 0) > 0">
                            <div class="h-full bg-brand-500 rounded-full"
                                 :style="'width:' + Math.min(100, Math.round((detail.stats?.total_verse ?? 0) / (detail.stats?.total_prix_achat ?? 1) * 100)) + '%'">
                            </div>
                        </div>
                    </div>
                    <div class="px-5 py-4 text-center">
                        <p class="text-xs text-gray-400 dark:text-gray-500">Total versé</p>
                        <p class="text-base font-bold text-green-600 dark:text-green-400 mt-0.5" x-text="fmt(detail.stats?.total_verse ?? 0)"></p>
                        <p class="text-xs text-gray-400 mt-1"
                           x-text="Math.min(100, Math.round((detail.stats?.total_verse ?? 0) / Math.max(1, detail.stats?.total_prix_achat ?? 1) * 100)) + '% payé'">
                        </p>
                    </div>
                    <div class="px-5 py-4 text-center">
                        <p class="text-xs text-gray-400 dark:text-gray-500"
                           x-text="(detail.stats?.reste_a_verser ?? 0) < 0 ? 'Avance versée' : 'Reste à verser'"></p>
                        <p class="text-base font-bold mt-0.5"
                           :class="{
                               'text-red-600 dark:text-red-400':    (detail.stats?.reste_a_verser ?? 0) > 0,
                               'text-green-600 dark:text-green-400': (detail.stats?.reste_a_verser ?? 0) === 0,
                               'text-purple-600 dark:text-purple-400': (detail.stats?.reste_a_verser ?? 0) < 0
                           }"
                           x-text="fmt(Math.abs(detail.stats?.reste_a_verser ?? 0))">
                        </p>
                        <p class="text-xs mt-1"
                           :class="{
                               'text-red-400':    (detail.stats?.reste_a_verser ?? 0) > 0,
                               'text-green-500':  (detail.stats?.reste_a_verser ?? 0) === 0,
                               'text-purple-400': (detail.stats?.reste_a_verser ?? 0) < 0
                           }"
                           x-text="(detail.stats?.reste_a_verser ?? 0) > 0 ? 'En cours' : (detail.stats?.reste_a_verser ?? 0) < 0 ? '↑ GSF a trop versé' : '✓ Soldé'">
                        </p>
                    </div>
                </div>

                {{-- Onglets --}}
                <div x-data="{ tab: 'lots' }" class="flex flex-col flex-1 min-h-0">

                    {{-- Nav onglets --}}
                    <div class="px-6 border-b border-gray-100 dark:border-gray-800 shrink-0">
                        <nav class="-mb-px flex gap-1">
                            <button @click="tab = 'lots'"
                                :class="tab === 'lots' ? 'border-brand-500 text-brand-600 dark:text-brand-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="flex items-center gap-2 px-3 py-3 text-sm font-medium border-b-2 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                                Lots
                                <span class="ml-1 px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400"
                                      x-text="detail.lots?.length ?? 0"></span>
                            </button>
                            <button @click="tab = 'versements'"
                                :class="tab === 'versements' ? 'border-brand-500 text-brand-600 dark:text-brand-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="flex items-center gap-2 px-3 py-3 text-sm font-medium border-b-2 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 002 2v6a2 2 0 002 2z"/></svg>
                                Versements
                                <span class="ml-1 px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400"
                                      x-text="detail.versements?.length ?? 0"></span>
                            </button>
                            <button @click="tab = 'analyse'"
                                :class="tab === 'analyse' ? 'border-emerald-500 text-emerald-600 dark:text-emerald-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="flex items-center gap-2 px-3 py-3 text-sm font-medium border-b-2 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                Analyse financière
                            </button>
                        </nav>
                    </div>

                    {{-- Contenu onglets (scrollable) --}}
                    <div class="overflow-y-auto" style="max-height: calc(90vh - 210px)">

                        {{-- ── Onglet LOTS ── --}}
                        <div x-show="tab === 'lots'" class="p-6">
                            <template x-if="!detail.lots?.length">
                                <div class="text-center py-10 text-gray-400 dark:text-gray-600">
                                    <svg class="w-10 h-10 mx-auto mb-2 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                                    <p class="text-sm">Aucun lot associé à ce propriétaire</p>
                                </div>
                            </template>

                            <div x-show="detail.lots?.length" class="overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-800">
                                <table class="w-full text-sm">
                                    <thead class="bg-gray-50 dark:bg-gray-800/50">
                                        <tr class="text-left text-xs text-gray-500 uppercase tracking-wider">
                                            <th class="px-4 py-3 font-medium">Lot</th>
                                            <th class="px-4 py-3 font-medium">Lotissement</th>
                                            <th class="px-4 py-3 font-medium">Superficie</th>
                                            <th class="px-4 py-3 font-medium text-right">Prix achat</th>
                                            <th class="px-4 py-3 font-medium text-right">Prix vente</th>
                                            <th class="px-4 py-3 font-medium text-center">Statut</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                                        <template x-for="lot in detail.lots" :key="lot.id_lot">
                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30">
                                                <td class="px-4 py-3 font-semibold text-gray-900 dark:text-white" x-text="lot.numero_lot"></td>
                                                <td class="px-4 py-3 text-gray-500 dark:text-gray-400" x-text="lot.lotissement_nom || '—'"></td>
                                                <td class="px-4 py-3 text-gray-500 dark:text-gray-400" x-text="lot.superficie ? lot.superficie + ' m²' : '—'"></td>
                                                <td class="px-4 py-3 text-right font-semibold text-orange-600 dark:text-orange-400" x-text="fmt(lot.prix_achat || 0)"></td>
                                                <td class="px-4 py-3 text-right text-gray-600 dark:text-gray-400" x-text="fmt(Math.max(parseFloat(lot.prix_comptant||0), parseFloat(lot.prix_echelonne||0)))"></td>
                                                <td class="px-4 py-3 text-center">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                                                          :class="{
                                                            'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400': lot.statut === 'DISPONIBLE',
                                                            'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400': lot.statut === 'RESERVE',
                                                            'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400': lot.statut === 'VENDU'
                                                          }"
                                                          x-text="lot.statut_label || lot.statut">
                                                    </span>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- ── Onglet VERSEMENTS ── --}}
                        <div x-show="tab === 'versements'" class="p-6 space-y-4">

                            {{-- Titre + bouton --}}
                            <div class="flex items-center justify-between">
                                <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">Historique des versements</p>
                                <button @click="showVForm = true; versementError = ''; versementForm.montant = ''; versementForm.note = ''"
                                    class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-white bg-brand-500 rounded-lg hover:bg-brand-600 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    Nouveau versement
                                </button>
                            </div>

                            {{-- Historique --}}
                            <template x-if="!detail.versements?.length">
                                <div class="text-center py-10 text-gray-400 dark:text-gray-600">
                                    <svg class="w-10 h-10 mx-auto mb-2 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                    <p class="text-sm">Aucun versement enregistré</p>
                                    <p class="text-xs mt-1">Cliquez sur "Nouveau versement" pour commencer</p>
                                </div>
                            </template>

                            <div x-show="detail.versements?.length" class="overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-800">
                                <table class="w-full text-sm">
                                    <thead class="bg-gray-50 dark:bg-gray-800/50">
                                        <tr class="text-left text-xs text-gray-500 uppercase tracking-wider">
                                            <th class="px-4 py-3 font-medium">Référence</th>
                                            <th class="px-4 py-3 font-medium">Date</th>
                                            <th class="px-4 py-3 font-medium">Mode</th>
                                            <th class="px-4 py-3 font-medium text-right">Montant</th>
                                            <th class="px-4 py-3 font-medium">Note</th>
                                            <th class="px-4 py-3 font-medium text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                                        <template x-for="v in detail.versements" :key="v.id_versement">
                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30">
                                                <td class="px-4 py-3 font-mono text-xs text-gray-500 dark:text-gray-400" x-text="v.reference"></td>
                                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300 whitespace-nowrap" x-text="v.date_versement_formatee || v.date_versement"></td>
                                                <td class="px-4 py-3 text-gray-500 dark:text-gray-400" x-text="v.mode_paiement_label || v.mode_paiement"></td>
                                                <td class="px-4 py-3 text-right font-semibold text-green-600 dark:text-green-400 whitespace-nowrap" x-text="v.montant_formate || fmt(v.montant)"></td>
                                                <td class="px-4 py-3 text-gray-400 dark:text-gray-500" x-text="v.note || '—'"></td>
                                                <td class="px-4 py-3 text-center">
                                                    <button @click="deleteVersement(v)"
                                                        class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                                                        title="Supprimer">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                    <tfoot class="sticky bottom-0 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-200 dark:border-gray-700">
                                        <tr>
                                            <td colspan="3" class="px-4 py-2 text-xs font-semibold text-gray-600 dark:text-gray-400">Total versé</td>
                                            <td class="px-4 py-2 text-right text-sm font-bold text-green-600 dark:text-green-400" x-text="fmt(detail.stats?.total_verse ?? 0)"></td>
                                            <td colspan="2"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            {{-- ── Sous-modal formulaire versement ── --}}
                            <template x-teleport="body">
                            <div x-show="showVForm" x-cloak class="fixed inset-0 z-[200000] flex items-center justify-center p-4">
                                <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="showVForm = false"></div>
                                <div class="relative w-full max-w-md bg-white dark:bg-gray-900 rounded-2xl shadow-2xl" @click.stop>

                                    <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 dark:border-gray-800">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-xl bg-brand-100 dark:bg-brand-900/30 flex items-center justify-center shrink-0">
                                                <svg class="w-4 h-4 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                            </div>
                                            <div>
                                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Nouveau versement</h3>
                                                <p class="text-xs text-gray-400 dark:text-gray-500" x-text="detail?.proprietaire?.nom"></p>
                                            </div>
                                        </div>
                                        <button @click="showVForm = false" class="p-2 rounded-xl text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>

                                    <form @submit.prevent="saveVersement()" class="p-6 space-y-4">

                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Montant (FCFA) <span class="text-red-500">*</span></label>
                                                <input type="number" x-model="versementForm.montant" min="1" step="1" required placeholder="Ex : 500 000"
                                                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500 outline-none"/>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Date <span class="text-red-500">*</span></label>
                                                <input type="date" x-model="versementForm.date_versement" required
                                                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500 outline-none"/>
                                            </div>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Mode de paiement <span class="text-red-500">*</span></label>
                                            <select x-model="versementForm.mode_paiement" required
                                                @change="versementForm.banque = ''; versementForm.operateur = ''; versementForm.reference_cheque = ''"
                                                class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500 outline-none">
                                                <option value="ESPECES">Espèces</option>
                                                <option value="VIREMENT">Virement bancaire</option>
                                                <option value="CHEQUE">Chèque</option>
                                                <option value="MOBILE_MONEY">Mobile Money</option>
                                            </select>
                                        </div>

                                        {{-- Mobile Money → Opérateur --}}
                                        <div x-show="versementForm.mode_paiement === 'MOBILE_MONEY'">
                                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Opérateur <span class="text-red-500">*</span></label>
                                            <select x-model="versementForm.operateur"
                                                :required="versementForm.mode_paiement === 'MOBILE_MONEY'"
                                                class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500 outline-none">
                                                <option value="">Sélectionnez un opérateur</option>
                                                <option value="Orange Money">Orange Money</option>
                                                <option value="MTN Money">MTN Money</option>
                                                <option value="Wave">Wave</option>
                                                <option value="Moov Money">Moov Money</option>
                                                <option value="Autre">Autre</option>
                                            </select>
                                        </div>

                                        {{-- Virement → Banque --}}
                                        <div x-show="versementForm.mode_paiement === 'VIREMENT'">
                                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Banque <span class="text-red-500">*</span></label>
                                            <input type="text" x-model="versementForm.banque"
                                                :required="versementForm.mode_paiement === 'VIREMENT'"
                                                placeholder="Ex : SGBCI, BIAO, Ecobank…"
                                                class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500 outline-none"/>
                                        </div>

                                        {{-- Chèque → Banque + Référence --}}
                                        <div x-show="versementForm.mode_paiement === 'CHEQUE'" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Banque <span class="text-red-500">*</span></label>
                                                <input type="text" x-model="versementForm.banque"
                                                    :required="versementForm.mode_paiement === 'CHEQUE'"
                                                    placeholder="Ex : SGBCI, BIAO…"
                                                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500 outline-none"/>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">N° de chèque <span class="text-red-500">*</span></label>
                                                <input type="text" x-model="versementForm.reference_cheque"
                                                    :required="versementForm.mode_paiement === 'CHEQUE'"
                                                    placeholder="Ex : 0012345"
                                                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500 outline-none"/>
                                            </div>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Note (optionnel)</label>
                                            <input type="text" x-model="versementForm.note" placeholder="Détails du versement…"
                                                class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500 outline-none"/>
                                        </div>

                                        <p x-show="versementError" x-text="versementError" class="text-xs text-red-600"></p>

                                        <div class="flex gap-3 pt-1">
                                            <button type="button" @click="showVForm = false"
                                                class="flex-1 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                                Annuler
                                            </button>
                                            <button type="submit" :disabled="savingVersement"
                                                class="flex-1 py-2.5 text-sm font-medium text-white bg-brand-500 rounded-lg hover:bg-brand-600 disabled:opacity-50 transition-colors">
                                                <span x-show="!savingVersement">Enregistrer</span>
                                                <span x-show="savingVersement">Enregistrement…</span>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            </template>

                        </div>

                        {{-- ── Onglet ANALYSE FINANCIÈRE ── --}}
                        <div x-show="tab === 'analyse'" class="p-6 space-y-5">

                            {{-- Résumé en 3 blocs --}}
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                                {{-- 1. Marge théorique GSF --}}
                                <div class="rounded-xl border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-900/10 p-4">
                                    <div class="flex items-center gap-2 mb-3">
                                        <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                                        </div>
                                        <p class="text-xs font-semibold text-emerald-700 dark:text-emerald-400">Marge théorique GSF</p>
                                    </div>
                                    <p class="text-xl font-bold mb-3"
                                       :class="(detail.stats?.gain_theorique ?? 0) >= 0 ? 'text-emerald-700 dark:text-emerald-300' : 'text-red-600'"
                                       x-text="((detail.stats?.gain_theorique ?? 0) >= 0 ? '+' : '') + fmt(detail.stats?.gain_theorique ?? 0)">
                                    </p>
                                    <div class="space-y-1.5 border-t border-emerald-200 dark:border-emerald-800 pt-3">
                                        <div class="flex justify-between text-xs">
                                            <span class="text-gray-500">Prix de vente (lots)</span>
                                            <span class="font-medium text-gray-700 dark:text-gray-300" x-text="fmt(detail.stats?.total_prix_vente_theorique ?? 0)"></span>
                                        </div>
                                        <div class="flex justify-between text-xs">
                                            <span class="text-gray-500">Prix d'achat (proprio)</span>
                                            <span class="font-medium text-orange-600" x-text="'− ' + fmt(detail.stats?.total_prix_achat ?? 0)"></span>
                                        </div>
                                    </div>
                                    <p class="text-xs text-emerald-600 dark:text-emerald-400 mt-2 italic">Si tous les lots sont vendus au prix affiché</p>
                                </div>

                                {{-- 2. Encaissements clients --}}
                                <div class="rounded-xl border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/10 p-4">
                                    <div class="flex items-center gap-2 mb-3">
                                        <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 002 2v6a2 2 0 002 2z"/></svg>
                                        </div>
                                        <p class="text-xs font-semibold text-blue-700 dark:text-blue-400">Encaissements clients</p>
                                    </div>
                                    <p class="text-xl font-bold text-blue-700 dark:text-blue-300 mb-3"
                                       x-text="fmt(detail.stats?.encaissements_clients ?? 0)">
                                    </p>
                                    <div class="space-y-1.5 border-t border-blue-200 dark:border-blue-800 pt-3">
                                        <div class="flex justify-between text-xs">
                                            <span class="text-gray-500">Paiements validés</span>
                                            <span class="font-medium text-gray-700 dark:text-gray-300" x-text="fmt(detail.stats?.encaissements_clients ?? 0)"></span>
                                        </div>
                                        <div class="flex justify-between text-xs">
                                            <span class="text-gray-500">Lots concernés</span>
                                            <span class="font-medium text-gray-700 dark:text-gray-300" x-text="(detail.stats?.nb_lots ?? 0) + ' lot(s)'"></span>
                                        </div>
                                    </div>
                                    <p class="text-xs text-blue-600 dark:text-blue-400 mt-2 italic">Paiements clients reçus sur ces terrains</p>
                                </div>

                                {{-- 3. Position trésorerie --}}
                                <div class="rounded-xl border p-4"
                                     :class="(detail.stats?.position_tresorerie ?? 0) >= 0
                                         ? 'border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/10'
                                         : 'border-orange-200 dark:border-orange-800 bg-orange-50 dark:bg-orange-900/10'">
                                    <div class="flex items-center gap-2 mb-3">
                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center"
                                             :class="(detail.stats?.position_tresorerie ?? 0) >= 0 ? 'bg-green-100 dark:bg-green-900/30' : 'bg-orange-100 dark:bg-orange-900/30'">
                                            <svg class="w-4 h-4" :class="(detail.stats?.position_tresorerie ?? 0) >= 0 ? 'text-green-600' : 'text-orange-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                                        </div>
                                        <p class="text-xs font-semibold"
                                           :class="(detail.stats?.position_tresorerie ?? 0) >= 0 ? 'text-green-700 dark:text-green-400' : 'text-orange-700 dark:text-orange-400'">
                                            Position trésorerie GSF
                                        </p>
                                    </div>
                                    <p class="text-xl font-bold mb-3"
                                       :class="(detail.stats?.position_tresorerie ?? 0) >= 0 ? 'text-green-700 dark:text-green-300' : 'text-orange-700 dark:text-orange-300'"
                                       x-text="((detail.stats?.position_tresorerie ?? 0) >= 0 ? '+' : '') + fmt(detail.stats?.position_tresorerie ?? 0)">
                                    </p>
                                    <div class="space-y-1.5 border-t pt-3"
                                         :class="(detail.stats?.position_tresorerie ?? 0) >= 0 ? 'border-green-200 dark:border-green-800' : 'border-orange-200 dark:border-orange-800'">
                                        <div class="flex justify-between text-xs">
                                            <span class="text-gray-500">Reçu des clients</span>
                                            <span class="font-medium text-gray-700 dark:text-gray-300" x-text="fmt(detail.stats?.encaissements_clients ?? 0)"></span>
                                        </div>
                                        <div class="flex justify-between text-xs">
                                            <span class="text-gray-500">Versé au proprio</span>
                                            <span class="font-medium text-orange-600" x-text="'− ' + fmt(detail.stats?.total_verse ?? 0)"></span>
                                        </div>
                                    </div>
                                    <p class="text-xs mt-2 italic font-medium"
                                       :class="(detail.stats?.position_tresorerie ?? 0) >= 0 ? 'text-green-600 dark:text-green-400' : 'text-orange-600 dark:text-orange-400'"
                                       x-text="(detail.stats?.position_tresorerie ?? 0) >= 0
                                           ? '✓ GSF a encaissé plus que versé au proprio'
                                           : '⚠ GSF a versé ' + fmt(Math.abs(detail.stats?.position_tresorerie ?? 0)) + ' avant encaissement'">
                                    </p>
                                </div>
                            </div>

                        </div>{{-- fin onglet analyse --}}

                    </div>{{-- fin scroll --}}
                </div>{{-- fin onglets --}}

            </div>{{-- fin contenu --}}
        </template>
    </div>
</div>
</template>

{{-- ════════════════════════ MODAL SUPPRESSION ════════════════════════ --}}
<template x-teleport="body">
<div x-show="showDeleteConfirm" x-cloak class="fixed inset-0 z-[100000] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showDeleteConfirm = false"></div>
    <div class="relative w-full max-w-sm bg-white dark:bg-gray-900 rounded-2xl shadow-2xl p-6" @click.stop>
        <div class="w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center mx-auto mb-4">
            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>
        <h3 class="text-center font-semibold text-gray-900 dark:text-white mb-1">Supprimer le propriétaire ?</h3>
        <p class="text-center text-sm text-gray-500 dark:text-gray-400 mb-1" x-text="toDelete?.nom"></p>
        <p class="text-center text-xs text-red-500 mb-5">Seuls les propriétaires sans lots actifs peuvent être supprimés.</p>
        <p x-show="deleteError" x-text="deleteError" class="text-xs text-red-600 text-center mb-4"></p>
        <div class="flex gap-3">
            <button @click="showDeleteConfirm = false"
                class="flex-1 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                Annuler
            </button>
            <button @click="confirmDelete()" :disabled="saving"
                class="flex-1 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 disabled:opacity-50 transition-colors">
                <span x-show="!saving">Supprimer</span>
                <span x-show="saving">…</span>
            </button>
        </div>
    </div>
</div>
</template>

</div>

<script>
function proprietairesApp() {
    return {
        proprietaires: [],
        loading: true,
        totaux: { lots: 0, prix_achat: 0, verse: 0 },

        showForm: false,
        editId: null,
        form: { nom: '', contact: '', email: '', adresse: '', note: '' },
        formError: '',
        saving: false,

        showDetail: false,
        detail: null,
        currentDetailId: null,
        showVForm: false,
        versementForm: {
            montant: '',
            date_versement: new Date().toISOString().substring(0, 10),
            mode_paiement: 'ESPECES',
            banque: '',
            operateur: '',
            reference_cheque: '',
            note: '',
        },
        versementError: '',
        savingVersement: false,

        showDeleteConfirm: false,
        toDelete: null,
        deleteError: '',

        async init() {
            await this.load();
        },

        async load() {
            this.loading = true;
            try {
                const r = await fetch('/api/proprietaires');
                this.proprietaires = await r.json();
                this.computeTotaux();
            } catch (e) {
                console.error(e);
            } finally {
                this.loading = false;
            }
        },

        computeTotaux() {
            this.totaux = this.proprietaires.reduce((acc, p) => {
                acc.lots      += p.stats?.nb_lots         ?? 0;
                acc.prix_achat += p.stats?.total_prix_achat ?? 0;
                acc.verse      += p.stats?.total_verse      ?? 0;
                return acc;
            }, { lots: 0, prix_achat: 0, verse: 0 });
        },

        fmt(val) {
            return new Intl.NumberFormat('fr-FR').format(Math.round(val || 0)) + ' FCFA';
        },

        openCreate() {
            this.editId = null;
            this.form = { nom: '', contact: '', email: '', adresse: '', note: '' };
            this.formError = '';
            this.showForm = true;
        },

        openEdit(p) {
            this.editId = p.id_proprietaire;
            this.form = { nom: p.nom, contact: p.contact || '', email: p.email || '', adresse: p.adresse || '', note: p.note || '' };
            this.formError = '';
            this.showForm = true;
        },

        async saveForm() {
            this.saving = true;
            this.formError = '';
            try {
                const url  = this.editId ? `/api/proprietaires/${this.editId}` : '/api/proprietaires';
                const meth = this.editId ? 'PUT' : 'POST';
                const r    = await fetch(url, {
                    method: meth,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify(this.form),
                });
                const data = await r.json();
                if (!r.ok) { this.formError = data.message || 'Erreur'; return; }
                this.showForm = false;
                await this.load();
                window.gsfToast?.('Propriétaire enregistré', 'success');
            } catch (e) {
                this.formError = 'Erreur réseau';
            } finally {
                this.saving = false;
            }
        },

        openDelete(p) {
            this.toDelete = p;
            this.deleteError = '';
            this.showDeleteConfirm = true;
        },

        async confirmDelete() {
            this.saving = true;
            this.deleteError = '';
            try {
                const r    = await fetch(`/api/proprietaires/${this.toDelete.id_proprietaire}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                });
                const data = await r.json();
                if (!r.ok) { this.deleteError = data.message || 'Erreur'; return; }
                this.showDeleteConfirm = false;
                await this.load();
                window.gsfToast?.('Propriétaire supprimé', 'success');
            } catch (e) {
                this.deleteError = 'Erreur réseau';
            } finally {
                this.saving = false;
            }
        },

        async openDetail(p) {
            this.detail = null;
            this.showDetail = true;
            this.currentDetailId = p.id_proprietaire;
            this.versementError = '';
            this.versementForm = {
                montant: '',
                date_versement: new Date().toISOString().substring(0, 10),
                mode_paiement: 'ESPECES',
                note: '',
            };
            try {
                const r   = await fetch(`/api/proprietaires/${p.id_proprietaire}`);
                this.detail = await r.json();
            } catch (e) {
                console.error(e);
            }
        },

        async saveVersement() {
            this.savingVersement = true;
            this.versementError  = '';
            try {
                const r = await fetch(`/api/proprietaires/${this.currentDetailId}/versements`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        montant:          this.versementForm.montant,
                        date_versement:   this.versementForm.date_versement,
                        mode_paiement:    this.versementForm.mode_paiement,
                        banque:           this.versementForm.banque || null,
                        operateur:        this.versementForm.operateur || null,
                        reference_cheque: this.versementForm.reference_cheque || null,
                        note:             this.versementForm.note,
                    }),
                });
                const data = await r.json();
                if (!r.ok) { this.versementError = data.message || 'Erreur'; return; }
                this.showVForm = false;
                this.versementForm.montant          = '';
                this.versementForm.note             = '';
                this.versementForm.banque           = '';
                this.versementForm.operateur        = '';
                this.versementForm.reference_cheque = '';
                this.versementForm.mode_paiement    = 'ESPECES';
                const rd      = await fetch(`/api/proprietaires/${this.currentDetailId}`);
                this.detail   = await rd.json();
                await this.load();
                window.gsfToast?.('Versement enregistré', 'success');
            } catch (e) {
                this.versementError = 'Erreur réseau';
            } finally {
                this.savingVersement = false;
            }
        },

        async deleteVersement(v) {
            if (!confirm('Supprimer ce versement ?')) return;
            try {
                const r = await fetch(`/api/proprietaires/${this.currentDetailId}/versements/${v.id_versement}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                });
                if (r.ok) {
                    const rd    = await fetch(`/api/proprietaires/${this.currentDetailId}`);
                    this.detail = await rd.json();
                    await this.load();
                    window.gsfToast?.('Versement supprimé', 'success');
                }
            } catch (e) {
                console.error(e);
            }
        },

        _printHelpers(d, s) {
            const now        = new Date().toLocaleDateString('fr-FR', { day: '2-digit', month: 'long', year: 'numeric' });
            const modeLabel  = m => ({ ESPECES: 'Espèces', VIREMENT: 'Virement bancaire', CHEQUE: 'Chèque', MOBILE_MONEY: 'Mobile Money' }[m] || m);
            const statutLabel= st => ({ DISPONIBLE: 'Disponible', RESERVE: 'Réservé', VENDU: 'Vendu' }[st] || st);
            const resteLabel = (s.reste_a_verser ?? 0) < 0 ? 'Avance versée' : 'Reste à verser';
            const resteVal   = this.fmt(Math.abs(s.reste_a_verser ?? 0));
            const resteColor = (s.reste_a_verser ?? 0) < 0 ? '#7c3aed' : (s.reste_a_verser ?? 0) > 0 ? '#dc2626' : '#16a34a';
            const resteNote  = (s.reste_a_verser ?? 0) < 0 ? 'GSF a trop versé' : (s.reste_a_verser ?? 0) > 0 ? 'En cours' : '✓ Soldé';
            const logoUrl    = window.location.origin + '/images/logo/gsf-logo.png';

            const baseCSS = `
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: Arial, sans-serif; font-size: 13px; color: #111; padding: 30px; }
  .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 3px solid #1d4ed8; padding-bottom: 16px; margin-bottom: 20px; }
  .doc-info { text-align: right; font-size: 11px; color: #555; line-height: 1.5; }
  .doc-info strong { display: block; font-size: 14px; color: #111; margin-bottom: 8px; }
  .doc-info .doc-date { display: block; margin-bottom: 8px; }
  .doc-info em { font-style: normal; display: inline-block; font-size: 10px; background:#f3f4f6; border:1px solid #e5e7eb; border-radius:4px; padding:3px 10px; }
  .section { margin-bottom: 22px; }
  .section-title { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #6b7280; border-bottom: 1px solid #e5e7eb; padding-bottom: 4px; margin-bottom: 10px; }
  .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; }
  .stat-box { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 10px; text-align: center; }
  .stat-box .lbl { font-size: 10px; color: #6b7280; margin-bottom: 3px; }
  .stat-box .val { font-size: 14px; font-weight: 700; }
  .analyse-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
  .analyse-box { border-radius: 6px; padding: 10px; }
  .analyse-box .lbl { font-size: 10px; font-weight: 700; text-transform: uppercase; margin-bottom: 6px; }
  .analyse-box .val { font-size: 15px; font-weight: 800; margin-bottom: 6px; }
  .analyse-box .detail { font-size: 10px; color: #555; line-height: 1.6; }
  table { width: 100%; border-collapse: collapse; font-size: 12px; }
  th { background: #f3f4f6; text-align: left; padding: 7px 10px; font-size: 10px; text-transform: uppercase; color: #6b7280; font-weight: 600; border-bottom: 2px solid #e5e7eb; }
  td { padding: 7px 10px; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
  tfoot td { font-weight: 700; background: #f9fafb; border-top: 2px solid #e5e7eb; }
  .badge { display: inline-block; padding: 2px 8px; border-radius: 20px; font-size: 10px; font-weight: 600; }
  .badge-disponible { background:#dcfce7; color:#16a34a; }
  .badge-reserve    { background:#ffedd5; color:#ea580c; }
  .badge-vendu      { background:#fee2e2; color:#dc2626; }
  .footer { margin-top: 30px; padding-top: 12px; border-top: 1px solid #e5e7eb; font-size: 10px; color: #9ca3af; text-align: center; }
  @media print { body { padding: 15px; } }`;

            const headerBlock = (title, badge) => `
<div class="header">
  <div><img src="${logoUrl}" alt="GSF Logo" style="height:52px;width:auto;max-width:200px;object-fit:contain;display:block;"/></div>
  <div class="doc-info">
    <strong>${title}</strong>
    <span class="doc-date">Imprimée le ${now}</span>
    <em>${badge}</em>
  </div>
</div>`;

            const infoBlock = `
<div class="section">
  <div class="section-title">Informations du propriétaire</div>
  <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px">
    <div><span style="font-size:10px;color:#6b7280">Nom</span><br><strong>${d.proprietaire?.nom || '—'}</strong></div>
    <div><span style="font-size:10px;color:#6b7280">Téléphone</span><br><strong>${d.proprietaire?.contact || '—'}</strong></div>
    <div><span style="font-size:10px;color:#6b7280">Email</span><br><strong>${d.proprietaire?.email || '—'}</strong></div>
    <div><span style="font-size:10px;color:#6b7280">Adresse</span><br><strong>${d.proprietaire?.adresse || '—'}</strong></div>
    ${d.proprietaire?.note ? `<div style="grid-column:span 2"><span style="font-size:10px;color:#6b7280">Note</span><br><em>${d.proprietaire.note}</em></div>` : ''}
  </div>
</div>`;

            const situationBlock = `
<div class="section">
  <div class="section-title">Situation financière</div>
  <div class="stats-grid">
    <div class="stat-box"><div class="lbl">Lots totaux</div><div class="val">${s.nb_lots ?? 0}</div>
      <div style="font-size:10px;color:#6b7280;margin-top:3px">${s.disponible??0} dispo · ${s.reserve??0} rés. · ${s.vendu??0} vdus</div>
    </div>
    <div class="stat-box"><div class="lbl">Total dû (prix achat)</div><div class="val" style="color:#ea580c">${this.fmt(s.total_prix_achat??0)}</div></div>
    <div class="stat-box"><div class="lbl">Total versé</div><div class="val" style="color:#16a34a">${this.fmt(s.total_verse??0)}</div></div>
    <div class="stat-box"><div class="lbl">${resteLabel}</div>
      <div class="val" style="color:${resteColor}">${resteVal}</div>
      <div style="font-size:10px;margin-top:3px">${resteNote}</div>
    </div>
  </div>
</div>`;

            const versRows = (d.versements ?? []).map(v => `
  <tr>
    <td style="font-family:monospace;font-size:11px">${v.reference}</td>
    <td>${v.date_versement_formatee || v.date_versement}</td>
    <td>${modeLabel(v.mode_paiement)}</td>
    <td style="text-align:right;color:#16a34a;font-weight:600">${v.montant_formate || this.fmt(v.montant)}</td>
    <td>${v.note || '—'}</td>
  </tr>`).join('');

            const versementsBlock = (d.versements??[]).length > 0 ? `
<div class="section">
  <div class="section-title">Historique des versements (${(d.versements??[]).length})</div>
  <table>
    <thead><tr><th>Référence</th><th>Date</th><th>Mode</th><th style="text-align:right">Montant</th><th>Note</th></tr></thead>
    <tbody>${versRows}</tbody>
    <tfoot><tr><td colspan="3">Total versé</td><td style="text-align:right;color:#16a34a">${this.fmt(s.total_verse??0)}</td><td></td></tr></tfoot>
  </table>
</div>` : '';

            return { now, modeLabel, statutLabel, baseCSS, headerBlock, infoBlock, situationBlock, versementsBlock, logoUrl };
        },

        printFicheProprietaire() {
            if (!this.detail) return;
            const d = this.detail;
            const s = d.stats ?? {};
            const h = this._printHelpers(d, s);
            const statutLabel = h.statutLabel;

            const lotsRows = (d.lots ?? []).map(l => `
  <tr>
    <td>${l.numero_lot}</td>
    <td>${l.lotissement_nom || '—'}</td>
    <td>${l.superficie ? l.superficie + ' m²' : '—'}</td>
    <td style="text-align:right;color:#ea580c">${this.fmt(l.prix_achat || 0)}</td>
    <td style="text-align:center"><span class="badge badge-${(l.statut||'').toLowerCase()}">${statutLabel(l.statut)}</span></td>
  </tr>`).join('');

            const lotsBlock = (d.lots??[]).length > 0 ? `
<div class="section">
  <div class="section-title">Lots associés (${(d.lots??[]).length})</div>
  <table>
    <thead><tr><th>N° Lot</th><th>Lotissement</th><th>Superficie</th><th style="text-align:right">Prix achat</th><th style="text-align:center">Statut</th></tr></thead>
    <tbody>${lotsRows}</tbody>
  </table>
</div>` : '';

            const html = `<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8">
<title>Fiche propriétaire — ${d.proprietaire?.nom}</title>
<style>${h.baseCSS}</style></head><body>
${h.headerBlock('Fiche Propriétaire', 'Document remis au propriétaire')}
${h.infoBlock}
${h.situationBlock}
${lotsBlock}
${h.versementsBlock}
<div class="footer">Fiche générée le ${h.now} · GSF — Géomètre Son Femme</div>
<script>window.onload = () => { window.print(); }<\/script>
</body></html>`;

            const w = window.open('', '_blank', 'width=900,height=700');
            w.document.write(html);
            w.document.close();
        },

        printFicheComplete() {
            if (!this.detail) return;
            const d = this.detail;
            const s = d.stats ?? {};
            const h = this._printHelpers(d, s);
            const statutLabel = h.statutLabel;

            const gainSign  = (s.gain_theorique ?? 0) >= 0 ? '+' : '';
            const tresoSign = (s.position_tresorerie ?? 0) >= 0 ? '+' : '';
            const tresoColor= (s.position_tresorerie ?? 0) >= 0 ? '#16a34a' : '#ea580c';

            const lotsRows = (d.lots ?? []).map(l => `
  <tr>
    <td>${l.numero_lot}</td>
    <td>${l.lotissement_nom || '—'}</td>
    <td>${l.superficie ? l.superficie + ' m²' : '—'}</td>
    <td style="text-align:right;color:#ea580c">${this.fmt(l.prix_achat || 0)}</td>
    <td style="text-align:right">${this.fmt(Math.max(parseFloat(l.prix_comptant||0), parseFloat(l.prix_echelonne||0)))}</td>
    <td style="text-align:right;color:#15803d;font-weight:600">${this.fmt(Math.max(parseFloat(l.prix_comptant||0), parseFloat(l.prix_echelonne||0)) - parseFloat(l.prix_achat||0))}</td>
    <td style="text-align:center"><span class="badge badge-${(l.statut||'').toLowerCase()}">${statutLabel(l.statut)}</span></td>
  </tr>`).join('');

            const lotsBlock = (d.lots??[]).length > 0 ? `
<div class="section">
  <div class="section-title">Lots associés — vue complète (${(d.lots??[]).length})</div>
  <table>
    <thead><tr><th>N° Lot</th><th>Lotissement</th><th>Superficie</th><th style="text-align:right">Prix achat</th><th style="text-align:right">Prix vente</th><th style="text-align:right">Marge</th><th style="text-align:center">Statut</th></tr></thead>
    <tbody>${lotsRows}</tbody>
  </table>
</div>` : '';

            const analyseBlock = `
<div class="section">
  <div class="section-title">Analyse financière GSF (confidentiel)</div>
  <div class="analyse-grid">
    <div class="analyse-box" style="background:#f0fdf4;border:1px solid #bbf7d0">
      <div class="lbl" style="color:#15803d">Marge théorique GSF</div>
      <div class="val" style="color:#15803d">${gainSign}${this.fmt(s.gain_theorique??0)}</div>
      <div class="detail">Prix vente total : ${this.fmt(s.total_prix_vente_theorique??0)}<br>Prix achat total : − ${this.fmt(s.total_prix_achat??0)}</div>
    </div>
    <div class="analyse-box" style="background:#eff6ff;border:1px solid #bfdbfe">
      <div class="lbl" style="color:#1d4ed8">Encaissements clients</div>
      <div class="val" style="color:#1d4ed8">${this.fmt(s.encaissements_clients??0)}</div>
      <div class="detail">Paiements validés sur les contrats<br>des lots de ce propriétaire</div>
    </div>
    <div class="analyse-box" style="background:${(s.position_tresorerie??0)>=0?'#f0fdf4':'#fff7ed'};border:1px solid ${(s.position_tresorerie??0)>=0?'#bbf7d0':'#fed7aa'}">
      <div class="lbl" style="color:${tresoColor}">Position trésorerie GSF</div>
      <div class="val" style="color:${tresoColor}">${tresoSign}${this.fmt(s.position_tresorerie??0)}</div>
      <div class="detail">Reçu des clients : ${this.fmt(s.encaissements_clients??0)}<br>Versé au proprio : − ${this.fmt(s.total_verse??0)}<br>${(s.position_tresorerie??0) >= 0 ? '✓ GSF a encaissé avant de verser' : '⚠ GSF a avancé avant encaissement'}</div>
    </div>
  </div>
</div>`;

            const html = `<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8">
<title>Fiche interne — ${d.proprietaire?.nom}</title>
<style>${h.baseCSS}
  .confidentiel { background:#fef9c3; border:1px solid #fde68a; border-radius:4px; padding:6px 12px; font-size:10px; font-weight:700; color:#92400e; text-align:center; margin-bottom:16px; }
</style></head><body>
${h.headerBlock('Fiche Interne — Usage GSF', 'CONFIDENTIEL · Ne pas remettre au propriétaire')}
<div class="confidentiel">⚠ Document confidentiel — Usage interne GSF uniquement</div>
${h.infoBlock}
${h.situationBlock}
${analyseBlock}
${lotsBlock}
${h.versementsBlock}
<div class="footer">Fiche interne générée le ${h.now} · GSF — Géomètre Son Femme · CONFIDENTIEL</div>
<script>window.onload = () => { window.print(); }<\/script>
</body></html>`;

            const w = window.open('', '_blank', 'width=900,height=700');
            w.document.write(html);
            w.document.close();
        },
    };
}
</script>

@endsection

@extends('layouts.app')

@section('content')

<div x-data="paiementsPage()" x-init="init()">

    {{-- Titre + onglets --}}
    <div class="mb-6">
        <h1 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Gestion des Paiements</h1>
        <div class="border-b border-gray-200 dark:border-gray-800">
            <nav class="-mb-px flex gap-1">
                <button
                    @click="activeTab = 'clients'"
                    :class="activeTab === 'clients'
                        ? 'border-blue-600 text-blue-600 dark:text-blue-400'
                        : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 hover:border-gray-300'"
                    class="flex items-center gap-2 px-4 py-3 text-sm font-medium border-b-2 transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 002 2v6a2 2 0 002 2z"/>
                    </svg>
                    Paiements clients
                </button>
                <button
                    @click="activeTab = 'proprietaires'; loadVersements()"
                    :class="activeTab === 'proprietaires'
                        ? 'border-orange-500 text-orange-600 dark:text-orange-400'
                        : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 hover:border-gray-300'"
                    class="flex items-center gap-2 px-4 py-3 text-sm font-medium border-b-2 transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Versements propriétaires
                    <span x-show="statsV.total_verse > 0" class="ml-1 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400">
                        <span x-text="versements.length"></span>
                    </span>
                </button>
            </nav>
        </div>
    </div>

    {{-- ─── Onglet : Paiements clients ─── --}}
    <div x-show="activeTab === 'clients'">
        <x-paiements.paiement-table />
    </div>

    {{-- ─── Onglet : Versements propriétaires ─── --}}
    <div x-show="activeTab === 'proprietaires'" x-cloak>

        {{-- Stats --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 p-4 text-center">
                <p class="text-xs text-gray-500 dark:text-gray-400">Versements enregistrés</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1" x-text="versements.length"></p>
            </div>
            <div class="rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 p-4 text-center">
                <p class="text-xs text-gray-500 dark:text-gray-400">Propriétaires concernés</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1" x-text="proprietairesListe.length"></p>
            </div>
            <div class="rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 p-4 text-center">
                <p class="text-xs text-gray-500 dark:text-gray-400">Total versé</p>
                <p class="text-sm font-bold text-green-600 dark:text-green-400 mt-1" x-text="fmt(statsV.total_verse)"></p>
            </div>
            <div class="rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 p-4 text-center">
                <p class="text-xs text-gray-500 dark:text-gray-400">Reste à verser (global)</p>
                <p class="text-sm font-bold mt-1" :class="statsV.reste_a_verser > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600'" x-text="fmt(statsV.reste_a_verser)"></p>
            </div>
        </div>

        {{-- Table des versements --}}
        <div class="rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-800 dark:text-white">Historique des versements</h2>
                <div class="flex items-center gap-3">
                    <select x-model="filtreProprietaire"
                        class="rounded-lg border border-gray-300 dark:border-gray-700 px-3 py-1.5 text-xs text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800">
                        <option value="">Tous les propriétaires</option>
                        <template x-for="p in proprietairesListe" :key="p.id_proprietaire">
                            <option :value="p.id_proprietaire" x-text="p.nom"></option>
                        </template>
                    </select>
                    <button @click="openVModal()"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-orange-600 rounded-lg hover:bg-orange-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Nouveau versement
                    </button>
                </div>
            </div>

            <div x-show="vLoading" class="flex justify-center py-12">
                <div class="w-8 h-8 border-4 border-orange-500 border-t-transparent rounded-full animate-spin"></div>
            </div>

            <div x-show="!vLoading && versementsFiltres.length === 0" class="text-center py-12 text-gray-400 dark:text-gray-600">
                <svg class="w-10 h-10 mx-auto mb-2 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                <p class="text-sm">Aucun versement enregistré</p>
                <p class="text-xs mt-1">Cliquez sur "Nouveau versement" pour commencer</p>
            </div>

            <div x-show="!vLoading && versementsFiltres.length > 0" class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Référence</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Propriétaire</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Montant</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mode</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Note</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-100 dark:divide-gray-800">
                        <template x-for="v in versementsFiltres" :key="v.id_versement">
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="px-6 py-3 text-sm font-mono text-gray-600 dark:text-gray-400 whitespace-nowrap" x-text="v.reference"></td>
                                <td class="px-6 py-3 text-sm font-medium text-gray-900 dark:text-white whitespace-nowrap" x-text="v.proprietaire_nom"></td>
                                <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-400 whitespace-nowrap" x-text="v.date_versement_formatee || v.date_versement"></td>
                                <td class="px-6 py-3 text-sm font-semibold text-green-600 dark:text-green-400 whitespace-nowrap" x-text="v.montant_formate || fmt(v.montant)"></td>
                                <td class="px-6 py-3 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap" x-text="modeLabel(v.mode_paiement)"></td>
                                <td class="px-6 py-3 text-sm text-gray-400 dark:text-gray-500" x-text="v.note || '—'"></td>
                                <td class="px-6 py-3 text-center">
                                    <button @click="deleteVersement(v)"
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                                        title="Supprimer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

    </div>{{-- fin onglet propriétaires --}}

{{-- ══ Modal Nouveau Versement ══ --}}
<template x-teleport="body">
<div x-show="showVModal" x-cloak class="fixed inset-0 z-[100000] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="closeVModal()"></div>

    <div class="relative w-full max-w-lg bg-white dark:bg-gray-900 rounded-2xl shadow-2xl" @click.stop>

        {{-- Header modal --}}
        <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 dark:border-gray-800">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">Nouveau versement</h2>
                    <p class="text-xs text-gray-400 dark:text-gray-500">Versement au propriétaire terrien</p>
                </div>
            </div>
            <button @click="closeVModal()" class="p-2 rounded-xl text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Corps du formulaire --}}
        <form @submit.prevent="saveVersement()" class="p-6 space-y-4">

            {{-- Propriétaire --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Propriétaire <span class="text-red-500">*</span></label>
                <select x-model="vForm.id_proprietaire" required @change="onProprietaireChange()"
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white bg-white dark:bg-gray-800 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none">
                    <option value="">Sélectionnez un propriétaire</option>
                    <template x-for="p in proprietairesListe" :key="p.id_proprietaire">
                        <option :value="p.id_proprietaire" x-text="p.nom + (p.contact ? ' — ' + p.contact : '')"></option>
                    </template>
                </select>
            </div>

            {{-- Situation propriétaire (conditionnelle) --}}
            <div x-show="proprietaireSelectionne" x-cloak class="rounded-lg bg-orange-50 dark:bg-orange-900/10 border border-orange-200 dark:border-orange-800 p-3">
                <p class="text-xs font-semibold text-orange-800 dark:text-orange-300 mb-2">Situation du propriétaire</p>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div>
                        <span class="text-gray-500">Total dû :</span>
                        <span class="font-medium text-gray-900 dark:text-white" x-text="fmt(proprietaireSelectionne?.stats?.total_prix_achat ?? 0)"></span>
                    </div>
                    <div>
                        <span class="text-gray-500">Déjà versé :</span>
                        <span class="font-medium text-green-600" x-text="fmt(proprietaireSelectionne?.stats?.total_verse ?? 0)"></span>
                    </div>
                    <div>
                        <span class="text-gray-500">Solde :</span>
                        <span class="font-semibold"
                            :class="(proprietaireSelectionne?.stats?.reste_a_verser ?? 0) > 0 ? 'text-red-600' : (proprietaireSelectionne?.stats?.reste_a_verser ?? 0) < 0 ? 'text-purple-600' : 'text-green-600'"
                            x-text="(proprietaireSelectionne?.stats?.reste_a_verser ?? 0) === 0
                                ? '✓ Soldé'
                                : (proprietaireSelectionne?.stats?.reste_a_verser ?? 0) > 0
                                    ? fmt(proprietaireSelectionne.stats.reste_a_verser) + ' à verser'
                                    : 'Avance : ' + fmt(Math.abs(proprietaireSelectionne.stats.reste_a_verser))">
                        </span>
                    </div>
                    <div>
                        <span class="text-gray-500">Lots :</span>
                        <span class="font-medium text-gray-900 dark:text-white" x-text="(proprietaireSelectionne?.stats?.nb_lots ?? 0) + ' lot(s)'"></span>
                    </div>
                </div>
            </div>

            {{-- Montant + Date --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Montant (FCFA) <span class="text-red-500">*</span></label>
                    <input type="number" x-model="vForm.montant" min="1" step="1" required
                        class="w-full rounded-lg border border-gray-300 dark:border-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white bg-white dark:bg-gray-800 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none"
                        placeholder="Ex : 500 000"/>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date <span class="text-red-500">*</span></label>
                    <input type="date" x-model="vForm.date_versement" required
                        class="w-full rounded-lg border border-gray-300 dark:border-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white bg-white dark:bg-gray-800 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none"/>
                </div>
            </div>

            {{-- Mode paiement --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mode de paiement <span class="text-red-500">*</span></label>
                <select x-model="vForm.mode_paiement" required
                    @change="vForm.banque = ''; vForm.operateur = ''; vForm.reference_cheque = ''"
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white bg-white dark:bg-gray-800 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none">
                    <option value="ESPECES">Espèces</option>
                    <option value="VIREMENT">Virement bancaire</option>
                    <option value="CHEQUE">Chèque</option>
                    <option value="MOBILE_MONEY">Mobile Money</option>
                </select>
            </div>

            {{-- Mobile Money → Opérateur --}}
            <div x-show="vForm.mode_paiement === 'MOBILE_MONEY'">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Opérateur <span class="text-red-500">*</span></label>
                <select x-model="vForm.operateur"
                    :required="vForm.mode_paiement === 'MOBILE_MONEY'"
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white bg-white dark:bg-gray-800 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none">
                    <option value="">Sélectionnez un opérateur</option>
                    <option value="Orange Money">Orange Money</option>
                    <option value="MTN Money">MTN Money</option>
                    <option value="Wave">Wave</option>
                    <option value="Moov Money">Moov Money</option>
                    <option value="Autre">Autre</option>
                </select>
            </div>

            {{-- Virement → Banque --}}
            <div x-show="vForm.mode_paiement === 'VIREMENT'">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Banque <span class="text-red-500">*</span></label>
                <input type="text" x-model="vForm.banque"
                    :required="vForm.mode_paiement === 'VIREMENT'"
                    placeholder="Ex : SGBCI, BIAO, Ecobank…"
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white bg-white dark:bg-gray-800 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none"/>
            </div>

            {{-- Chèque → Banque + Référence --}}
            <div x-show="vForm.mode_paiement === 'CHEQUE'" class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Banque <span class="text-red-500">*</span></label>
                    <input type="text" x-model="vForm.banque"
                        :required="vForm.mode_paiement === 'CHEQUE'"
                        placeholder="Ex : SGBCI, BIAO…"
                        class="w-full rounded-lg border border-gray-300 dark:border-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white bg-white dark:bg-gray-800 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none"/>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">N° de chèque <span class="text-red-500">*</span></label>
                    <input type="text" x-model="vForm.reference_cheque"
                        :required="vForm.mode_paiement === 'CHEQUE'"
                        placeholder="Ex : 0012345"
                        class="w-full rounded-lg border border-gray-300 dark:border-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white bg-white dark:bg-gray-800 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none"/>
                </div>
            </div>

            {{-- Note --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Note (optionnel)</label>
                <input type="text" x-model="vForm.note"
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white bg-white dark:bg-gray-800 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none"
                    placeholder="Détails du versement…"/>
            </div>

            <p x-show="vFormError" x-text="vFormError" class="text-xs text-red-600"></p>

            {{-- Actions --}}
            <div class="flex gap-3 pt-1">
                <button type="button" @click="closeVModal()"
                    class="flex-1 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                    Annuler
                </button>
                <button type="submit" :disabled="vSaving"
                    class="flex-1 py-2.5 text-sm font-medium text-white bg-orange-600 rounded-lg hover:bg-orange-700 disabled:opacity-50 transition-colors">
                    <span x-show="!vSaving">Enregistrer le versement</span>
                    <span x-show="vSaving">Enregistrement…</span>
                </button>
            </div>
        </form>
    </div>
</div>
</template>

</div>{{-- fin x-data paiementsPage --}}

<script>
function paiementsPage() {
    return {
        activeTab: 'clients',

        // Versements
        versements: [],
        proprietairesListe: [],
        proprietaireSelectionne: null,
        filtreProprietaire: '',
        vLoading: false,
        statsV: { total_verse: 0, reste_a_verser: 0 },
        showVModal: false,

        // Formulaire versement
        vForm: {
            id_proprietaire: '',
            montant: '',
            date_versement: new Date().toISOString().substring(0, 10),
            mode_paiement: 'ESPECES',
            banque: '',
            operateur: '',
            reference_cheque: '',
            note: '',
        },
        vFormError: '',
        vSaving: false,

        async init() {
            await this.loadProprietaires();
        },

        async loadProprietaires() {
            try {
                const r = await fetch('/api/proprietaires');
                if (r.ok) this.proprietairesListe = await r.json();
            } catch (e) {}
            this.computeStatsV();
        },

        computeStatsV() {
            this.statsV.total_verse     = this.proprietairesListe.reduce((s, p) => s + (p.stats?.total_verse ?? 0), 0);
            this.statsV.reste_a_verser  = this.proprietairesListe.reduce((s, p) => s + (p.stats?.reste_a_verser ?? 0), 0);
        },

        async loadVersements() {
            this.vLoading = true;
            try {
                // Charge tous les versements en parallèle pour chaque propriétaire
                const results = await Promise.all(
                    this.proprietairesListe.map(p =>
                        fetch(`/api/proprietaires/${p.id_proprietaire}`)
                            .then(r => r.json())
                            .then(d => (d.versements || []).map(v => ({ ...v, proprietaire_nom: p.nom, id_proprietaire: p.id_proprietaire })))
                            .catch(() => [])
                    )
                );
                this.versements = results.flat().sort((a, b) => new Date(b.date_versement) - new Date(a.date_versement));
            } catch (e) {
                console.error(e);
            } finally {
                this.vLoading = false;
            }
        },

        get versementsFiltres() {
            if (!this.filtreProprietaire) return this.versements;
            return this.versements.filter(v => v.id_proprietaire == this.filtreProprietaire);
        },

        onProprietaireChange() {
            const p = this.proprietairesListe.find(x => x.id_proprietaire == this.vForm.id_proprietaire);
            this.proprietaireSelectionne = p || null;
        },

        openVModal() {
            this.resetVForm();
            this.showVModal = true;
        },

        closeVModal() {
            this.showVModal = false;
            this.resetVForm();
        },

        resetVForm() {
            this.vForm = {
                id_proprietaire: '',
                montant: '',
                date_versement: new Date().toISOString().substring(0, 10),
                mode_paiement: 'ESPECES',
                banque: '',
                operateur: '',
                reference_cheque: '',
                note: '',
            };
            this.proprietaireSelectionne = null;
            this.vFormError = '';
        },

        async saveVersement() {
            this.vSaving = true;
            this.vFormError = '';
            try {
                const r = await fetch(`/api/proprietaires/${this.vForm.id_proprietaire}/versements`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        montant:          this.vForm.montant,
                        date_versement:   this.vForm.date_versement,
                        mode_paiement:    this.vForm.mode_paiement,
                        banque:           this.vForm.banque || null,
                        operateur:        this.vForm.operateur || null,
                        reference_cheque: this.vForm.reference_cheque || null,
                        note:             this.vForm.note,
                    }),
                });
                const data = await r.json();
                if (!r.ok) {
                    this.vFormError = data.message || 'Erreur lors de l\'enregistrement';
                    return;
                }
                this.closeVModal();
                await this.loadProprietaires();
                await this.loadVersements();
                window.gsfToast?.('Versement enregistré avec succès', 'success');
            } catch (e) {
                this.vFormError = 'Erreur réseau';
            } finally {
                this.vSaving = false;
            }
        },

        async deleteVersement(v) {
            if (!confirm(`Supprimer le versement ${v.reference} de ${this.fmt(v.montant)} ?`)) return;
            try {
                const r = await fetch(`/api/proprietaires/${v.id_proprietaire}/versements/${v.id_versement}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                });
                if (r.ok) {
                    await this.loadProprietaires();
                    await this.loadVersements();
                    window.gsfToast?.('Versement supprimé', 'success');
                }
            } catch (e) {
                console.error(e);
            }
        },

        fmt(val) {
            return new Intl.NumberFormat('fr-FR').format(Math.round(val || 0)) + ' FCFA';
        },

        modeLabel(mode) {
            return { ESPECES: 'Espèces', VIREMENT: 'Virement', CHEQUE: 'Chèque', MOBILE_MONEY: 'Mobile Money' }[mode] || mode;
        },
    };
}
</script>

@endsection

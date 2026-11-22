@extends('layouts.app')

@section('content')

<div x-data="banqueApp()" x-init="init()" class="space-y-6">

{{-- En-tête page --}}
<div class="flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-xl font-bold text-gray-900 dark:text-white">Banque GSF</h1>
        <p class="text-xs text-gray-400 mt-0.5">Comptes bancaires de l'entreprise — entrées, sorties et validation</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <button x-show="window.gsfUser.can.banque?.comptes" @click="openComptes()"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            Comptes bancaires
        </button>
        <button x-show="window.gsfUser.can.banque?.create" @click="openCreate()" :disabled="comptesActifs.length === 0"
            :title="comptesActifs.length === 0 ? 'Créez d\'abord un compte bancaire actif' : ''"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-brand-500 rounded-lg hover:bg-brand-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nouvelle opération
        </button>
    </div>
</div>

{{-- Aucun compte --}}
<div x-show="!loading && comptes.length === 0" x-cloak
    class="rounded-xl border border-dashed border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 p-8 text-center">
    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Aucun compte bancaire enregistré</p>
    <p class="mt-1 text-xs text-gray-400" x-show="window.gsfUser.can.banque?.comptes">Ajoutez le compte bancaire de GSF pour commencer le suivi.</p>
    <p class="mt-1 text-xs text-gray-400" x-show="!window.gsfUser.can.banque?.comptes">Un administrateur doit d'abord créer le compte bancaire de GSF.</p>
    <button x-show="window.gsfUser.can.banque?.comptes" @click="openCompteForm()"
        class="mt-4 inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-brand-500 rounded-lg hover:bg-brand-600">
        Ajouter un compte bancaire
    </button>
</div>

{{-- Cartes comptes --}}
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4" x-show="comptes.length > 0">
    <template x-for="c in comptes" :key="c.id">
        <button type="button" @click="filtres.compte = (filtres.compte == c.id ? '' : String(c.id)); loadOperations()"
            class="text-left rounded-xl border bg-white dark:bg-gray-900 p-4 transition-all hover:shadow-md"
            :class="filtres.compte == c.id ? 'border-brand-500 ring-2 ring-brand-500/20' : 'border-gray-200 dark:border-gray-800'">
            <div class="flex items-start justify-between gap-2">
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white truncate" x-text="c.banque"></p>
                    <p class="text-xs text-gray-400 truncate" x-text="c.numero_compte + (c.intitule ? ' — ' + c.intitule : '')"></p>
                </div>
                <span x-show="!c.actif" class="shrink-0 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-gray-100 text-gray-500 dark:bg-gray-800">Inactif</span>
            </div>
            <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">Solde (opérations validées)</p>
            <p class="text-2xl font-bold break-words" :class="c.solde < 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white'" x-text="fmt(c.solde)"></p>
            <div x-show="c.nb_en_attente > 0" class="mt-2 flex flex-wrap gap-x-3 gap-y-1 text-xs">
                <span class="text-amber-600 dark:text-amber-400" x-text="c.nb_en_attente + ' en attente'"></span>
                <span x-show="c.entrees_en_attente > 0" class="text-green-600" x-text="'+ ' + fmt(c.entrees_en_attente)"></span>
                <span x-show="c.sorties_en_attente > 0" class="text-red-600" x-text="'− ' + fmt(c.sorties_en_attente)"></span>
            </div>
        </button>
    </template>
</div>

{{-- Tableau des opérations --}}
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden" x-show="comptes.length > 0">
    <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Opérations bancaires</h2>
        <div class="flex flex-col lg:flex-row justify-end items-start lg:items-center gap-4">
            <div class="flex w-full lg:w-auto items-center gap-3 flex-wrap">
                <select x-model="filtres.compte" @change="loadOperations()"
                    class="w-full sm:w-44 rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    <option value="">Tous les comptes</option>
                    <template x-for="c in comptes" :key="'f' + c.id">
                        <option :value="String(c.id)" x-text="c.banque + ' — ' + c.numero_compte"></option>
                    </template>
                </select>
                <select x-model="filtres.type" @change="loadOperations()"
                    class="w-full sm:w-36 rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    <option value="">Entrées et sorties</option>
                    <option value="CREDIT">Entrées</option>
                    <option value="DEBIT">Sorties</option>
                </select>
                <select x-model="filtres.statut" @change="loadOperations()"
                    class="w-full sm:w-36 rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    <option value="">Tous les statuts</option>
                    <option value="EN_ATTENTE">En attente</option>
                    <option value="VALIDE">Validées</option>
                    <option value="REJETE">Rejetées</option>
                </select>
                <div class="flex w-full sm:w-auto items-center gap-2">
                    <input type="date" x-model="filtres.du" @change="loadOperations()" title="Du"
                        class="w-full sm:w-36 rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    <span class="text-xs text-gray-400">au</span>
                    <input type="date" x-model="filtres.au" @change="loadOperations()" title="Au"
                        class="w-full sm:w-36 rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                </div>
            </div>
        </div>
    </div>

    {{-- Totaux de la sélection --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 p-4 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm text-center">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Entrées validées</p>
            <p class="mt-1 text-base sm:text-lg font-bold text-green-600 dark:text-green-400 break-words" x-text="fmt(totaux.entrees)"></p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm text-center">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Sorties validées</p>
            <p class="mt-1 text-base sm:text-lg font-bold text-red-600 dark:text-red-400 break-words" x-text="fmt(totaux.sorties)"></p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm text-center">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Variation nette</p>
            <p class="mt-1 text-base sm:text-lg font-bold break-words" :class="totaux.entrees - totaux.sorties < 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-800 dark:text-white'" x-text="fmt(totaux.entrees - totaux.sorties)"></p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm text-center">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">En attente de validation</p>
            <p class="mt-1 text-base sm:text-lg font-bold text-amber-600 dark:text-amber-400" x-text="totaux.enAttente"></p>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Opération</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Compte</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Montant</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Statut</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <tr x-show="loadingOps"><td colspan="6" class="px-4 py-8 text-center text-sm text-gray-400">Chargement…</td></tr>
                <tr x-show="!loadingOps && operations.length === 0"><td colspan="6" class="px-4 py-8 text-center text-sm text-gray-400">Aucune opération pour ces filtres</td></tr>
                <template x-for="op in paginated" :key="op.id">
                    <tr @click="openDetail(op)" class="cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300" x-text="op.date_operation_formatee"></td>
                        <td class="px-4 py-3 text-sm">
                            <p class="font-medium text-gray-900 dark:text-white" x-text="op.libelle"></p>
                            <p class="text-xs text-gray-400" x-text="op.categorie_label + (op.reference ? ' · Réf. ' + op.reference : '')"></p>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400" x-text="op.compte ? op.compte.banque : '-'"></td>
                        <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-semibold"
                            :class="op.type === 'CREDIT' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'"
                            x-text="(op.type === 'CREDIT' ? '+ ' : '− ') + fmt(op.montant)"></td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold" :class="statutClass(op.statut)" x-text="statutLabel(op.statut)"></span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <button x-show="op.statut === 'EN_ATTENTE' && window.gsfUser.can.banque?.valider" @click.stop="valider(op)" title="Valider"
                                    class="p-1.5 rounded-lg text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                </button>
                                <button x-show="op.statut === 'EN_ATTENTE' && window.gsfUser.can.banque?.rejeter" @click.stop="openRejet(op)" title="Rejeter"
                                    class="p-1.5 rounded-lg text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/20">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                </button>
                                <button x-show="op.statut !== 'VALIDE' && window.gsfUser.can.banque?.update" @click.stop="openEdit(op)" title="Modifier"
                                    class="p-1.5 rounded-lg text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <button x-show="op.statut !== 'VALIDE' && window.gsfUser.can.banque?.delete" @click.stop="supprimer(op)" title="Supprimer"
                                    class="p-1.5 rounded-lg text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700" x-show="totalPages > 1">
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
            <div class="text-sm text-gray-700 dark:text-gray-300" x-text="operations.length + ' opération(s)'"></div>
            <div class="flex gap-2">
                <button @click="page > 1 && page--" :disabled="page === 1"
                    class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded-md text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 disabled:opacity-50">Précédent</button>
                <span class="px-3 py-1 text-sm text-gray-700 dark:text-gray-300" x-text="'Page ' + page + ' / ' + totalPages"></span>
                <button @click="page < totalPages && page++" :disabled="page === totalPages"
                    class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded-md text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 disabled:opacity-50">Suivant</button>
            </div>
        </div>
    </div>
</div>

{{-- ════════════════════════ MODAL OPÉRATION ════════════════════════ --}}
<template x-teleport="body">
<div x-show="showForm" x-cloak @keydown.escape.window="showForm = false" class="fixed inset-0 z-[100000] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showForm = false"></div>
    <div class="relative w-full max-w-lg max-h-[90vh] overflow-y-auto bg-white dark:bg-gray-900 rounded-2xl shadow-2xl" @click.stop>
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-800">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white" x-text="editId ? 'Modifier l\'opération' : 'Nouvelle opération bancaire'"></h2>
            <button @click="showForm = false" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-800">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form @submit.prevent="saveOperation()" class="p-6 space-y-4">
            <div x-show="formError" class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-300" x-text="formError"></div>

            {{-- Type --}}
            <div class="grid grid-cols-2 gap-2">
                <button type="button" @click="setType('CREDIT')"
                    class="py-2.5 rounded-lg border text-sm font-semibold transition-colors"
                    :class="form.type === 'CREDIT' ? 'border-green-500 bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400' : 'border-gray-300 text-gray-500 dark:border-gray-700'">
                    + Entrée
                </button>
                <button type="button" @click="setType('DEBIT')"
                    class="py-2.5 rounded-lg border text-sm font-semibold transition-colors"
                    :class="form.type === 'DEBIT' ? 'border-red-500 bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400' : 'border-gray-300 text-gray-500 dark:border-gray-700'">
                    − Sortie
                </button>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Compte bancaire <span class="text-red-500">*</span></label>
                <select x-model="form.id_compte" required class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                    <template x-for="c in comptesActifs" :key="'fc' + c.id">
                        <option :value="String(c.id)" x-text="c.banque + ' — ' + c.numero_compte"></option>
                    </template>
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Catégorie <span class="text-red-500">*</span></label>
                <select x-model="form.categorie" required class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                    <template x-for="(label, key) in (categories[form.type] || {})" :key="key">
                        <option :value="key" x-text="label"></option>
                    </template>
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Libellé <span class="text-red-500">*</span></label>
                <input type="text" x-model="form.libelle" required maxlength="255" placeholder="Ex : Dépôt des encaissements de la semaine"
                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Montant (FCFA) <span class="text-red-500">*</span></label>
                    <input type="number" x-model="form.montant" min="1" step="1" required
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Date <span class="text-red-500">*</span></label>
                    <input type="date" x-model="form.date_operation" :max="today" required
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                </div>
            </div>

            <div x-show="form.type === 'DEBIT'" class="rounded-lg px-3 py-2 text-xs"
                :class="depasseDisponible ? 'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-300' : 'bg-gray-50 text-gray-600 dark:bg-gray-800 dark:text-gray-400'">
                Disponible sur ce compte : <strong x-text="fmt(Math.max(0, disponibleForm))"></strong>
                <span x-show="depasseDisponible"> — le montant dépasse le solde disponible.</span>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Référence (bordereau, n° de chèque, virement…)</label>
                <input type="text" x-model="form.reference" maxlength="100"
                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Note</label>
                <textarea x-model="form.note" rows="2"
                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white"></textarea>
            </div>

            <p class="text-xs text-gray-400">L'opération sera enregistrée <strong>en attente</strong> : elle ne modifiera le solde qu'après validation par un administrateur ou le directeur.</p>

            <div class="flex gap-3 pt-2">
                <button type="button" @click="showForm = false"
                    class="flex-1 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800">Annuler</button>
                <button type="submit" :disabled="saving || depasseDisponible"
                    class="flex-1 py-2 text-sm font-medium text-white bg-brand-500 rounded-lg hover:bg-brand-600 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span x-text="saving ? 'Enregistrement…' : 'Enregistrer'"></span>
                </button>
            </div>
        </form>
    </div>
</div>
</template>

{{-- ════════════════════════ MODAL DÉTAIL ════════════════════════ --}}
<template x-teleport="body">
<div x-show="detail" x-cloak @keydown.escape.window="detail = null" class="fixed inset-0 z-[100000] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="detail = null"></div>
    <div class="relative w-full max-w-lg max-h-[90vh] overflow-y-auto bg-white dark:bg-gray-900 rounded-2xl shadow-2xl" @click.stop>
        <template x-if="detail">
            <div>
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-800">
                    <div>
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Détail de l'opération</h2>
                        <p class="text-xs text-gray-400" x-text="(detail.type === 'CREDIT' ? 'Entrée' : 'Sortie') + ' · ' + detail.categorie_label"></p>
                    </div>
                    <button @click="detail = null" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-800">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <div class="text-center">
                        <p class="text-2xl font-bold break-words" :class="detail.type === 'CREDIT' ? 'text-green-600' : 'text-red-600'"
                           x-text="(detail.type === 'CREDIT' ? '+ ' : '− ') + fmt(detail.montant)"></p>
                        <span class="mt-2 inline-block px-2 py-1 rounded-full text-xs font-semibold" :class="statutClass(detail.statut)" x-text="statutLabel(detail.statut)"></span>
                    </div>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-3 text-sm">
                        <div class="sm:col-span-2"><dt class="text-xs text-gray-400">Libellé</dt><dd class="text-gray-900 dark:text-white" x-text="detail.libelle"></dd></div>
                        <div><dt class="text-xs text-gray-400">Date</dt><dd class="text-gray-900 dark:text-white" x-text="detail.date_operation_formatee"></dd></div>
                        <div><dt class="text-xs text-gray-400">Compte</dt><dd class="text-gray-900 dark:text-white" x-text="detail.compte ? detail.compte.banque + ' — ' + detail.compte.numero_compte : '-'"></dd></div>
                        <div><dt class="text-xs text-gray-400">Référence</dt><dd class="text-gray-900 dark:text-white" x-text="detail.reference || '-'"></dd></div>
                        <div><dt class="text-xs text-gray-400">Saisie par</dt><dd class="text-gray-900 dark:text-white" x-text="detail.auteur ? detail.auteur.nom : '-'"></dd></div>
                        <div x-show="detail.validateur" class="sm:col-span-2">
                            <dt class="text-xs text-gray-400" x-text="detail.statut === 'REJETE' ? 'Rejetée par' : 'Validée par'"></dt>
                            <dd class="text-gray-900 dark:text-white" x-text="(detail.validateur ? detail.validateur.nom : '') + (detail.valide_le ? ' le ' + new Date(detail.valide_le).toLocaleString('fr-FR') : '')"></dd>
                        </div>
                        <div x-show="detail.motif_rejet" class="sm:col-span-2"><dt class="text-xs text-gray-400">Motif du rejet</dt><dd class="text-red-600" x-text="detail.motif_rejet"></dd></div>
                        <div x-show="detail.note" class="sm:col-span-2"><dt class="text-xs text-gray-400">Note</dt><dd class="text-gray-900 dark:text-white whitespace-pre-line" x-text="detail.note"></dd></div>
                    </dl>
                </div>
                <div class="flex flex-wrap justify-end gap-2 px-6 py-4 border-t border-gray-200 dark:border-gray-800">
                    <button @click="detail = null" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-700 rounded-lg">Fermer</button>
                    <button x-show="detail.statut !== 'VALIDE' && window.gsfUser.can.banque?.update" @click="const o = detail; detail = null; openEdit(o)"
                        class="px-4 py-2 text-sm font-medium text-blue-700 border border-blue-300 rounded-lg hover:bg-blue-50 dark:text-blue-400 dark:border-blue-800">Modifier</button>
                    <button x-show="detail.statut === 'EN_ATTENTE' && window.gsfUser.can.banque?.rejeter" @click="const o = detail; detail = null; openRejet(o)"
                        class="px-4 py-2 text-sm font-medium text-amber-700 border border-amber-300 rounded-lg hover:bg-amber-50 dark:text-amber-400 dark:border-amber-800">Rejeter</button>
                    <button x-show="detail.statut === 'EN_ATTENTE' && window.gsfUser.can.banque?.valider" @click="const o = detail; detail = null; valider(o)"
                        class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">Valider</button>
                </div>
            </div>
        </template>
    </div>
</div>
</template>

{{-- ════════════════════════ MODAL REJET ════════════════════════ --}}
<template x-teleport="body">
<div x-show="rejet.op" x-cloak class="fixed inset-0 z-[100001] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="rejet.op = null"></div>
    <form @submit.prevent="confirmRejet()" class="relative w-full max-w-md bg-white dark:bg-gray-900 rounded-2xl shadow-2xl p-6 space-y-4" @click.stop>
        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Rejeter l'opération</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400" x-text="rejet.op ? rejet.op.libelle + ' — ' + fmt(rejet.op.montant) : ''"></p>
        <div>
            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Motif <span class="text-red-500">*</span></label>
            <textarea x-model="rejet.motif" required maxlength="255" rows="3"
                class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white"></textarea>
        </div>
        <div class="flex gap-3">
            <button type="button" @click="rejet.op = null" class="flex-1 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-700 rounded-lg">Annuler</button>
            <button type="submit" :disabled="saving" class="flex-1 py-2 text-sm font-medium text-white bg-amber-500 rounded-lg hover:bg-amber-600 disabled:opacity-50">Rejeter</button>
        </div>
    </form>
</div>
</template>

{{-- ════════════════════════ MODAL COMPTES BANCAIRES (ADMIN) ════════════════════════ --}}
<template x-teleport="body">
<div x-show="showComptes" x-cloak @keydown.escape.window="showComptes = false" class="fixed inset-0 z-[100000] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showComptes = false"></div>
    <div class="relative w-full max-w-2xl max-h-[90vh] overflow-y-auto bg-white dark:bg-gray-900 rounded-2xl shadow-2xl" @click.stop>
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-800">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">Comptes bancaires GSF</h2>
            <button @click="showComptes = false" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-800">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="p-6 space-y-3" x-show="!compteForm.visible">
            <template x-for="c in comptes" :key="'mc' + c.id">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 rounded-lg border border-gray-200 dark:border-gray-800 p-3">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white" x-text="c.banque + (c.actif ? '' : ' (inactif)')"></p>
                        <p class="text-xs text-gray-400" x-text="c.numero_compte + (c.intitule ? ' — ' + c.intitule : '')"></p>
                        <p class="text-xs text-gray-400" x-text="'Solde initial : ' + fmt(c.solde_initial) + (c.date_solde_initial ? ' au ' + new Date(c.date_solde_initial).toLocaleDateString('fr-FR') : '')"></p>
                    </div>
                    <div class="flex gap-2 shrink-0">
                        <button @click="openCompteForm(c)" class="px-3 py-1.5 text-xs font-medium text-blue-700 border border-blue-300 rounded-lg hover:bg-blue-50 dark:text-blue-400 dark:border-blue-800">Modifier</button>
                        <button @click="deleteCompte(c)" class="px-3 py-1.5 text-xs font-medium text-red-700 border border-red-300 rounded-lg hover:bg-red-50 dark:text-red-400 dark:border-red-800">Supprimer</button>
                    </div>
                </div>
            </template>
            <button @click="openCompteForm()" class="w-full py-2.5 text-sm font-medium text-brand-600 border border-dashed border-brand-400 rounded-lg hover:bg-brand-50 dark:hover:bg-brand-500/10">
                + Ajouter un compte bancaire
            </button>
        </div>

        <form x-show="compteForm.visible" @submit.prevent="saveCompte()" class="p-6 space-y-4">
            <div x-show="compteForm.error" class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-300" x-text="compteForm.error"></div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Banque <span class="text-red-500">*</span></label>
                    <input type="text" x-model="compteForm.data.banque" required maxlength="100" list="banques-ci" placeholder="Ex : SGBCI"
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                    <datalist id="banques-ci">
                        <option value="SGBCI"></option><option value="Ecobank CI"></option><option value="NSIA Banque"></option>
                        <option value="Orabank"></option><option value="BICICI"></option><option value="Bridge Bank Group"></option>
                        <option value="Coris Bank International"></option><option value="Bank of Africa"></option>
                    </datalist>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Numéro de compte <span class="text-red-500">*</span></label>
                    <input type="text" x-model="compteForm.data.numero_compte" required maxlength="100"
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Intitulé</label>
                <input type="text" x-model="compteForm.data.intitule" maxlength="150" placeholder="Ex : Compte courant GSF SARL"
                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Solde initial (FCFA) <span class="text-red-500">*</span></label>
                    <input type="number" x-model="compteForm.data.solde_initial" step="1" required
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                    <p class="mt-1 text-xs text-gray-400">Solde du relevé à la date de départ du suivi.</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">À la date du</label>
                    <input type="date" x-model="compteForm.data.date_solde_initial"
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                </div>
            </div>
            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                <input type="checkbox" x-model="compteForm.data.actif" class="rounded border-gray-300">
                Compte actif (peut recevoir de nouvelles opérations)
            </label>
            <div class="flex gap-3 pt-2">
                <button type="button" @click="compteForm.visible = false" class="flex-1 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-700 rounded-lg">Retour</button>
                <button type="submit" :disabled="saving" class="flex-1 py-2 text-sm font-medium text-white bg-brand-500 rounded-lg hover:bg-brand-600 disabled:opacity-50">Enregistrer</button>
            </div>
        </form>
    </div>
</div>
</template>

</div>

<script>
function banqueApp() {
    const csrf = () => document.querySelector('meta[name="csrf-token"]').content;
    const today = new Date().toISOString().substring(0, 10);
    const emptyForm = () => ({ id_compte: '', type: 'CREDIT', categorie: 'DEPOT_ESPECES', libelle: '', montant: '', date_operation: today, reference: '', note: '' });

    return {
        today,
        loading: true,
        loadingOps: false,
        comptes: [],
        categories: { CREDIT: {}, DEBIT: {} },
        operations: [],
        filtres: { compte: '', type: '', statut: '', du: '', au: '' },
        page: 1,
        perPage: 15,

        showForm: false,
        editId: null,
        form: emptyForm(),
        formError: '',
        saving: false,

        detail: null,
        rejet: { op: null, motif: '' },

        showComptes: false,
        compteForm: { visible: false, id: null, error: '', data: {} },

        async init() {
            await this.loadComptes();
            await this.loadOperations();
        },

        get comptesActifs() { return this.comptes.filter(c => c.actif); },
        // Disponible pour la sortie en cours de saisie (une sortie en attente qu'on modifie ne se déduit pas d'elle-même)
        get disponibleForm() {
            const c = this.comptes.find(c => String(c.id) === String(this.form.id_compte));
            if (!c) return 0;
            const editing = this.editId && this.operations.find(o => o.id === this.editId);
            const rendu = editing && editing.type === 'DEBIT' && editing.statut === 'EN_ATTENTE' && String(editing.id_compte) === String(c.id) ? +editing.montant : 0;
            return +c.disponible + rendu;
        },
        get depasseDisponible() {
            return this.form.type === 'DEBIT' && +this.form.montant > 0 && +this.form.montant > this.disponibleForm;
        },
        get totalPages() { return Math.max(1, Math.ceil(this.operations.length / this.perPage)); },
        get paginated() { return this.operations.slice((this.page - 1) * this.perPage, this.page * this.perPage); },
        get totaux() {
            return this.operations.reduce((t, o) => {
                if (o.statut === 'VALIDE') o.type === 'CREDIT' ? t.entrees += +o.montant : t.sorties += +o.montant;
                if (o.statut === 'EN_ATTENTE') t.enAttente++;
                return t;
            }, { entrees: 0, sorties: 0, enAttente: 0 });
        },

        fmt(val) { return new Intl.NumberFormat('fr-FR').format(Math.round(val || 0)) + ' FCFA'; },
        statutLabel(s) { return { EN_ATTENTE: 'En attente', VALIDE: 'Validée', REJETE: 'Rejetée' }[s] || s; },
        statutClass(s) {
            return {
                EN_ATTENTE: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                VALIDE: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                REJETE: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
            }[s] || '';
        },

        async request(url, method = 'GET', body = null) {
            const r = await fetch(url, {
                method,
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf() },
                body: body ? JSON.stringify(body) : null,
            });
            const data = await r.json().catch(() => ({}));
            if (!r.ok) {
                const msg = data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Erreur ' + r.status);
                throw new Error(msg);
            }
            return data;
        },

        async loadComptes() {
            try {
                const data = await this.request('/api/banque/comptes');
                this.comptes = data.comptes;
                this.categories = data.categories;
            } catch (e) {
                window.gsfToast(e.message, 'error');
            } finally {
                this.loading = false;
            }
        },

        async loadOperations() {
            this.loadingOps = true;
            this.page = 1;
            try {
                const params = new URLSearchParams(Object.entries(this.filtres).filter(([, v]) => v));
                this.operations = await this.request('/api/banque/operations?' + params);
            } catch (e) {
                window.gsfToast(e.message, 'error');
            } finally {
                this.loadingOps = false;
            }
        },

        async refresh() {
            await Promise.all([this.loadComptes(), this.loadOperations()]);
        },

        // ── Opérations ──
        setType(type) {
            this.form.type = type;
            if (!(this.form.categorie in (this.categories[type] || {}))) {
                this.form.categorie = Object.keys(this.categories[type] || {})[0] || '';
            }
        },

        openCreate() {
            this.editId = null;
            this.form = emptyForm();
            this.form.id_compte = this.filtres.compte || String(this.comptesActifs[0]?.id || '');
            this.formError = '';
            this.showForm = true;
        },

        openEdit(op) {
            this.editId = op.id;
            this.form = {
                id_compte: String(op.id_compte), type: op.type, categorie: op.categorie, libelle: op.libelle,
                montant: Math.round(op.montant), date_operation: op.date_operation, reference: op.reference || '', note: op.note || '',
            };
            this.formError = '';
            this.showForm = true;
        },

        openDetail(op) { this.detail = op; },

        async saveOperation() {
            this.saving = true;
            this.formError = '';
            try {
                const data = this.editId
                    ? await this.request(`/api/banque/operations/${this.editId}`, 'PUT', this.form)
                    : await this.request('/api/banque/operations', 'POST', this.form);
                this.showForm = false;
                window.gsfToast(data.message);
                await this.refresh();
            } catch (e) {
                this.formError = e.message;
            } finally {
                this.saving = false;
            }
        },

        async valider(op) {
            const ok = await window.gsfConfirm({
                title: 'Valider l\'opération',
                message: `${op.type === 'CREDIT' ? 'Entrée' : 'Sortie'} de <strong>${this.fmt(op.montant)}</strong> — ${op.libelle}.<br>Le solde du compte sera mis à jour et l'opération ne pourra plus être modifiée.`,
                type: 'info',
                confirmText: 'Valider',
            });
            if (!ok) return;
            try {
                const data = await this.request(`/api/banque/operations/${op.id}/valider`, 'POST');
                window.gsfToast(data.message);
                await this.refresh();
            } catch (e) {
                window.gsfToast(e.message, 'error');
            }
        },

        openRejet(op) { this.rejet = { op, motif: '' }; },

        async confirmRejet() {
            this.saving = true;
            try {
                const data = await this.request(`/api/banque/operations/${this.rejet.op.id}/rejeter`, 'POST', { motif: this.rejet.motif });
                this.rejet = { op: null, motif: '' };
                window.gsfToast(data.message, 'warning');
                await this.refresh();
            } catch (e) {
                window.gsfToast(e.message, 'error');
            } finally {
                this.saving = false;
            }
        },

        async supprimer(op) {
            const ok = await window.gsfConfirm({ title: 'Supprimer l\'opération', message: `${op.libelle} — ${this.fmt(op.montant)}`, confirmText: 'Supprimer' });
            if (!ok) return;
            try {
                const data = await this.request(`/api/banque/operations/${op.id}`, 'DELETE');
                window.gsfToast(data.message);
                await this.refresh();
            } catch (e) {
                window.gsfToast(e.message, 'error');
            }
        },

        // ── Comptes ──
        openComptes() {
            this.compteForm.visible = false;
            this.showComptes = true;
        },

        openCompteForm(c = null) {
            this.compteForm = {
                visible: true,
                id: c ? c.id : null,
                error: '',
                data: c
                    ? { banque: c.banque, numero_compte: c.numero_compte, intitule: c.intitule || '', solde_initial: Math.round(c.solde_initial), date_solde_initial: c.date_solde_initial || '', actif: !!c.actif }
                    : { banque: '', numero_compte: '', intitule: '', solde_initial: 0, date_solde_initial: today, actif: true },
            };
            this.showComptes = true;
        },

        async saveCompte() {
            this.saving = true;
            this.compteForm.error = '';
            try {
                const payload = { ...this.compteForm.data, date_solde_initial: this.compteForm.data.date_solde_initial || null };
                const data = this.compteForm.id
                    ? await this.request(`/api/banque/comptes/${this.compteForm.id}`, 'PUT', payload)
                    : await this.request('/api/banque/comptes', 'POST', payload);
                this.compteForm.visible = false;
                window.gsfToast(data.message);
                await this.refresh();
            } catch (e) {
                this.compteForm.error = e.message;
            } finally {
                this.saving = false;
            }
        },

        async deleteCompte(c) {
            const ok = await window.gsfConfirm({ title: 'Supprimer le compte', message: `${c.banque} — ${c.numero_compte}`, confirmText: 'Supprimer' });
            if (!ok) return;
            try {
                const data = await this.request(`/api/banque/comptes/${c.id}`, 'DELETE');
                window.gsfToast(data.message);
                if (this.filtres.compte == c.id) this.filtres.compte = '';
                await this.refresh();
            } catch (e) {
                window.gsfToast(e.message, 'error');
            }
        },
    };
}
</script>

@endsection

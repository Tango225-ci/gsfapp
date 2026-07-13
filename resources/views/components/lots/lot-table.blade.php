<div x-data="dataTable" x-init="init()">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
        <!-- Header avec recherche et boutons -->
        <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Tableau Récapitulatif des Lots</h2>

                <div class="flex items-center gap-3 flex-wrap">
                    <!-- Filtre par statut -->
                    <select x-model="filtreStatut" @change="currentPage = 1"
                        class="w-40 rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">Tous les statuts</option>
                        <option value="DISPONIBLE">Disponibles</option>
                        <option value="RESERVE">Réservés</option>
                        <option value="VENDU">Vendus</option>
                    </select>

                    <!-- Filtre par type -->
                    <select x-model="filtreType" @change="currentPage = 1"
                        class="w-40 rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">Tous les types</option>
                        <option value="RESIDENTIEL">Résidentiel</option>
                        <option value="COMMERCIAL">Commercial</option>
                        <option value="INDUSTRIEL">Industriel</option>
                        <option value="MIXTE">Mixte</option>
                    </select>

                    <!-- Filtre par lotissement -->
                    <select x-model="filtreLotissement" @change="currentPage = 1"
                        class="w-48 rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">Tous les lotissements</option>
                        <template x-for="lotissement in lotissementsListe" :key="lotissement.id_lotissement">
                            <option :value="lotissement.id_lotissement" x-text="lotissement.nom"></option>
                        </template>
                    </select>

                    <!-- Barre de recherche -->
                    <div class="relative w-56">
                        <input type="text" x-model="search" placeholder="Rechercher numéro, îlot..."
                            class="w-full pl-4 pr-10 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white text-sm">
                        <svg class="absolute right-3 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>

                    <!-- Bouton Nouveau Lot -->
                    <button @click="newLot" x-show="window.gsfUser.can.lots.create"
                        class="flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Nouveau
                    </button>

                    <!-- Bouton Export CSV -->
                    <button @click="exportCSV"
                        class="flex items-center gap-2 border border-gray-300 dark:border-gray-600 px-4 py-2 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        CSV
                    </button>

                    <!-- Bouton Export Excel -->
                    <button @click="exportExcel"
                        class="flex items-center gap-2 border border-gray-300 dark:border-gray-600 px-4 py-2 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Excel
                    </button>
                </div>
            </div>
        </div>

        <!-- Cartes statistiques -->
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 p-4 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm text-center">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total lots</p>
                <p class="mt-2 text-4xl font-bold text-gray-800 dark:text-white" x-text="stats.total"></p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm text-center">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Disponibles</p>
                <p class="mt-2 text-4xl font-bold text-green-600 dark:text-green-400" x-text="stats.disponibles"></p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm text-center">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Réservés</p>
                <p class="mt-2 text-4xl font-bold text-yellow-600 dark:text-yellow-400" x-text="stats.reserves"></p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm text-center">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Vendus</p>
                <p class="mt-2 text-4xl font-bold text-red-600 dark:text-red-400" x-text="stats.vendus"></p>
            </div>
        </div>

        <!-- Overlay formulaire création lot -->
        <template x-teleport="body">
        <div x-show="showLotForm" x-cloak @keydown.escape.window="closeLotForm" class="fixed inset-0 z-[100000] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div @click.stop class="relative w-full max-w-3xl max-h-[90vh] overflow-y-auto rounded-2xl bg-white shadow-2xl dark:bg-gray-900">

                <!-- En-tête -->
                <div class="sticky top-0 flex items-center justify-between border-b border-gray-200 bg-white px-6 py-4 dark:border-gray-700 dark:bg-gray-900">
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white" x-text="editingLot ? 'Modifier le lot' : 'Nouveau lot'"></h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400" x-text="editingLot ? 'Modifiez les informations du lot' : 'Ajoutez un lot avec un formulaire clair et rapide'"></p>
                    </div>
                    <button type="button" @click="closeLotForm"
                        class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Formulaire -->
                <form @submit.prevent="saveLot" class="p-6">
                    <template x-if="formErrors.length">
                        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800 dark:border-red-800 dark:bg-red-900/20 dark:text-red-200">
                            <p class="font-semibold">Veuillez corriger les erreurs :</p>
                            <ul class="mt-2 list-inside list-disc">
                                <template x-for="error in formErrors" :key="error">
                                    <li x-text="error"></li>
                                </template>
                            </ul>
                        </div>
                    </template>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Numéro du lot *</label>
                            <input type="text" x-model="form.numero_lot" required
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                placeholder="LOT-001">
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Numéro îlot *</label>
                            <input type="text" x-model="form.numero_ilot" required maxlength="10"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                placeholder="ILOT-01">
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Type de lot</label>
                            <select x-model="form.type_lot"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                                <option value="">Sélectionnez un type</option>
                                <option value="RESIDENTIEL">Résidentiel</option>
                                <option value="COMMERCIAL">Commercial</option>
                                <option value="INDUSTRIEL">Industriel</option>
                                <option value="MIXTE">Mixte</option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Superficie (m²)</label>
                            <input type="number" step="0.01" x-model="form.superficie"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                placeholder="500.00">
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Prix Comptant (FCFA)</label>
                            <input type="number" step="1000" min="0" x-model="form.prix_comptant"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                placeholder="15000000">
                            <p class="mt-1 text-xs text-gray-500">Prix pour un paiement en une seule fois</p>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Prix Échelonné (FCFA)</label>
                            <input type="number" step="1000" min="0" x-model="form.prix_echelonne"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                placeholder="18000000">
                            <p class="mt-1 text-xs text-gray-500">Prix pour un paiement en plusieurs fois</p>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Statut</label>
                            <select x-model="form.statut"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                                <option value="DISPONIBLE">Disponible</option>
                                <option value="RESERVE">Réservé</option>
                                <option value="VENDU">Vendu</option>
                            </select>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Lotissement</label>
                            <select x-model="form.id_lotissement"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                                <option value="">Sélectionnez un lotissement</option>
                                <template x-for="lotissement in lotissementsListe" :key="lotissement.id_lotissement">
                                    <option :value="lotissement.id_lotissement" x-text="lotissement.nom"></option>
                                </template>
                            </select>
                        </div>

                        <div class="sm:col-span-2 border-t border-gray-100 dark:border-gray-700 pt-4">
                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-3">Propriétaire terrien</p>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Propriétaire terrien</label>
                            <select x-model="form.id_proprietaire"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                                <option value="">Aucun / Non renseigné</option>
                                <template x-for="p in proprietairesListe" :key="p.id_proprietaire">
                                    <option :value="p.id_proprietaire" x-text="p.nom + (p.contact ? ' — ' + p.contact : '')"></option>
                                </template>
                            </select>
                            <p class="mt-1 text-xs text-gray-400">Propriétaire foncier du terrain (facultatif)</p>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Prix d'achat (FCFA)</label>
                            <input type="number" step="1000" min="0" x-model="form.prix_achat"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                placeholder="0">
                            <p class="mt-1 text-xs text-gray-500">Montant dû au propriétaire pour ce lot</p>
                        </div>
                    </div>

                    <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                        <p class="text-xs text-blue-700 dark:text-blue-300 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Au moins un des deux prix (comptant ou échelonné) doit être renseigné.</span>
                        </p>
                    </div>

                    <div class="mt-6 flex gap-3 border-t border-gray-200 pt-6 dark:border-gray-700">
                        <button type="button" @click="closeLotForm"
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">
                            Annuler
                        </button>
                        <button type="submit"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            <span x-text="editingLot ? 'Mettre à jour' : 'Ajouter lot'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        </template>

        <!-- Overlay formulaire affichage détails lot -->
        <template x-teleport="body">
        <div x-show="showDetailsModal" x-cloak @keydown.escape.window="closeDetailsModal" class="fixed inset-0 z-[100000] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div @click.stop class="relative w-full max-w-2xl max-h-[85vh] overflow-y-auto rounded-xl bg-white shadow-2xl dark:bg-gray-900">

                <div class="sticky top-0 flex items-center justify-between border-b border-gray-200 bg-white px-5 py-3 dark:border-gray-700 dark:bg-gray-900">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Détails du lot</h3>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Informations complètes (lecture seule)</p>
                    </div>
                    <button type="button" @click="closeDetailsModal"
                        class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="p-5">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">ID Lot</label>
                            <input type="text" x-model="selectedLot?.id_lot" readonly disabled
                                class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-1.5 text-sm text-gray-600 cursor-not-allowed dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400">
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Date création</label>
                            <input type="text" x-model="selectedLot?.created_at_formatted" readonly disabled
                                class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-1.5 text-sm text-gray-600 cursor-not-allowed dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400">
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Numéro lot</label>
                            <input type="text" x-model="selectedLot?.numero_lot" readonly disabled
                                class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-1.5 text-sm text-gray-600 cursor-not-allowed dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400">
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Numéro îlot</label>
                            <input type="text" x-model="selectedLot?.numero_ilot" readonly disabled
                                class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-1.5 text-sm text-gray-600 cursor-not-allowed dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400">
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Type lot</label>
                            <input type="text" x-model="selectedLot?.type_lot" readonly disabled
                                class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-1.5 text-sm text-gray-600 cursor-not-allowed dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400"
                                placeholder="Non renseigné">
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Superficie (m²)</label>
                            <input type="text" x-model="selectedLot?.superficie" readonly disabled
                                class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-1.5 text-sm text-gray-600 cursor-not-allowed dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400"
                                placeholder="Non renseignée">
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Prix Comptant</label>
                            <input type="text" x-model="selectedLot?.prix_comptant_formatted" readonly disabled
                                class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-1.5 text-sm text-gray-600 cursor-not-allowed dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400">
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Prix Échelonné</label>
                            <input type="text" x-model="selectedLot?.prix_echelonne_formatted" readonly disabled
                                class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-1.5 text-sm text-gray-600 cursor-not-allowed dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400">
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Statut</label>
                            <input type="text" x-model="selectedLot?.statut_label" readonly disabled
                                class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-1.5 text-sm text-gray-600 cursor-not-allowed dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Lotissement</label>
                            <input type="text" x-model="selectedLot?.lotissement_nom" readonly disabled
                                class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-1.5 text-sm text-gray-600 cursor-not-allowed dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400"
                                placeholder="Non rattaché">
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Propriétaire terrien</label>
                            <input type="text" :value="selectedLot?.proprietaire_nom || '—'" readonly disabled
                                class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-1.5 text-sm text-gray-600 cursor-not-allowed dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400">
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Prix d'achat</label>
                            <input type="text" :value="selectedLot?.prix_achat > 0 ? new Intl.NumberFormat('fr-FR').format(selectedLot.prix_achat) + ' FCFA' : '—'" readonly disabled
                                class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-1.5 text-sm text-gray-600 cursor-not-allowed dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400">
                        </div>
                    </div>

                    <div class="mt-5 flex gap-3 border-t border-gray-200 pt-4 dark:border-gray-700">
                        <button type="button" @click="closeDetailsModal"
                            class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">
                            Fermer
                        </button>
                        <button type="button" @click="editFromDetails"
                            x-show="selectedLot?.can_be_modified"
                            class="rounded-lg bg-blue-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Modifier le lot
                        </button>
                    </div>
                </div>
            </div>
        </div>
        </template>

        <!-- Tableau -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">NUMÉRO LOT</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">ÎLOT</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">TYPE</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">SUPERFICIE</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">PRIX COMPTANT</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">PRIX ÉCHELONNÉ</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">STATUT</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">LOTISSEMENT</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <template x-for="item in paginatedData" :key="item.id_lot">
                        <tr @click="selectRow(item.id_lot)"
                            :class="selectedRow === item.id_lot ? 'bg-gray-200 dark:bg-gray-700 border-l-4 border-blue-500 shadow-sm' : 'hover:bg-gray-50 dark:hover:bg-gray-700/50'"
                            class="cursor-pointer transition-all duration-200">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white" x-text="item.id_lot"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white" x-text="item.numero_lot"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400" x-text="item.numero_ilot"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400" x-text="item.type_lot || '-'"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400" x-text="item.superficie ? item.superficie + ' m²' : '-'"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400" x-text="item.prix_comptant_formatted"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400" x-text="item.prix_echelonne_formatted"></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span x-text="item.statut_label"
                                    :class="{
                                        'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-200': item.statut === 'DISPONIBLE',
                                        'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-200': item.statut === 'RESERVE',
                                        'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-200': item.statut === 'VENDU'
                                    }"
                                    class="px-2 py-1 rounded-full text-xs font-semibold"></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400" x-text="item.lotissement_nom || '-'"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button @click.stop="viewDetails(item)"
                                    class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300 mr-3 transition-colors" title="Détails">
                                    <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </button>
                                <button @click.stop="editItem(item)"
                                    x-show="item.can_be_modified && window.gsfUser.can.lots.update"
                                    class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 mr-3 transition-colors" title="Modifier">
                                    <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>
                                <button @click.stop="deleteItem(item.id_lot)"
                                    x-show="item.can_be_deleted && window.gsfUser.can.lots.delete"
                                    class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 transition-colors" title="Supprimer">
                                    <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    </template>

                    <tr x-show="filteredData.length === 0">
                        <td colspan="10" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                            <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="text-lg font-medium">Aucun lot trouvé</p>
                            <p class="text-sm mt-1">Essayez de modifier vos filtres</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                <div class="text-sm text-gray-700 dark:text-gray-300">
                    Affichage de <span class="font-medium" x-text="startIndex"></span> à <span class="font-medium" x-text="endIndex"></span> sur <span class="font-medium" x-text="filteredData.length"></span> résultats
                </div>
                <div class="flex gap-2">
                    <button @click="prevPage" :disabled="currentPage === 1"
                        :class="currentPage === 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100 dark:hover:bg-gray-700'"
                        class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded-md text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 transition-colors">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        Précédent
                    </button>
                    <span class="px-3 py-1 text-sm text-gray-700 dark:text-gray-300">
                        Page <span class="font-medium" x-text="currentPage"></span> / <span class="font-medium" x-text="totalPages"></span>
                    </span>
                    <button @click="nextPage" :disabled="currentPage === totalPages"
                        :class="currentPage === totalPages ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100 dark:hover:bg-gray-700'"
                        class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded-md text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 transition-colors">
                        Suivant
                        <svg class="w-4 h-4 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('dataTable', () => ({
        search: '',
        filtreStatut: '',
        filtreType: '',
        filtreLotissement: '',
        currentPage: 1,
        itemsPerPage: 10,
        selectedRow: null,
        showLotForm: false,
        showDetailsModal: false,
        selectedLot: null,
        editingLot: null,
        formErrors: [],
        lotissementsListe: [],
        proprietairesListe: [],
        form: {
            numero_lot: '',
            numero_ilot: '',
            type_lot: '',
            superficie: '',
            prix_comptant: '',
            prix_echelonne: '',
            prix_achat: '',
            statut: 'DISPONIBLE',
            id_lotissement: '',
            id_proprietaire: ''
        },
        items: [],

        get stats() {
            const total = this.items.length;
            const disponibles = this.items.filter(i => i.statut === 'DISPONIBLE').length;
            const reserves = this.items.filter(i => i.statut === 'RESERVE').length;
            const vendus = this.items.filter(i => i.statut === 'VENDU').length;
            return { total, disponibles, reserves, vendus };
        },

        get filteredData() {
            let data = [...this.items];

            if (this.filtreStatut) {
                data = data.filter(i => i.statut === this.filtreStatut);
            }

            if (this.filtreType) {
                data = data.filter(i => i.type_lot === this.filtreType);
            }

            if (this.filtreLotissement) {
                data = data.filter(i => i.id_lotissement == this.filtreLotissement);
            }

            if (this.search) {
                const searchLower = this.search.toLowerCase();
                data = data.filter(item =>
                    (item.numero_lot && item.numero_lot.toLowerCase().includes(searchLower)) ||
                    (item.numero_ilot && item.numero_ilot.toLowerCase().includes(searchLower)) ||
                    (item.type_lot && item.type_lot.toLowerCase().includes(searchLower)) ||
                    (item.lotissement_nom && item.lotissement_nom.toLowerCase().includes(searchLower))
                );
            }

            return data;
        },

        get totalPages() {
            return Math.ceil(this.filteredData.length / this.itemsPerPage) || 1;
        },

        get paginatedData() {
            const start = (this.currentPage - 1) * this.itemsPerPage;
            const end = start + this.itemsPerPage;
            return this.filteredData.slice(start, end);
        },

        get startIndex() {
            if (this.filteredData.length === 0) return 0;
            return (this.currentPage - 1) * this.itemsPerPage + 1;
        },

        get endIndex() {
            return Math.min(this.currentPage * this.itemsPerPage, this.filteredData.length);
        },

        nextPage() {
            if (this.currentPage < this.totalPages) {
                this.currentPage++;
                this.selectedRow = null;
            }
        },

        prevPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
                this.selectedRow = null;
            }
        },

        selectRow(id) {
            this.selectedRow = id;
        },

        getStatutLabel(statut) {
            const labels = {
                'DISPONIBLE': 'Disponible',
                'RESERVE': 'Réservé',
                'VENDU': 'Vendu'
            };
            return labels[statut] || statut;
        },

        formatPrice(price) {
            if (!price && price !== 0) return '0 FCFA';
            const numPrice = parseFloat(price);
            if (isNaN(numPrice) || numPrice === 0) return '0 FCFA';
            return new Intl.NumberFormat('fr-FR').format(numPrice) + ' FCFA';
        },

        formatDate(dateString) {
            if (!dateString) return '-';
            const date = new Date(dateString);
            if (isNaN(date.getTime())) return dateString;
            return date.toLocaleDateString('fr-FR');
        },

        checkDuplicateLot() {
            const numeroLot = this.form.numero_lot?.trim();
            const numeroIlot = this.form.numero_ilot?.trim();
            const idLotissement = this.form.id_lotissement;

            if (!numeroLot || !numeroIlot || !idLotissement) return false;

            const numeroLotLower = numeroLot.toLowerCase();
            const numeroIlotLower = numeroIlot.toLowerCase();

            return this.items.some(item => {
                if (this.editingLot && item.id_lot === this.editingLot.id_lot) return false;
                return item.numero_lot?.toLowerCase() === numeroLotLower &&
                       item.numero_ilot?.toLowerCase() === numeroIlotLower &&
                       item.id_lotissement == idLotissement;
            });
        },

        async init() {
            await Promise.all([this.loadLotissements(), this.loadProprietaires()]);
            await this.loadLotsFromDB();
        },

        async loadLotissements() {
            try {
                const response = await fetch('/api/lotissements');
                if (response.ok) {
                    this.lotissementsListe = await response.json();
                } else {
                    this.lotissementsListe = [];
                }
            } catch (error) {
                console.error('Erreur chargement lotissements:', error);
                this.lotissementsListe = [];
            }
        },

        async loadProprietaires() {
            try {
                const response = await fetch('/api/proprietaires/liste');
                if (response.ok) {
                    this.proprietairesListe = await response.json();
                } else {
                    this.proprietairesListe = [];
                }
            } catch (error) {
                this.proprietairesListe = [];
            }
        },

        getProprietaireNom(id) {
            if (!id) return '—';
            const p = this.proprietairesListe.find(p => p.id_proprietaire == id);
            return p ? p.nom : '—';
        },

        getLotissementNom(idLotissement) {
            if (!idLotissement) return '-';
            const lotissement = this.lotissementsListe.find(l => l.id_lotissement == idLotissement);
            return lotissement ? lotissement.nom : '-';
        },

        newLot() {
            this.editingLot = null;
            this.form = {
                numero_lot: '',
                numero_ilot: '',
                type_lot: '',
                superficie: '',
                prix_comptant: '',
                prix_echelonne: '',
                prix_achat: '',
                statut: 'DISPONIBLE',
                id_lotissement: '',
                id_proprietaire: ''
            };
            this.formErrors = [];
            this.showLotForm = true;
        },

        editItem(item) {
            if (!item.can_be_modified) {
                window.gsfToast('Ce lot ne peut pas être modifié car il est ' + item.statut_label.toLowerCase(), 'warning');
                return;
            }
            this.editingLot = item;
            this.form = {
                numero_lot: item.numero_lot,
                numero_ilot: item.numero_ilot,
                type_lot: item.type_lot || '',
                superficie: item.superficie || '',
                prix_comptant: item.prix_comptant || '',
                prix_echelonne: item.prix_echelonne || '',
                prix_achat: item.prix_achat || '',
                statut: item.statut,
                id_lotissement: item.id_lotissement || '',
                id_proprietaire: item.id_proprietaire || ''
            };
            this.formErrors = [];
            this.showLotForm = true;
        },

        closeLotForm() {
            this.showLotForm = false;
            this.editingLot = null;
            this.formErrors = [];
        },

        closeDetailsModal() {
            this.showDetailsModal = false;
            this.selectedLot = null;
        },

        viewDetails(item) {
            this.selectedLot = item;
            this.showDetailsModal = true;
        },

        editFromDetails() {
            const item = this.selectedLot;
            this.closeDetailsModal();
            this.editItem(item);
        },

        async loadLotsFromDB() {
            try {
                const response = await fetch('/api/lots');
                if (!response.ok) throw new Error('HTTP ' + response.status);
                const data = await response.json();

                if (Array.isArray(data) && data.length > 0) {
                    this.items = data.map(lot => ({
                        id_lot: lot.id_lot,
                        numero_lot: lot.numero_lot,
                        numero_ilot: lot.numero_ilot,
                        type_lot: lot.type_lot,
                        superficie: lot.superficie,
                        prix_comptant: parseFloat(lot.prix_comptant) || 0,
                        prix_comptant_formatted: lot.prix_comptant_formatted || this.formatPrice(lot.prix_comptant),
                        prix_echelonne: parseFloat(lot.prix_echelonne) || 0,
                        prix_echelonne_formatted: lot.prix_echelonne_formatted || this.formatPrice(lot.prix_echelonne),
                        prix_achat: parseFloat(lot.prix_achat) || 0,
                        statut: lot.statut,
                        statut_label: this.getStatutLabel(lot.statut),
                        id_lotissement: lot.id_lotissement,
                        lotissement_nom: lot.lotissement_nom || this.getLotissementNom(lot.id_lotissement),
                        id_proprietaire: lot.id_proprietaire,
                        proprietaire_nom: lot.proprietaire_nom || this.getProprietaireNom(lot.id_proprietaire),
                        created_at: lot.created_at,
                        created_at_formatted: this.formatDate(lot.created_at),
                        can_be_modified: lot.can_be_modified || false,
                        can_be_deleted: lot.can_be_deleted || false
                    }));
                } else {
                    this.items = [];
                }
                this.currentPage = 1;
            } catch (error) {
                console.error('Erreur chargement lots:', error);
                this.items = [];
            }
        },

        async saveLot() {
            this.formErrors = [];

            if (!this.form.numero_lot.trim()) {
                this.formErrors.push('Le numéro du lot est requis.');
            }
            if (!this.form.numero_ilot.trim()) {
                this.formErrors.push('Le numéro d\'îlot est requis.');
            }

            const prixComptant = parseFloat(this.form.prix_comptant) || 0;
            const prixEchelonne = parseFloat(this.form.prix_echelonne) || 0;

            if (prixComptant <= 0 && prixEchelonne <= 0) {
                this.formErrors.push('Au moins un prix (comptant ou échelonné) doit être supérieur à 0.');
            }

            if (this.form.numero_lot.trim() && this.form.numero_ilot.trim() && this.form.id_lotissement) {
                if (this.checkDuplicateLot()) {
                    this.formErrors.push('Un lot avec le même numéro, îlot et lotissement existe déjà.');
                }
            }

            if (this.formErrors.length) return;

            const confirmed = await window.gsfConfirm({
                title: this.editingLot ? 'Modifier le lot' : 'Créer le lot',
                message: this.editingLot
                    ? `Confirmer la modification du lot <strong>${this.editingLot.numero_lot}</strong> ?`
                    : `Confirmer la création du lot <strong>${this.form.numero_lot}</strong> ?`,
                type: 'info',
                confirmText: this.editingLot ? 'Modifier' : 'Créer'
            });
            if (!confirmed) return;

            let url = '/api/lots';
            let method = 'POST';

            if (this.editingLot) {
                url = `/api/lots/${this.editingLot.id_lot}`;
                method = 'PUT';
            }

            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.form)
                });

                const data = await response.json();

                if (data.success) {
                    await this.loadLotissements();
                    await this.loadLotsFromDB();
                    this.closeLotForm();
                    window.gsfToast(this.editingLot ? 'Lot modifié avec succès.' : 'Lot créé avec succès.', 'success');
                } else {
                    window.gsfToast('Erreur: ' + (data.message || 'Erreur inconnue'), 'error');
                }
            } catch (error) {
                console.error('Erreur:', error);
                window.gsfToast('Erreur: ' + error.message, 'error');
            }
        },

        async deleteItem(id) {
            const item = this.items.find(i => i.id_lot === id);

            if (item && !item.can_be_deleted) {
                window.gsfToast('Ce lot ne peut pas être supprimé car il est ' + item.statut_label.toLowerCase(), 'warning');
                return;
            }

            const confirmed = await window.gsfConfirm({
                title: 'Supprimer le lot',
                message: `Êtes-vous sûr de vouloir supprimer le lot <strong>${item?.numero_lot || ('#' + id)}</strong> ?<br><span class="text-xs text-red-500 mt-1 block">Cette action est irréversible.</span>`,
                type: 'danger',
                confirmText: 'Supprimer'
            });
            if (!confirmed) return;

            try {
                const response = await fetch(`/api/lots/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    await this.loadLotsFromDB();
                    window.gsfToast('Lot supprimé avec succès', 'success');
                } else {
                    window.gsfToast('Erreur : ' + (data.message || 'Erreur inconnue'), 'error');
                }
            } catch (error) {
                console.error('Erreur:', error);
                window.gsfToast('Une erreur est survenue', 'error');
            }
        },

        exportCSV() {
            const headers = ['ID', 'Numéro Lot', 'Îlot', 'Type', 'Superficie (m²)', 'Prix Comptant', 'Prix Échelonné', 'Statut', 'Lotissement'];
            const rows = this.filteredData.map(item => [
                item.id_lot,
                item.numero_lot,
                item.numero_ilot,
                item.type_lot || '',
                item.superficie || '',
                item.prix_comptant,
                item.prix_echelonne,
                item.statut_label,
                item.lotissement_nom || ''
            ]);

            const csvContent = [headers.join(','), ...rows.map(row => row.map(cell => `"${cell}"`).join(','))].join('\n');
            const blob = new Blob(['\uFEFF' + csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `lots_${new Date().toISOString().split('T')[0]}.csv`;
            link.click();
            URL.revokeObjectURL(link.href);
        },

        exportExcel() {
            const headers = ['ID', 'Numéro Lot', 'Îlot', 'Type', 'Superficie (m²)', 'Prix Comptant', 'Prix Échelonné', 'Statut', 'Lotissement'];
            const rows = this.filteredData.map(item => [
                item.id_lot,
                item.numero_lot,
                item.numero_ilot,
                item.type_lot || '',
                item.superficie || '',
                item.prix_comptant,
                item.prix_echelonne,
                item.statut_label,
                item.lotissement_nom || ''
            ]);

            const excelContent = [headers.join('\t'), ...rows.map(row => row.join('\t'))].join('\n');
            const blob = new Blob(['\uFEFF' + excelContent], { type: 'application/vnd.ms-excel' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `lots_${new Date().toISOString().split('T')[0]}.xls`;
            link.click();
            URL.revokeObjectURL(link.href);
        }
    }));
});
</script>

<style>
[x-cloak] { display: none !important; }
</style>


<div x-data="dataTable" x-init="init()">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
        <!-- Header avec recherche et boutons -->
        <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Tableau Récapitulatif des Lotissements</h2>

                <div class="flex items-center gap-3 flex-wrap">
                    <!-- Filtre par statut -->
                    <select x-model="filtreStatut" @change="currentPage = 1"
                        class="w-48 rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">Tous les statuts</option>
                        <option value="NON_APPROUVE">Non approuvé</option>
                        <option value="APPROUVE">Approuvé</option>
                        <option value="APPROUVE_DIFFUSE">Approuvé et diffusé</option>
                        <option value="ACD_INDIVIDUEL">ACD Individuel</option>
                        <option value="ACD_GLOBAL">ACD Global</option>
                    </select>

                    <!-- Filtre par ville -->
                    <select x-model="filtreVille" @change="currentPage = 1"
                        class="w-40 rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">Toutes les villes</option>
                        <template x-for="ville in villesListe" :key="ville">
                            <option :value="ville" x-text="ville"></option>
                        </template>
                    </select>

                    <!-- Barre de recherche -->
                    <div class="relative w-64">
                        <input type="text" x-model="search" placeholder="Rechercher nom, ville..."
                            class="w-full pl-4 pr-10 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white text-sm">
                        <svg class="absolute right-3 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>

                    <!-- Bouton Nouveau Lotissement -->
                    <button @click="newLotissement" x-show="window.gsfUser.can.lotissements.create"
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
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total lotissements</p>
                <p class="mt-2 text-4xl font-bold text-gray-800 dark:text-white" x-text="stats.total"></p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm text-center">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Approuvés & diffusés</p>
                <p class="mt-2 text-4xl font-bold text-green-600 dark:text-green-400" x-text="stats.approuves_diffuses"></p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm text-center">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Non approuvés</p>
                <p class="mt-2 text-4xl font-bold text-yellow-600 dark:text-yellow-400" x-text="stats.non_approuves"></p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm text-center">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">ACD (Individuel/Global)</p>
                <p class="mt-2 text-4xl font-bold text-purple-600 dark:text-purple-400" x-text="stats.acd"></p>
            </div>
        </div>

        <!-- Overlay formulaire création lotissement -->
        <template x-teleport="body">
        <div x-show="showLotissementForm" x-cloak @keydown.escape.window="closeLotissementForm" class="fixed inset-0 z-[100000] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div @click.stop class="relative w-full max-w-3xl max-h-[90vh] overflow-y-auto rounded-2xl bg-white shadow-2xl dark:bg-gray-900">

                <!-- En-tête -->
                <div class="sticky top-0 flex items-center justify-between border-b border-gray-200 bg-white px-6 py-4 dark:border-gray-700 dark:bg-gray-900">
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white" x-text="editingLotissement ? 'Modifier le lotissement' : 'Nouveau lotissement'"></h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400" x-text="editingLotissement ? 'Modifiez les informations du lotissement' : 'Ajoutez un lotissement avec un formulaire clair et rapide'"></p>
                    </div>
                    <button type="button" @click="closeLotissementForm"
                        class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Formulaire -->
                <form @submit.prevent="saveLotissement" class="p-6">
                    <!-- Messages d'erreur -->
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

                    <!-- Grille des champs -->
                    <div class="grid gap-5 sm:grid-cols-2">
                        <!-- Nom du lotissement -->
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Nom du lotissement *</label>
                            <input type="text" x-model="form.nom" required
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                placeholder="Lotissement les Palmiers">
                        </div>

                        <!-- Ville -->
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Ville *</label>
                            <select x-model="form.ville" required
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                                <option value="">Sélectionnez une ville</option>
                                <template x-for="ville in villesListe" :key="ville">
                                    <option :value="ville" x-text="ville"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Localisation -->
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Localisation</label>
                            <textarea x-model="form.localisation" rows="2"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                placeholder="Adresse ou localisation géographique du lotissement..."></textarea>
                        </div>

                        <!-- Statut du lotissement -->
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Statut du lotissement</label>
                            <select x-model="form.statut_lotissement"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                                <option value="">Sélectionnez un statut</option>
                                <option value="NON_APPROUVE">Non approuvé</option>
                                <option value="APPROUVE">Approuvé</option>
                                <option value="APPROUVE_DIFFUSE">Approuvé et diffusé</option>
                                <option value="ACD_INDIVIDUEL">ACD Individuel</option>
                                <option value="ACD_GLOBAL">ACD Global</option>
                            </select>
                        </div>

                        <!-- Numéro d'approbation -->
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Numéro d'approbation</label>
                            <input type="text" x-model="form.numero_approbation" maxlength="20"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                placeholder="2024/001">
                        </div>

                        <!-- Date d'approbation -->
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Date d'approbation</label>
                            <input type="date" x-model="form.date_approbation"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        </div>
                    </div>

                    <!-- Boutons -->
                    <div class="mt-6 flex gap-3 border-t border-gray-200 pt-6 dark:border-gray-700">
                        <button type="button" @click="closeLotissementForm"
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">
                            Annuler
                        </button>
                        <button type="submit"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            <span x-text="editingLotissement ? 'Mettre à jour' : 'Ajouter lotissement'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        </template>

        <!-- Overlay formulaire affichage détails lotissement - MODIFIÉ AVEC SECTION FRAIS ACD -->
        <template x-teleport="body">
        <div x-show="showDetailsModal" x-cloak @keydown.escape.window="closeDetailsModal" class="fixed inset-0 z-[100000] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div @click.stop class="relative w-full max-w-3xl max-h-[85vh] overflow-y-auto rounded-xl bg-white shadow-2xl dark:bg-gray-900">

                <div class="sticky top-0 flex items-center justify-between border-b border-gray-200 bg-white px-5 py-3 dark:border-gray-700 dark:bg-gray-900">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Détails du lotissement</h3>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Informations complètes et frais ACD par ville</p>
                    </div>
                    <button type="button" @click="closeDetailsModal"
                        class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="p-5">
                    <!-- SECTION INFORMATIONS GÉNÉRALES -->
                    <div class="mb-6">
                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            INFORMATIONS GÉNÉRALES
                        </h4>
                        <div class="grid gap-4 sm:grid-cols-2 bg-gray-50 dark:bg-gray-800/50 p-4 rounded-lg">
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">ID Lotissement</label>
                                <input type="text" x-model="selectedLotissement?.id_lotissement" readonly disabled
                                    class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-1.5 text-sm text-gray-600 cursor-not-allowed dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400">
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Nom</label>
                                <input type="text" x-model="selectedLotissement?.nom" readonly disabled
                                    class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-1.5 text-sm text-gray-600 cursor-not-allowed dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400">
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Ville</label>
                                <input type="text" x-model="selectedLotissement?.ville" readonly disabled
                                    class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-1.5 text-sm text-gray-600 cursor-not-allowed dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400">
                            </div>

                            <div class="sm:col-span-2">
                                <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Localisation</label>
                                <textarea x-model="selectedLotissement?.localisation" readonly disabled rows="2"
                                    class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-1.5 text-sm text-gray-600 cursor-not-allowed dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400"
                                    placeholder="Non renseignée"></textarea>
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Statut</label>
                                <input type="text" x-model="selectedLotissement?.statut_lotissement_label" readonly disabled
                                    class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-1.5 text-sm text-gray-600 cursor-not-allowed dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400">
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Numéro approbation</label>
                                <input type="text" x-model="selectedLotissement?.numero_approbation" readonly disabled
                                    class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-1.5 text-sm text-gray-600 cursor-not-allowed dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400"
                                    placeholder="Non renseigné">
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Date approbation</label>
                                <input type="text" x-model="selectedLotissement?.date_approbation_formatee" readonly disabled
                                    class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-1.5 text-sm text-gray-600 cursor-not-allowed dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400"
                                    placeholder="Non renseignée">
                            </div>
                        </div>
                    </div>

                    <!-- NOUVELLE SECTION: FRAIS DOCUMENTS ACD PAR VILLE -->
                    <div class="mb-6">
                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            FRAIS DOCUMENTS ACD
                            <span class="text-xs text-gray-500">(Ville: <span x-text="selectedLotissement?.ville" class="font-medium text-blue-600"></span>)</span>
                        </h4>

                        <!-- État de chargement -->
                        <div x-show="loadingFrais" class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-6 text-center">
                            <svg class="w-8 h-8 mx-auto mb-2 text-gray-400 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <p class="text-sm text-gray-500">Chargement des frais...</p>
                        </div>

                        <!-- Aucun frais configuré -->
                        <div x-show="!loadingFrais && fraisDocuments.length === 0 && selectedLotissement?.ville" class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4 text-center">
                            <svg class="w-8 h-8 mx-auto mb-2 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                            <p class="text-sm text-yellow-700 dark:text-yellow-300">Aucun frais de document configuré pour la ville <strong x-text="selectedLotissement?.ville"></strong>.</p>
                            <p class="text-xs text-yellow-600 dark:text-yellow-400 mt-1">Veuillez configurer les frais dans la page d'administration des documents ACD.</p>
                            <button @click="goToDocumentsConfig" class="mt-3 inline-flex items-center gap-1 text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                Configurer les frais
                            </button>
                        </div>

                        <!-- Tableau des frais -->
                        <div x-show="!loadingFrais && fraisDocuments.length > 0" class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-100 dark:bg-gray-800">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Type de document</th>
                                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400">Montant (FCFA)</th>
                                        <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-400">Obligatoire</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                    <template x-for="frais in fraisDocuments" :key="frais.id_frais">
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                            <td class="px-3 py-2">
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900 dark:text-white" x-text="frais.document_type?.nom || 'Document'"></p>
                                                    <p class="text-xs text-gray-500" x-text="frais.document_type?.code"></p>
                                                </div>
                                            </td>
                                            <td class="px-3 py-2 text-right">
                                                <span class="text-sm font-semibold text-gray-900 dark:text-white" x-text="formatPrice(frais.montant)"></span>
                                            </td>
                                            <td class="px-3 py-2 text-center">
                                                <span x-show="frais.document_type?.est_obligatoire" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-200">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>
                                                    Obligatoire
                                                </span>
                                                <span x-show="!frais.document_type?.est_obligatoire" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                    Optionnel
                                                </span>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                        <!-- Résumé des frais -->
                        <div x-show="!loadingFrais && fraisDocuments.length > 0" class="mt-3 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Total des frais ACD pour la ville <strong x-text="selectedLotissement?.ville"></strong> :</span>
                                <span class="font-bold text-blue-700 dark:text-blue-300" x-text="formatPrice(totalFraisMontant)"></span>
                            </div>
                            <div class="flex justify-between items-center text-xs text-gray-500 mt-1">
                                <span>Nombre de documents: <span x-text="fraisDocuments.length"></span></span>
                                <span>Dont obligatoires: <span x-text="fraisObligatoiresCount"></span></span>
                            </div>
                        </div>

                        <!-- Message si pas de ville -->
                        <div x-show="!selectedLotissement?.ville" class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4 text-center">
                            <p class="text-sm text-gray-500">Aucune ville associée à ce lotissement.</p>
                        </div>
                    </div>

                    <!-- Boutons d'action -->
                    <div class="mt-5 flex gap-3 border-t border-gray-200 pt-4 dark:border-gray-700">
                        <button type="button" @click="closeDetailsModal"
                            class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">
                            Fermer
                        </button>
                        <button type="button" @click="editFromDetails"
                            class="rounded-lg bg-blue-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Modifier le lotissement
                        </button>
                        <button type="button" @click="goToDocumentsConfig"
                            class="rounded-lg bg-purple-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            Configurer les frais ACD
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">NOM</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">VILLE</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">STATUT</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">NUMÉRO APPROBATION</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">DATE APPROBATION</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <template x-for="item in paginatedData" :key="item.id_lotissement">
                        <tr @click="selectRow(item.id_lotissement)"
                            :class="selectedRow === item.id_lotissement ? 'bg-gray-200 dark:bg-gray-700 border-l-4 border-blue-500 shadow-sm' : 'hover:bg-gray-50 dark:hover:bg-gray-700/50'"
                            class="cursor-pointer transition-all duration-200">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white" x-text="item.id_lotissement"></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8 rounded-full bg-gradient-to-br from-green-400 to-green-600 flex items-center justify-center">
                                        <span class="text-sm font-medium text-white" x-text="item.initials"></span>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white" x-text="item.nom"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400" x-text="item.ville"></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span x-text="item.statut_lotissement_label"
                                    :class="{
                                        'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300': item.statut_lotissement === 'NON_APPROUVE',
                                        'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-200': item.statut_lotissement === 'APPROUVE',
                                        'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-200': item.statut_lotissement === 'APPROUVE_DIFFUSE',
                                        'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200': item.statut_lotissement === 'ACD_INDIVIDUEL',
                                        'bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-200': item.statut_lotissement === 'ACD_GLOBAL'
                                    }"
                                    class="px-2 py-1 rounded-full text-xs font-semibold"></span>
                            </table>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400" x-text="item.numero_approbation || '-'"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400" x-text="item.date_approbation_formatee || '-'"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button @click.stop="viewDetails(item)"
                                    class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300 mr-3 transition-colors" title="Détails">
                                    <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </button>
                                <button @click.stop="editItem(item)" x-show="window.gsfUser.can.lotissements.update"
                                    class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 mr-3 transition-colors" title="Modifier">
                                    <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>
                                <button @click.stop="deleteItem(item.id_lotissement)" x-show="window.gsfUser.can.lotissements.delete"
                                    class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 transition-colors" title="Supprimer">
                                    <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    </template>

                    <tr x-show="filteredData.length === 0">
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                            <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="text-lg font-medium">Aucun lotissement trouvé</p>
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
        filtreVille: '',
        currentPage: 1,
        itemsPerPage: 10,
        selectedRow: null,
        showLotissementForm: false,
        showDetailsModal: false,
        selectedLotissement: null,
        editingLotissement: null,
        formErrors: [],
        villesListe: [],
        form: {
            nom: '',
            localisation: '',
            ville: '',
            statut_lotissement: '',
            numero_approbation: '',
            date_approbation: ''
        },
        items: [],

        // NOUVELLES VARIABLES POUR LES FRAIS ACD
        fraisDocuments: [],
        loadingFrais: false,

        get stats() {
            const total = this.items.length;
            const approuves_diffuses = this.items.filter(i => i.statut_lotissement === 'APPROUVE_DIFFUSE').length;
            const non_approuves = this.items.filter(i => i.statut_lotissement === 'NON_APPROUVE').length;
            const acd = this.items.filter(i => i.statut_lotissement === 'ACD_INDIVIDUEL' || i.statut_lotissement === 'ACD_GLOBAL').length;
            return { total, approuves_diffuses, non_approuves, acd };
        },

        // NOUVEAU: Calcul du total des frais
        get totalFraisMontant() {
            return this.fraisDocuments.reduce((sum, f) => sum + (parseFloat(f.montant) || 0), 0);
        },

        // NOUVEAU: Comptage des documents obligatoires
        get fraisObligatoiresCount() {
            return this.fraisDocuments.filter(f => f.document_type?.est_obligatoire).length;
        },

        get filteredData() {
            let data = [...this.items];

            if (this.filtreStatut) {
                data = data.filter(i => i.statut_lotissement === this.filtreStatut);
            }

            if (this.filtreVille) {
                data = data.filter(i => i.ville === this.filtreVille);
            }

            if (this.search) {
                const searchLower = this.search.toLowerCase();
                data = data.filter(item =>
                    item.nom.toLowerCase().includes(searchLower) ||
                    item.ville.toLowerCase().includes(searchLower) ||
                    (item.localisation && item.localisation.toLowerCase().includes(searchLower)) ||
                    (item.numero_approbation && item.numero_approbation.toLowerCase().includes(searchLower))
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
                'NON_APPROUVE': 'Non approuvé',
                'APPROUVE': 'Approuvé',
                'APPROUVE_DIFFUSE': 'Approuvé et diffusé',
                'ACD_INDIVIDUEL': 'ACD Individuel',
                'ACD_GLOBAL': 'ACD Global'
            };
            return labels[statut] || statut || '';
        },

        formatDate(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            if (isNaN(date.getTime())) return dateString;
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = date.getFullYear();
            return `${day}/${month}/${year}`;
        },

        generateInitials(name) {
            if (!name) return '?';
            return name
                .split(' ')
                .filter(part => part.length)
                .slice(0, 2)
                .map(part => part[0].toUpperCase())
                .join('');
        },

        formatPrice(p) {
            if (!p && p !== 0) return '0 FCFA';
            const n = parseFloat(p);
            if (isNaN(n) || n === 0) return '0 FCFA';
            return new Intl.NumberFormat('fr-FR').format(n) + ' FCFA';
        },

        // NOUVELLE MÉTHODE: Charger les frais de document pour une ville
        async loadFraisByVille(ville) {
            if (!ville) {
                this.fraisDocuments = [];
                return;
            }

            this.loadingFrais = true;
            try {
                const response = await fetch(`/api/admin/documents/frais?ville=${encodeURIComponent(ville)}`);
                if (response.ok) {
                    const data = await response.json();
                    if (data.success) {
                        this.fraisDocuments = data.frais || [];
                    } else {
                        this.fraisDocuments = [];
                    }
                } else {
                    // Fallback: essayer l'autre endpoint
                    const fallbackResponse = await fetch('/api/admin/documents/frais');
                    if (fallbackResponse.ok) {
                        const allData = await fallbackResponse.json();
                        if (allData.success) {
                            this.fraisDocuments = allData.frais.filter(f => f.ville === ville) || [];
                        } else {
                            this.fraisDocuments = [];
                        }
                    } else {
                        this.fraisDocuments = [];
                    }
                }
            } catch (error) {
                console.error('Erreur chargement frais:', error);
                this.fraisDocuments = [];
            } finally {
                this.loadingFrais = false;
            }
        },

        // NOUVELLE MÉTHODE: Rediriger vers la page de configuration des documents
        goToDocumentsConfig() {
            // Rediriger vers la page admin de configuration des documents
            window.location.href = '/admin/documents-config';
        },

        async init() {
            await this.loadVilles();
            await this.loadLotissementsFromDB();
        },

        async loadVilles() {
            try {
                const response = await fetch('/api/lotissements/villes');
                if (response.ok) {
                    this.villesListe = await response.json();
                } else {
                    this.villesListe = [
                        'Abidjan', 'Bouaké', 'Daloa', 'Yamoussoukro', 'San-Pédro', 'Korhogo', 'Man', 'Divo',
                        'Gagnoa', 'Abobo', 'Anyama', 'Port-Bouët', 'Treichville', 'Cocody', 'Marcory', 'Koumassi',
                        'Adjamé', 'Bingerville', 'Grand-Bassam', 'Soubré', 'Séguéla', 'Issia', 'Duékoué', 'Odienné'
                    ];
                }
            } catch (error) {
                console.error('Erreur chargement villes:', error);
                this.villesListe = [
                    'Abidjan', 'Bouaké', 'Daloa', 'Yamoussoukro', 'San-Pédro', 'Korhogo', 'Man', 'Divo',
                    'Gagnoa', 'Abobo', 'Anyama', 'Port-Bouët', 'Treichville', 'Cocody', 'Marcory', 'Koumassi',
                    'Adjamé', 'Bingerville', 'Grand-Bassam', 'Soubré', 'Séguéla', 'Issia', 'Duékoué', 'Odienné'
                ];
            }
        },

        async loadLotissementsFromDB() {
            try {
                const response = await fetch('/api/lotissements');
                if (!response.ok) throw new Error('HTTP ' + response.status);
                const data = await response.json();

                if (Array.isArray(data) && data.length > 0) {
                    this.items = data.map(lotissement => ({
                        id_lotissement: lotissement.id_lotissement,
                        nom: lotissement.nom,
                        localisation: lotissement.localisation,
                        ville: lotissement.ville,
                        statut_lotissement: lotissement.statut_lotissement,
                        statut_lotissement_label: this.getStatutLabel(lotissement.statut_lotissement),
                        numero_approbation: lotissement.numero_approbation,
                        date_approbation: lotissement.date_approbation,
                        date_approbation_formatee: this.formatDate(lotissement.date_approbation),
                        initials: this.generateInitials(lotissement.nom)
                    }));
                } else {
                    this.items = [];
                }
                this.currentPage = 1;
            } catch (error) {
                console.error('Erreur chargement lotissements:', error);
                this.items = [];
            }
        },

        newLotissement() {
            this.editingLotissement = null;
            this.form = {
                nom: '',
                localisation: '',
                ville: '',
                statut_lotissement: '',
                numero_approbation: '',
                date_approbation: ''
            };
            this.formErrors = [];
            this.showLotissementForm = true;
        },

        editItem(item) {
            this.editingLotissement = item;
            this.form = {
                nom: item.nom,
                localisation: item.localisation || '',
                ville: item.ville,
                statut_lotissement: item.statut_lotissement || '',
                numero_approbation: item.numero_approbation || '',
                date_approbation: item.date_approbation || ''
            };
            this.formErrors = [];
            this.showLotissementForm = true;
        },

        closeLotissementForm() {
            this.showLotissementForm = false;
            this.editingLotissement = null;
            this.formErrors = [];
        },

        closeDetailsModal() {
            this.showDetailsModal = false;
            this.selectedLotissement = null;
            this.fraisDocuments = [];
        },

        async viewDetails(item) {
            this.selectedLotissement = item;
            this.showDetailsModal = true;
            // Charger les frais pour la ville du lotissement
            if (item.ville) {
                await this.loadFraisByVille(item.ville);
            } else {
                this.fraisDocuments = [];
            }
        },

        editFromDetails() {
            const item = this.selectedLotissement;
            this.closeDetailsModal();
            this.editItem(item);
        },

        async saveLotissement() {
            this.formErrors = [];

            if (!this.form.nom.trim()) {
                this.formErrors.push('Le nom du lotissement est requis.');
            }
            if (!this.form.ville) {
                this.formErrors.push('La ville est requise.');
            }

            if (this.formErrors.length) return;

            const confirmed = await window.gsfConfirm({
                title: this.editingLotissement ? 'Modifier le lotissement' : 'Créer le lotissement',
                message: this.editingLotissement
                    ? `Confirmer la modification de <strong>${this.editingLotissement.nom}</strong> ?`
                    : `Confirmer la création du lotissement <strong>${this.form.nom}</strong> ?`,
                type: 'info',
                confirmText: this.editingLotissement ? 'Modifier' : 'Créer'
            });
            if (!confirmed) return;

            let url = '/api/lotissements';
            let method = 'POST';

            if (this.editingLotissement) {
                url = `/api/lotissements/${this.editingLotissement.id_lotissement}`;
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
                    await this.loadLotissementsFromDB();
                    this.closeLotissementForm();
                    window.gsfToast(this.editingLotissement ? 'Lotissement modifié avec succès.' : 'Lotissement créé avec succès.', 'success');
                } else {
                    window.gsfToast('Erreur: ' + (data.message || 'Erreur inconnue'), 'error');
                }
            } catch (error) {
                console.error('Erreur:', error);
                window.gsfToast('Erreur: ' + error.message, 'error');
            }
        },

        async deleteItem(id) {
            const item = this.items.find(l => l.id_lotissement === id);
            const confirmed = await window.gsfConfirm({
                title: 'Supprimer le lotissement',
                message: `Êtes-vous sûr de vouloir supprimer <strong>${item?.nom || 'ce lotissement'}</strong> ?<br><span class="text-xs text-red-500 mt-1 block">Cette action est irréversible.</span>`,
                type: 'danger',
                confirmText: 'Supprimer'
            });
            if (!confirmed) return;

            try {
                const response = await fetch(`/api/lotissements/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    await this.loadLotissementsFromDB();
                    window.gsfToast('Lotissement supprimé avec succès', 'success');
                } else {
                    window.gsfToast('Erreur lors de la suppression', 'error');
                }
            } catch (error) {
                console.error('Erreur:', error);
                window.gsfToast('Une erreur est survenue', 'error');
            }
        },

        exportCSV() {
            const headers = ['ID', 'Nom', 'Ville', 'Localisation', 'Statut', 'Numéro Approbation', 'Date Approbation'];
            const rows = this.filteredData.map(item => [
                item.id_lotissement,
                item.nom,
                item.ville,
                item.localisation || '',
                item.statut_lotissement_label || '',
                item.numero_approbation || '',
                item.date_approbation_formatee || ''
            ]);

            const csvContent = [
                headers.join(','),
                ...rows.map(row => row.map(cell => `"${cell}"`).join(','))
            ].join('\n');

            const blob = new Blob(['\uFEFF' + csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `lotissements_${new Date().toISOString().split('T')[0]}.csv`;
            link.click();
            URL.revokeObjectURL(link.href);
        },

        exportExcel() {
            const headers = ['ID', 'Nom', 'Ville', 'Localisation', 'Statut', 'Numéro Approbation', 'Date Approbation'];
            const rows = this.filteredData.map(item => [
                item.id_lotissement,
                item.nom,
                item.ville,
                item.localisation || '',
                item.statut_lotissement_label || '',
                item.numero_approbation || '',
                item.date_approbation_formatee || ''
            ]);

            const excelContent = [
                headers.join('\t'),
                ...rows.map(row => row.join('\t'))
            ].join('\n');

            const blob = new Blob(['\uFEFF' + excelContent], { type: 'application/vnd.ms-excel' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `lotissements_${new Date().toISOString().split('T')[0]}.xls`;
            link.click();
            URL.revokeObjectURL(link.href);
        }
    }));
});
</script>

<style>
[x-cloak] { display: none !important; }
</style>


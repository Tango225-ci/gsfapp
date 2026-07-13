<div x-data="dataTable" x-init="init()">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
        <!-- Header avec recherche et boutons -->
        <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Configuration des Documents ACD</h2>

                <div class="flex items-center gap-3 flex-wrap">
                    <!-- Filtre par statut -->
                    <select x-model="filtreStatut" @change="currentPage = 1"
                        class="w-48 rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">Tous les statuts</option>
                        <option value="1">Actif</option>
                        <option value="0">Inactif</option>
                    </select>

                    <!-- Filtre par ville -->
                    <select x-model="filtreVille" @change="currentPage = 1; chargerFrais()"
                        class="w-40 rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">Toutes les villes</option>
                        <template x-for="ville in villesListe" :key="ville">
                            <option :value="ville" x-text="ville"></option>
                        </template>
                    </select>

                    <!-- Barre de recherche -->
                    <div class="relative w-64">
                        <input type="text" x-model="search" placeholder="Rechercher type, code..."
                            class="w-full pl-4 pr-10 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white text-sm">
                        <svg class="absolute right-3 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>

                    <!-- Bouton Nouveau Type -->
                    <button @click="openTypeModal()"
                        class="flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Nouveau type
                    </button>

                    <!-- Bouton Nouveau Tarif -->
                    <button @click="openFraisModal()"
                        class="flex items-center gap-2 bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-green-700 transition-colors duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Nouveau tarif
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
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Types de documents</p>
                <p class="mt-2 text-4xl font-bold text-gray-800 dark:text-white" x-text="stats.total_types"></p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm text-center">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Documents actifs</p>
                <p class="mt-2 text-4xl font-bold text-green-600 dark:text-green-400" x-text="stats.types_actifs"></p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm text-center">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Villes configurées</p>
                <p class="mt-2 text-4xl font-bold text-purple-600 dark:text-purple-400" x-text="stats.villes_count"></p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm text-center">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Tarifs configurés</p>
                <p class="mt-2 text-4xl font-bold text-orange-600 dark:text-orange-400" x-text="stats.tarifs_count"></p>
            </div>
        </div>

        <!-- Overlay formulaire type de document -->
        <template x-teleport="body">
        <div x-show="showTypeModal" x-cloak @keydown.escape.window="closeTypeModal" class="fixed inset-0 z-[100000] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div @click.stop class="relative w-full max-w-lg rounded-2xl bg-white shadow-2xl dark:bg-gray-900">
                <div class="sticky top-0 flex items-center justify-between border-b border-gray-200 bg-white px-6 py-4 dark:border-gray-700 dark:bg-gray-900">
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white" x-text="editingType ? 'Modifier le type' : 'Nouveau type de document'"></h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400" x-text="editingType ? 'Modifiez les informations du type' : 'Ajoutez un type de document'"></p>
                    </div>
                    <button type="button" @click="closeTypeModal" class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form @submit.prevent="saveType" class="p-6">
                    <div x-show="typeErrors.length" class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800 dark:border-red-800 dark:bg-red-900/20 dark:text-red-200">
                        <p class="font-semibold">Veuillez corriger les erreurs :</p>
                        <ul class="mt-2 list-inside list-disc">
                            <template x-for="error in typeErrors" :key="error"><li x-text="error"></li></template>
                        </ul>
                    </div>
                    <div class="space-y-4">
                        <div><label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Nom *</label><input type="text" x-model="typeForm.nom" required class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"></div>
                        <div><label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Code *</label><input type="text" x-model="typeForm.code" required class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white" placeholder="DOSSIER_TECH"></div>
                        <div><label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label><textarea x-model="typeForm.description" rows="2" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"></textarea></div>
                        <div class="grid grid-cols-2 gap-4">
                            <div><label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Ordre d'affichage</label><input type="number" x-model="typeForm.ordre_affichage" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"></div>
                            <div><label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Statut</label><select x-model="typeForm.actif" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"><option :value="1">Actif</option><option :value="0">Inactif</option></select></div>
                        </div>
                        <div><label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" x-model="typeForm.est_obligatoire" class="w-4 h-4 text-blue-600"><span class="text-sm text-gray-700 dark:text-gray-300">Document obligatoire</span></label></div>
                    </div>
                    <div class="mt-6 flex gap-3 border-t border-gray-200 pt-6 dark:border-gray-700">
                        <button type="button" @click="closeTypeModal" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium hover:bg-gray-50">Annuler</button>
                        <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"><span x-text="editingType ? 'Mettre à jour' : 'Ajouter'"></span></button>
                    </div>
                </form>
            </div>
        </div>
        </template>

        <!-- Overlay formulaire tarif -->
        <template x-teleport="body">
        <div x-show="showFraisModal" x-cloak @keydown.escape.window="closeFraisModal" class="fixed inset-0 z-[100000] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div @click.stop class="relative w-full max-w-md rounded-2xl bg-white shadow-2xl dark:bg-gray-900">
                <div class="sticky top-0 flex items-center justify-between border-b border-gray-200 bg-white px-6 py-4 dark:border-gray-700 dark:bg-gray-900">
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white" x-text="editingFrais ? 'Modifier le tarif' : 'Nouveau tarif'"></h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400" x-text="editingFrais ? 'Modifiez les informations du tarif' : 'Ajoutez un tarif pour une ville'"></p>
                    </div>
                    <button type="button" @click="closeFraisModal" class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form @submit.prevent="saveFrais" class="p-6">
                    <div x-show="fraisErrors.length" class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800 dark:border-red-800 dark:bg-red-900/20 dark:text-red-200">
                        <p class="font-semibold">Veuillez corriger les erreurs :</p>
                        <ul class="mt-2 list-inside list-disc">
                            <template x-for="error in fraisErrors" :key="error"><li x-text="error"></li></template>
                        </ul>
                    </div>
                    <div class="space-y-4">
                        <div><label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Ville *</label><select x-model="fraisForm.ville" required class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"><option value="">Sélectionnez une ville</option><template x-for="ville in villesListe" :key="ville"><option :value="ville" x-text="ville"></option></template></select></div>
                        <div><label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Type de document *</label><select x-model="fraisForm.id_document_type" required class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"><option value="">Sélectionnez un type</option><template x-for="type in typesDocuments" :key="type.id_document_type"><option :value="type.id_document_type" x-text="type.nom"></option></template></select></div>
                        <div><label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Montant (FCFA) *</label><input type="number" x-model="fraisForm.montant" required min="0" step="1000" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"></div>
                    </div>
                    <div class="mt-6 flex gap-3 border-t border-gray-200 pt-6 dark:border-gray-700">
                        <button type="button" @click="closeFraisModal" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium hover:bg-gray-50">Annuler</button>
                        <button type="submit" class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700"><span x-text="editingFrais ? 'Mettre à jour' : 'Ajouter'"></span></button>
                    </div>
                </form>
            </div>
        </div>
        </template>

        <!-- Overlay détails tarifs par ville -->
        <template x-teleport="body">
        <div x-show="showDetailsModal" x-cloak @keydown.escape.window="closeDetailsModal" class="fixed inset-0 z-[100000] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div @click.stop class="relative w-full max-w-2xl max-h-[85vh] overflow-y-auto rounded-xl bg-white shadow-2xl dark:bg-gray-900">
                <div class="sticky top-0 flex items-center justify-between border-b border-gray-200 bg-white px-5 py-3 dark:border-gray-700 dark:bg-gray-900">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Tarifs par ville</h3>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Détail des frais ACD pour la ville sélectionnée</p>
                    </div>
                    <button type="button" @click="closeDetailsModal" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-5">
                    <div class="mb-4 flex justify-between items-center">
                        <h4 class="text-md font-semibold text-gray-700 dark:text-gray-300">Ville: <span class="text-blue-600" x-text="selectedVille"></span></h4>
                        <button @click="openFraisModalFromDetails" class="flex items-center gap-1 bg-green-600 text-white px-3 py-1.5 rounded-lg text-sm hover:bg-green-700">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Ajouter un tarif
                        </button>
                    </div>

                    <div x-show="loadingFrais" class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-6 text-center">
                        <svg class="w-8 h-8 mx-auto mb-2 text-gray-400 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <p class="text-sm text-gray-500">Chargement des tarifs...</p>
                    </div>

                    <div x-show="!loadingFrais && fraisDetailsListe.length === 0" class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4 text-center">
                        <svg class="w-8 h-8 mx-auto mb-2 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>
                        <p class="text-sm text-yellow-700 dark:text-yellow-300">Aucun tarif configuré pour la ville <strong x-text="selectedVille"></strong>.</p>
                    </div>

                    <div x-show="!loadingFrais && fraisDetailsListe.length > 0" class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-100 dark:bg-gray-800">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Type de document</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400">Montant (FCFA)</th>
                                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-400">Obligatoire</th>
                                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-400">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                <template x-for="frais in fraisDetailsListe" :key="frais.id_frais">
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                        <td class="px-3 py-2"><p class="text-sm font-medium text-gray-500 dark:text-gray-400" x-text="frais.document_type?.nom || 'Document'"></p><p class="text-xs text-gray-400 dark:text-gray-500" x-text="frais.document_type?.code"></p></td>
                                        <td class="px-3 py-2 text-right"><span class="text-sm font-semibold text-gray-600 dark:text-gray-300" x-text="formatPrice(frais.montant)"></span></td>
                                        <td class="px-3 py-2 text-center"><span x-show="frais.document_type?.est_obligatoire" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-200">Obligatoire</span><span x-show="!frais.document_type?.est_obligatoire" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">Optionnel</span></td>
                                        <td class="px-3 py-2 text-center"><button @click="editFraisFromDetails(frais)" class="text-blue-600 hover:text-blue-900 mr-2" title="Modifier"><svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg></button><button @click="deleteFraisFromDetails(frais)" class="text-red-600 hover:text-red-900" title="Supprimer"><svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <div x-show="!loadingFrais && fraisDetailsListe.length > 0" class="mt-3 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                        <div class="flex justify-between items-center text-sm"><span class="text-gray-600 dark:text-gray-400">Total des frais ACD pour la ville <strong x-text="selectedVille"></strong> :</span><span class="font-bold text-blue-700 dark:text-blue-300" x-text="formatPrice(totalFraisMontantDetails)"></span></div>
                        <div class="flex justify-between items-center text-xs text-gray-500 mt-1"><span>Nombre de documents: <span x-text="fraisDetailsListe.length"></span></span><span>Dont obligatoires: <span x-text="fraisObligatoiresCountDetails"></span></span></div>
                    </div>

                    <div class="mt-5 flex gap-3 border-t border-gray-200 pt-4 dark:border-gray-700"><button type="button" @click="closeDetailsModal" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm font-medium hover:bg-gray-50">Fermer</button><button type="button" @click="openFraisModalFromDetails" class="rounded-lg bg-green-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-green-700">Ajouter un tarif</button></div>
                </div>
            </div>
        </div>
        </template>

        <!-- Tableau des types de documents -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">NOM</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">CODE</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">DESCRIPTION</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">ORDRE</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">OBLIGATOIRE</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">STATUT</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <template x-for="item in paginatedData" :key="item.id_document_type">
                        <tr @click="selectRow(item.id_document_type)" :class="selectedRow === item.id_document_type ? 'bg-gray-200 dark:bg-gray-700 border-l-4 border-blue-500 shadow-sm' : 'hover:bg-gray-50 dark:hover:bg-gray-700/50'" class="cursor-pointer transition-all duration-200">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8 rounded-full bg-gradient-to-br from-gray-400 to-gray-600 flex items-center justify-center">
                                        <span class="text-sm font-medium text-white" x-text="item.nom.charAt(0).toUpperCase()"></span>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400" x-text="item.nom"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap"><span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-xs font-mono text-gray-500 dark:text-gray-400" x-text="item.code"></span></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400 dark:text-gray-500" x-text="item.description || '-'"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-400 dark:text-gray-500" x-text="item.ordre_affichage"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span x-show="item.est_obligatoire" class="px-2 py-1 bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-200 rounded-full text-xs font-semibold">Obligatoire</span>
                                <span x-show="!item.est_obligatoire" class="px-2 py-1 bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400 rounded-full text-xs font-semibold">Optionnel</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span x-show="item.actif" class="px-2 py-1 bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-200 rounded-full text-xs font-semibold">Actif</span>
                                <span x-show="!item.actif" class="px-2 py-1 bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400 rounded-full text-xs font-semibold">Inactif</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <button @click.stop="editType(item)" class="text-blue-600 hover:text-blue-900 mr-2 transition-colors" title="Modifier"><svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg></button>
                                <button @click.stop="deleteType(item)" class="text-red-600 hover:text-red-900 transition-colors" title="Supprimer"><svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="filteredData.length === 0"><td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400"><svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg><p class="text-lg font-medium">Aucun type de document trouvé</p><p class="text-sm mt-1">Essayez de modifier vos filtres</p></td></tr>
                </tbody>
            </table>
        </div>

        <!-- Tableau des tarifs groupés par ville AVEC ACCORDÉON -->
        <div class="mt-8" x-show="afficherTableauTarifs">
            <div class="px-6 py-3 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 border-t border-b border-gray-200 dark:border-gray-700">
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h3 class="text-md font-semibold text-gray-700 dark:text-gray-300">Tarifs ACD par ville</h3>
                    </div>
                    <div class="text-sm text-gray-500">
                        <span x-text="filteredFrais.length"></span> tarif(s) configuré(s)
                    </div>
                </div>
            </div>

            <!-- Affichage groupé par ville avec accordéon -->
            <div x-show="filteredFrais.length === 0" class="p-8 text-center bg-gray-50 dark:bg-gray-800/50">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-lg font-medium text-gray-500">Aucun tarif configuré</p>
                <p class="text-sm text-gray-400 mt-1">Cliquez sur "Nouveau tarif" pour ajouter des frais ACD par ville</p>
            </div>

            <div x-show="filteredFrais.length > 0">
                <template x-for="(group, ville) in groupedFrais" :key="ville">
                    <div class="mb-3 last:mb-0 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                        <!-- En-tête de ville (clic pour dérouler/rouler) -->
                        <div @click="toggleVille(ville)" 
                             class="flex items-center justify-between px-6 py-3 bg-gray-100 dark:bg-gray-700/50 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors duration-200">
                            <div class="flex items-center gap-3">
                                <!-- Flèche d'accordéon -->
                                <svg class="w-5 h-5 text-gray-500 transition-transform duration-200" :class="{'rotate-90': villesOuvertes.includes(ville)}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                                <div class="flex-shrink-0 h-9 w-9 rounded-full bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center shadow-sm">
                                    <span class="text-sm font-bold text-white" x-text="ville.charAt(0).toUpperCase()"></span>
                                </div>
                                <div>
                                    <h4 class="text-base font-bold text-gray-800 dark:text-white" x-text="ville"></h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        <span x-text="group.length"></span> document(s) - 
                                        Total: <span class="font-semibold text-green-600 dark:text-green-400" x-text="formatPrice(group.reduce((sum, f) => sum + (parseFloat(f.montant) || 0), 0))"></span>
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button @click.stop="viewFraisByVille(ville)" 
                                    class="flex items-center gap-1 text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 transition-colors px-2 py-1 rounded hover:bg-blue-50 dark:hover:bg-blue-900/20">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    Détails
                                </button>
                            </div>
                        </div>

                        <!-- Corps du tableau (visible seulement si la ville est ouverte) -->
                        <div x-show="villesOuvertes.includes(ville)" 
                             x-collapse
                             class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-800">
                                    <tr>
                                        <th class="px-6 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type de document</th>
                                        <th class="px-6 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Montant (FCFA)</th>
                                        <th class="px-6 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Obligatoire</th>
                                        <th class="px-6 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                    <template x-for="frais in group" :key="frais.id_frais">
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors duration-200">
                                            <td class="px-6 py-3 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900 dark:text-white" x-text="frais.document_type?.nom || '-'"></div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400" x-text="frais.document_type?.code"></div>
                                            </td>
                                            <td class="px-6 py-3 whitespace-nowrap text-right">
                                                <div class="text-sm font-semibold text-gray-700 dark:text-gray-300" x-text="formatPrice(frais.montant)"></div>
                                            </td>
                                            <td class="px-6 py-3 whitespace-nowrap text-center">
                                                <span x-show="frais.document_type?.est_obligatoire" class="px-2 py-1 bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-200 rounded-full text-xs font-semibold">Obligatoire</span>
                                                <span x-show="!frais.document_type?.est_obligatoire" class="px-2 py-1 bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400 rounded-full text-xs font-semibold">Optionnel</span>
                                            </td>
                                            <td class="px-6 py-3 whitespace-nowrap text-center text-sm font-medium">
                                                <button @click.stop="editFrais(frais)" class="text-blue-600 hover:text-blue-900 mr-2 transition-colors" title="Modifier">
                                                    <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                    </svg>
                                                </button>
                                                <button @click.stop="deleteFrais(frais)" class="text-red-600 hover:text-red-900 transition-colors" title="Supprimer">
                                                    <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Pagination pour les types de documents -->
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                <div class="text-sm text-gray-700 dark:text-gray-300">Affichage de <span class="font-medium" x-text="startIndex"></span> à <span class="font-medium" x-text="endIndex"></span> sur <span class="font-medium" x-text="filteredData.length"></span> résultats</div>
                <div class="flex gap-2">
                    <button @click="prevPage" :disabled="currentPage === 1" :class="currentPage === 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100 dark:hover:bg-gray-700'" class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded-md text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 transition-colors"><svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>Précédent</button>
                    <span class="px-3 py-1 text-sm text-gray-700 dark:text-gray-300">Page <span class="font-medium" x-text="currentPage"></span> / <span class="font-medium" x-text="totalPages"></span></span>
                    <button @click="nextPage" :disabled="currentPage === totalPages" :class="currentPage === totalPages ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100 dark:hover:bg-gray-700'" class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded-md text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 transition-colors">Suivant<svg class="w-4 h-4 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg></button>
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
        showTypeModal: false,
        showFraisModal: false,
        showDetailsModal: false,
        editingType: null,
        editingFrais: null,
        selectedVille: '',
        typeErrors: [],
        fraisErrors: [],
        villesListe: [],
        typesDocuments: [],
        fraisListe: [],
        fraisDetailsListe: [],
        loadingFrais: false,
        afficherTableauTarifs: true,
        villesOuvertes: [], // Liste des villes dont l'accordéon est ouvert

        typeForm: { nom: '', code: '', description: '', ordre_affichage: 0, est_obligatoire: true, actif: 1 },
        fraisForm: { ville: '', id_document_type: '', montant: 0 },

        get stats() {
            const total_types = this.typesDocuments.length;
            const types_actifs = this.typesDocuments.filter(t => t.actif == 1).length;
            const villes_count = [...new Set(this.fraisListe.map(f => f.ville))].length;
            const tarifs_count = this.fraisListe.length;
            return { total_types, types_actifs, villes_count, tarifs_count };
        },

        get filteredData() {
            let data = [...this.typesDocuments];
            if (this.filtreStatut !== '') data = data.filter(i => i.actif == this.filtreStatut);
            if (this.search) { const s = this.search.toLowerCase(); data = data.filter(i => i.nom.toLowerCase().includes(s) || i.code.toLowerCase().includes(s)); }
            return data;
        },

        get filteredFrais() {
            let data = [...this.fraisListe];
            if (this.filtreVille) data = data.filter(f => f.ville === this.filtreVille);
            return data;
        },

        // Grouper les frais par ville
        get groupedFrais() {
            const groups = {};
            this.filteredFrais.forEach(frais => {
                if (!groups[frais.ville]) groups[frais.ville] = [];
                groups[frais.ville].push(frais);
            });
            // Trier les villes par ordre alphabétique
            const sortedGroups = {};
            Object.keys(groups).sort().forEach(ville => {
                sortedGroups[ville] = groups[ville];
            });
            return sortedGroups;
        },

        get totalPages() { return Math.ceil(this.filteredData.length / this.itemsPerPage) || 1; },
        get paginatedData() { const start = (this.currentPage - 1) * this.itemsPerPage; return this.filteredData.slice(start, start + this.itemsPerPage); },
        get startIndex() { return this.filteredData.length === 0 ? 0 : (this.currentPage - 1) * this.itemsPerPage + 1; },
        get endIndex() { return Math.min(this.currentPage * this.itemsPerPage, this.filteredData.length); },
        get totalFraisMontantDetails() { return this.fraisDetailsListe.reduce((s, f) => s + (parseFloat(f.montant) || 0), 0); },
        get fraisObligatoiresCountDetails() { return this.fraisDetailsListe.filter(f => f.document_type?.est_obligatoire).length; },

        // Fonction pour ouvrir/fermer l'accordéon d'une ville
        toggleVille(ville) {
            const index = this.villesOuvertes.indexOf(ville);
            if (index === -1) {
                this.villesOuvertes.push(ville);
            } else {
                this.villesOuvertes.splice(index, 1);
            }
        },

        nextPage() { if (this.currentPage < this.totalPages) { this.currentPage++; this.selectedRow = null; } },
        prevPage() { if (this.currentPage > 1) { this.currentPage--; this.selectedRow = null; } },
        selectRow(id) { this.selectedRow = id; },

        formatPrice(p) { if (!p && p !== 0) return '0 FCFA'; const n = parseFloat(p); return isNaN(n) || n === 0 ? '0 FCFA' : new Intl.NumberFormat('fr-FR').format(n) + ' FCFA'; },

        async init() {
            await this.loadVilles();
            await this.loadTypes();
            await this.loadFrais();
            // Ouvrir automatiquement la première ville par défaut (optionnel)
            // this.$nextTick(() => {
            //     const premieresVilles = Object.keys(this.groupedFrais);
            //     if (premieresVilles.length > 0 && this.villesOuvertes.length === 0) {
            //         this.villesOuvertes = [premieresVilles[0]];
            //     }
            // });
        },

        async loadVilles() {
            try {
                const response = await fetch('/api/lotissements/villes');
                if (response.ok) this.villesListe = await response.json();
                else this.villesListe = ['Abidjan', 'Bouaké', 'Daloa', 'Yamoussoukro', 'San-Pédro', 'Korhogo', 'Man', 'Divo', 'Gagnoa', 'Abobo', 'Anyama'];
            } catch (error) { console.error('Erreur chargement villes:', error); this.villesListe = ['Abidjan', 'Bouaké', 'Daloa', 'Yamoussoukro', 'San-Pédro']; }
        },

        async loadTypes() {
            try {
                const response = await fetch('/api/documents/types');
                if (response.ok) { const data = await response.json(); if (data.success) this.typesDocuments = data.document_types; }
            } catch (error) { console.error('Erreur chargement types:', error); }
        },

        async loadFrais() {
            try {
                let url = '/api/documents/frais';
                if (this.filtreVille) url += `?ville=${encodeURIComponent(this.filtreVille)}`;
                const response = await fetch(url);
                if (response.ok) { const data = await response.json(); if (data.success) this.fraisListe = data.frais; }
            } catch (error) { console.error('Erreur chargement frais:', error); }
        },

        async loadFraisByVille(ville) {
            this.loadingFrais = true;
            try {
                const response = await fetch(`/api/documents/frais?ville=${encodeURIComponent(ville)}`);
                if (response.ok) { const data = await response.json(); if (data.success) this.fraisDetailsListe = data.frais; else this.fraisDetailsListe = []; }
                else this.fraisDetailsListe = [];
            } catch (error) { console.error('Erreur:', error); this.fraisDetailsListe = []; }
            finally { this.loadingFrais = false; }
        },

        viewFraisByVille(ville) { this.selectedVille = ville; this.loadFraisByVille(ville); this.showDetailsModal = true; },

        openTypeModal() { this.editingType = null; this.typeForm = { nom: '', code: '', description: '', ordre_affichage: 0, est_obligatoire: true, actif: 1 }; this.typeErrors = []; this.showTypeModal = true; },
        editType(type) { this.editingType = type; this.typeForm = { ...type, actif: type.actif ? 1 : 0 }; this.typeErrors = []; this.showTypeModal = true; },
        closeTypeModal() { this.showTypeModal = false; this.editingType = null; },

        async saveType() {
            this.typeErrors = [];
            if (!this.typeForm.nom.trim()) this.typeErrors.push('Le nom est requis.');
            if (!this.typeForm.code.trim()) this.typeErrors.push('Le code est requis.');
            if (this.typeErrors.length) return;

            const url = this.editingType ? `/api/documents/types/${this.editingType.id_document_type}` : '/api/documents/types';
            const method = this.editingType ? 'PUT' : 'POST';
            try {
                const response = await fetch(url, { method, headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '' }, body: JSON.stringify(this.typeForm) });
                const data = await response.json();
                if (data.success) { await this.loadTypes(); this.closeTypeModal(); window.gsfToast(this.editingType ? 'Type modifié avec succès' : 'Type créé avec succès', 'success'); }
                else window.gsfToast(data.message || 'Erreur', 'error');
            } catch (error) { console.error('Erreur:', error); window.gsfToast('Erreur', 'error'); }
        },

        async deleteType(type) {
            const confirmed = await window.gsfConfirm({
                title: 'Supprimer le type de document',
                message: `Êtes-vous sûr de vouloir supprimer le type <strong>${type.nom}</strong> ?<br><span class="text-xs text-red-500 mt-1 block">Cette action est irréversible.</span>`,
                type: 'danger',
                confirmText: 'Supprimer'
            });
            if (!confirmed) return;
            try {
                const response = await fetch(`/api/documents/types/${type.id_document_type}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '' } });
                const data = await response.json();
                if (data.success) { await this.loadTypes(); window.gsfToast('Type supprimé avec succès', 'success'); }
                else window.gsfToast(data.message || 'Erreur', 'error');
            } catch (error) { console.error('Erreur:', error); window.gsfToast('Erreur', 'error'); }
        },

        openFraisModal() { this.editingFrais = null; this.fraisForm = { ville: '', id_document_type: '', montant: 0 }; this.fraisErrors = []; this.showFraisModal = true; },
        editFrais(frais) { this.editingFrais = frais; this.fraisForm = { ville: frais.ville, id_document_type: frais.id_document_type, montant: frais.montant }; this.fraisErrors = []; this.showFraisModal = true; },
        closeFraisModal() { this.showFraisModal = false; this.editingFrais = null; },
        openFraisModalFromDetails() { this.closeDetailsModal(); this.openFraisModal(); },
        editFraisFromDetails(frais) { this.closeDetailsModal(); this.editFrais(frais); },

        async saveFrais() {
            this.fraisErrors = [];
            if (!this.fraisForm.ville) this.fraisErrors.push('La ville est requise.');
            if (!this.fraisForm.id_document_type) this.fraisErrors.push('Le type de document est requis.');
            if (!this.fraisForm.montant || this.fraisForm.montant <= 0) this.fraisErrors.push('Le montant doit être supérieur à 0.');
            if (this.fraisErrors.length) return;

            try {
                const response = await fetch('/api/documents/frais', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '' }, body: JSON.stringify(this.fraisForm) });
                const data = await response.json();
                if (data.success) { await this.loadFrais(); await this.loadVilles(); this.closeFraisModal(); window.gsfToast(data.message || 'Tarif enregistré avec succès', 'success'); }
                else window.gsfToast(data.message || 'Erreur', 'error');
            } catch (error) { console.error('Erreur:', error); window.gsfToast('Erreur', 'error'); }
        },

        async deleteFrais(frais) {
            const confirmed = await window.gsfConfirm({
                title: 'Supprimer le tarif',
                message: `Supprimer le tarif pour <strong>${frais.ville}</strong> — <strong>${frais.document_type?.nom || ''}</strong> ?<br><span class="text-xs text-red-500 mt-1 block">Cette action est irréversible.</span>`,
                type: 'danger',
                confirmText: 'Supprimer'
            });
            if (!confirmed) return;
            try {
                const response = await fetch(`/api/documents/frais/${frais.id_frais}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '' } });
                const data = await response.json();
                if (data.success) { await this.loadFrais(); window.gsfToast('Tarif supprimé avec succès', 'success'); }
                else window.gsfToast(data.message || 'Erreur', 'error');
            } catch (error) { console.error('Erreur:', error); window.gsfToast('Erreur', 'error'); }
        },

        async deleteFraisFromDetails(frais) { await this.deleteFrais(frais); if (this.selectedVille) await this.loadFraisByVille(this.selectedVille); },
        closeDetailsModal() { this.showDetailsModal = false; this.selectedVille = ''; this.fraisDetailsListe = []; },

        exportCSV() {
            const headers = ['Nom', 'Code', 'Description', 'Ordre', 'Obligatoire', 'Actif'];
            const rows = this.filteredData.map(t => [t.nom, t.code, t.description || '', t.ordre_affichage, t.est_obligatoire ? 'Oui' : 'Non', t.actif ? 'Oui' : 'Non']);
            const csv = [headers.join(','), ...rows.map(r => r.map(c => `"${String(c).replace(/"/g, '""')}"`).join(','))].join('\n');
            const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a'); link.href = URL.createObjectURL(blob); link.download = `documents_types_${new Date().toISOString().split('T')[0]}.csv`; link.click(); URL.revokeObjectURL(link.href);
        },

        exportExcel() {
            const headers = ['Nom', 'Code', 'Description', 'Ordre', 'Obligatoire', 'Actif'];
            const rows = this.filteredData.map(t => [t.nom, t.code, t.description || '', t.ordre_affichage, t.est_obligatoire ? 'Oui' : 'Non', t.actif ? 'Oui' : 'Non']);
            const excel = [headers.join('\t'), ...rows.map(r => r.join('\t'))].join('\n');
            const blob = new Blob(['\uFEFF' + excel], { type: 'application/vnd.ms-excel' });
            const link = document.createElement('a'); link.href = URL.createObjectURL(blob); link.download = `documents_types_${new Date().toISOString().split('T')[0]}.xls`; link.click(); URL.revokeObjectURL(link.href);
        }
    }));
});
</script>

<style>
[x-cloak] { display: none !important; }
</style>

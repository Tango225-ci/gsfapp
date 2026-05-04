<div x-data="dataTable" x-init="loadVisitesFromDB()">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
        <!-- Header avec recherche et boutons -->
        <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Tableau Récapitulatif des Visites</h2>

                <div class="flex items-center gap-3 flex-wrap">
                    <!-- Filtre par date -->
                    <select x-model="filtreDate" @change="currentPage = 1"
                        class="w-40 rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">Toutes les dates</option>
                        <option value="AUJOURDHUI">Aujourd'hui</option>
                        <option value="CETTE_SEMAINE">Cette semaine</option>
                        <option value="CE_MOIS">Ce mois</option>
                    </select>

                    <!-- Barre de recherche -->
                    <div class="relative w-56">
                        <input type="text" x-model="search" placeholder="Rechercher visiteur, motif..."
                            class="w-full pl-4 pr-10 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white text-sm">
                        <svg class="absolute right-3 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>

                    <!-- Bouton Nouvelle Visite -->
                    <button @click="newVisite"
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
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 p-4 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Total visites</p>
                        <p class="text-2xl font-bold text-gray-800 dark:text-white" x-text="stats.total"></p>
                    </div>
                    <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Aujourd'hui</p>
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400" x-text="stats.aujourdhui"></p>
                    </div>
                    <div class="w-10 h-10 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Ce mois</p>
                        <p class="text-2xl font-bold text-purple-600 dark:text-purple-400" x-text="stats.ce_mois"></p>
                    </div>
                    <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Overlay formulaire création visite -->
        <div x-show="showVisiteForm" x-cloak @keydown.escape.window="closeVisiteForm" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div @click.stop class="relative w-full max-w-3xl max-h-[90vh] overflow-y-auto rounded-2xl bg-white shadow-2xl dark:bg-gray-900">

                <div class="sticky top-0 flex items-center justify-between border-b border-gray-200 bg-white px-6 py-4 dark:border-gray-700 dark:bg-gray-900">
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white" x-text="editingVisite ? 'Modifier la visite' : 'Nouvelle visite'"></h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Ajoutez une visite avec un formulaire clair et rapide</p>
                    </div>
                    <button type="button" @click="closeVisiteForm" class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form @submit.prevent="saveVisite" class="p-6">
                    <template x-if="formErrors.length">
                        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800 dark:border-red-800 dark:bg-red-900/20 dark:text-red-200">
                            <p class="font-semibold">Veuillez corriger les erreurs :</p>
                            <ul class="mt-2 list-inside list-disc">
                                <template x-for="error in formErrors" :key="error"><li x-text="error"></li></template>
                            </ul>
                        </div>
                    </template>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Nom du visiteur *</label>
                            <input type="text" x-model="form.nom_visiteur" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white" placeholder="Jean Dupont">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Contact visiteur * (10 chiffres)</label>
                            <input type="tel" x-model="form.contact_visiteur" required @input="validatePhoneNumber" maxlength="10" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white" placeholder="0123456789">
                            <p class="mt-1 text-xs text-gray-500">Le numéro doit contenir exactement 10 chiffres</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Adresse visiteur *</label>
                            <input type="text" x-model="form.adresse_visiteur" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white" placeholder="Adresse complète">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Date visite *</label>
                            <input type="date" x-model="form.date_visite" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Heure arrivée</label>
                            <input type="time" x-model="form.heure_arrivee" @change="validateHeureArrivee" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                            <p class="mt-1 text-xs text-gray-500">Optionnel, doit être avant l'heure de départ</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Heure départ *</label>
                            <input type="time" x-model="form.heure_depart" required @change="validateHeureDepart" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                            <p class="mt-1 text-xs text-gray-500">Doit être après l'heure d'arrivée</p>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Motif *</label>
                            <textarea x-model="form.motif" required rows="3" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white" placeholder="Motif de la visite..."></textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex gap-3 border-t border-gray-200 pt-6 dark:border-gray-700">
                        <button type="button" @click="closeVisiteForm" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">Annuler</button>
                        <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"><span x-text="editingVisite ? 'Mettre à jour' : 'Ajouter visite'"></span></button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Overlay formulaire affichage détails visite -->
        <div x-show="showDetailsModal" x-cloak @keydown.escape.window="closeDetailsModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div @click.stop class="relative w-full max-w-2xl max-h-[85vh] overflow-y-auto rounded-xl bg-white shadow-2xl dark:bg-gray-900">
                <div class="sticky top-0 flex items-center justify-between border-b border-gray-200 bg-white px-5 py-3 dark:border-gray-700 dark:bg-gray-900">
                    <div><h3 class="text-lg font-semibold text-gray-900 dark:text-white">Détails de la visite</h3><p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Informations complètes (lecture seule)</p></div>
                    <button type="button" @click="closeDetailsModal" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
                </div>
                <div class="p-5">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div><label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">ID Visite</label><input type="text" x-model="selectedVisite?.id_visite" readonly disabled class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-1.5 text-sm text-gray-600 cursor-not-allowed dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400"></div>
                        <div><label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Date création</label><input type="text" x-model="selectedVisite?.date_create" readonly disabled class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-1.5 text-sm text-gray-600 cursor-not-allowed dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400"></div>
                        <div><label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Nom visiteur</label><input type="text" x-model="selectedVisite?.nom_visiteur" readonly disabled class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-1.5 text-sm text-gray-600 cursor-not-allowed dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400"></div>
                        <div><label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Contact visiteur</label><input type="text" x-model="selectedVisite?.contact_visiteur" readonly disabled class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-1.5 text-sm text-gray-600 cursor-not-allowed dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400"></div>
                        <div class="sm:col-span-2"><label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Adresse visiteur</label><input type="text" x-model="selectedVisite?.adresse_visiteur" readonly disabled class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-1.5 text-sm text-gray-600 cursor-not-allowed dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400"></div>
                        <div><label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Date visite</label><input type="text" x-model="selectedVisite?.date_visite" readonly disabled class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-1.5 text-sm text-gray-600 cursor-not-allowed dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400"></div>
                        <div><label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Heure arrivée</label><input type="text" x-model="selectedVisite?.heure_arrivee" readonly disabled class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-1.5 text-sm text-gray-600 cursor-not-allowed dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400" placeholder="Non renseignée"></div>
                        <div><label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Heure départ</label><input type="text" x-model="selectedVisite?.heure_depart" readonly disabled class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-1.5 text-sm text-gray-600 cursor-not-allowed dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400"></div>
                        <div class="sm:col-span-2"><label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Motif</label><textarea x-model="selectedVisite?.motif" readonly disabled rows="3" class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-1.5 text-sm text-gray-600 cursor-not-allowed dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400"></textarea></div>
                    </div>
                    <div class="mt-5 flex gap-3 border-t border-gray-200 pt-4 dark:border-gray-700">
                        <button type="button" @click="closeDetailsModal" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">Fermer</button>
                        <button type="button" @click="editFromDetails" class="rounded-lg bg-blue-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-blue-700">Modifier la visite</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tableau -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">VISITEUR</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">CONTACT</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">DATE VISITE</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">HEURE ARRIVÉE</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">HEURE DÉPART</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">MOTIF</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <template x-for="item in paginatedData" :key="item.id_visite">
                        <tr @click="selectRow(item.id_visite)" :class="selectedRow === item.id_visite ? 'bg-gray-200 dark:bg-gray-700 border-l-4 border-blue-500 shadow-sm' : 'hover:bg-gray-50 dark:hover:bg-gray-700/50'" class="cursor-pointer transition-all duration-200">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white" x-text="item.id_visite"></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center">
                                        <span class="text-sm font-medium text-white" x-text="item.initials"></span>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white" x-text="item.nom_visiteur"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400" x-text="item.contact_visiteur"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400" x-text="item.date_visite"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400" x-text="item.heure_arrivee || '-'"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400" x-text="item.heure_depart"></td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 max-w-xs truncate" x-text="item.motif"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button @click.stop="viewDetails(item)" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300 mr-3 transition-colors" title="Voir détails"><svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg></button>
                                <button @click.stop="editItem(item)" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 mr-3 transition-colors" title="Modifier"><svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg></button>
                                <button @click.stop="deleteItem(item.id_visite)" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 transition-colors" title="Supprimer"><svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="filteredData.length === 0">
                        <td colspan="8" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                            <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <p class="text-lg font-medium">Aucune visite trouvée</p>
                            <p class="text-sm mt-1">Essayez de modifier vos filtres</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
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
        filtreDate: '',
        currentPage: 1,
        itemsPerPage: 10,
        selectedRow: null,
        showVisiteForm: false,
        showDetailsModal: false,
        selectedVisite: null,
        editingVisite: null,
        formErrors: [],
        form: {
            nom_visiteur: '',
            contact_visiteur: '',
            adresse_visiteur: '',
            date_visite: '',
            heure_arrivee: '',
            heure_depart: '',
            motif: ''
        },
        items: [],

        get stats() {
            const total = this.items.length;
            const today = new Date().toISOString().split('T')[0];
            const currentMonth = new Date().getMonth();
            const currentYear = new Date().getFullYear();
            const aujourdhui = this.items.filter(i => i.date_visite === today).length;
            const ce_mois = this.items.filter(i => {
                if (!i.date_visite) return false;
                const d = new Date(i.date_visite);
                return d.getMonth() === currentMonth && d.getFullYear() === currentYear;
            }).length;
            return { total, aujourdhui, ce_mois };
        },

        get filteredData() {
            let data = [...this.items];

            if (this.filtreDate) {
                const today = new Date();
                const todayStr = today.toISOString().split('T')[0];
                const currentMonth = today.getMonth();
                const currentYear = today.getFullYear();

                data = data.filter(i => {
                    if (!i.date_visite) return false;
                    const d = new Date(i.date_visite);
                    switch (this.filtreDate) {
                        case 'AUJOURDHUI': return i.date_visite === todayStr;
                        case 'CETTE_SEMAINE':
                            const startOfWeek = new Date(today);
                            startOfWeek.setDate(today.getDate() - today.getDay());
                            startOfWeek.setHours(0, 0, 0, 0);
                            return d >= startOfWeek;
                        case 'CE_MOIS': return d.getMonth() === currentMonth && d.getFullYear() === currentYear;
                        default: return true;
                    }
                });
            }

            if (this.search) {
                const s = this.search.toLowerCase();
                data = data.filter(item =>
                    item.nom_visiteur.toLowerCase().includes(s) ||
                    item.contact_visiteur.includes(s) ||
                    item.motif.toLowerCase().includes(s) ||
                    (item.adresse_visiteur && item.adresse_visiteur.toLowerCase().includes(s))
                );
            }
            return data;
        },

        get totalPages() { return Math.ceil(this.filteredData.length / this.itemsPerPage) || 1 },
        get paginatedData() { const s = (this.currentPage - 1) * this.itemsPerPage; return this.filteredData.slice(s, s + this.itemsPerPage) },
        get startIndex() { return this.filteredData.length === 0 ? 0 : (this.currentPage - 1) * this.itemsPerPage + 1 },
        get endIndex() { return Math.min(this.currentPage * this.itemsPerPage, this.filteredData.length) },
        nextPage() { if (this.currentPage < this.totalPages) { this.currentPage++; this.selectedRow = null } },
        prevPage() { if (this.currentPage > 1) { this.currentPage--; this.selectedRow = null } },
        selectRow(id) { this.selectedRow = id },

        validatePhoneNumber() {
            this.form.contact_visiteur = this.form.contact_visiteur.replace(/\D/g, '');
            if (this.form.contact_visiteur.length > 10) this.form.contact_visiteur = this.form.contact_visiteur.slice(0, 10);
        },
        validateHeureArrivee() {
            if (this.form.heure_arrivee && this.form.heure_depart && this.form.heure_arrivee >= this.form.heure_depart) {
                this.formErrors.push('L\'heure d\'arrivée doit être avant l\'heure de départ.');
                return false;
            }
            return true;
        },
        validateHeureDepart() {
            if (this.form.heure_arrivee && this.form.heure_depart && this.form.heure_arrivee >= this.form.heure_depart) {
                this.formErrors.push('L\'heure de départ doit être après l\'heure d\'arrivée.');
                return false;
            }
            return true;
        },
        getTodayDate() { const t = new Date(); return `${t.getFullYear()}-${String(t.getMonth()+1).padStart(2,'0')}-${String(t.getDate()).padStart(2,'0')}` },
        newVisite() {
            this.editingVisite = null;
            this.form = { nom_visiteur: '', contact_visiteur: '', adresse_visiteur: '', date_visite: this.getTodayDate(), heure_arrivee: '', heure_depart: '', motif: '' };
            this.formErrors = []; this.showVisiteForm = true;
        },
        editItem(item) {
            this.editingVisite = item;
            this.form = { nom_visiteur: item.nom_visiteur, contact_visiteur: item.contact_visiteur, adresse_visiteur: item.adresse_visiteur, date_visite: item.date_visite, heure_arrivee: item.heure_arrivee || '', heure_depart: item.heure_depart, motif: item.motif };
            this.formErrors = []; this.showVisiteForm = true;
        },
        closeVisiteForm() { this.showVisiteForm = false; this.editingVisite = null; this.formErrors = []; },
        closeDetailsModal() { this.showDetailsModal = false; this.selectedVisite = null; },
        viewDetails(item) { this.selectedVisite = item; this.showDetailsModal = true; },
        editFromDetails() { const item = this.selectedVisite; this.closeDetailsModal(); this.editItem(item); },
        generateInitials(name) {
            return name.split(' ').filter(p => p.length).slice(0, 2).map(p => p[0].toUpperCase()).join('');
        },

        loadVisitesFromDB() {
            console.log('Chargement des visites...');
            fetch('/api/visites')
                .then(response => {
                    if (!response.ok) throw new Error('HTTP ' + response.status);
                    return response.json();
                })
                .then(data => {
                    if (Array.isArray(data) && data.length > 0) {
                        this.items = data.map(visite => ({
                            id_visite: visite.id_visite,
                            nom_visiteur: visite.nom_visiteur,
                            contact_visiteur: visite.contact_visiteur,
                            adresse_visiteur: visite.adresse_visiteur,
                            date_visite: visite.date_visite,
                            heure_arrivee: visite.heure_arrivee,
                            heure_depart: visite.heure_depart,
                            motif: visite.motif,
                            date_create: visite.date_create,
                            valeursupprimee: visite.valeursupprimee,
                            initials: this.generateInitials(visite.nom_visiteur),
                        }));
                    } else { this.items = []; }
                    this.currentPage = 1;
                })
                .catch(error => { console.error('Erreur:', error); this.items = []; });
        },

        saveVisite() {
            this.formErrors = [];
            if (!this.form.nom_visiteur.trim()) this.formErrors.push('Le nom du visiteur est requis.');
            if (!this.form.contact_visiteur.trim()) this.formErrors.push('Le contact est requis.');
            else if (this.form.contact_visiteur.length !== 10) this.formErrors.push('Le numéro doit contenir exactement 10 chiffres.');
            if (!this.form.adresse_visiteur.trim()) this.formErrors.push('L\'adresse est requise.');
            if (!this.form.date_visite) this.formErrors.push('La date est requise.');
            if (!this.form.heure_depart) this.formErrors.push('L\'heure de départ est requise.');
            if (this.form.heure_arrivee && this.form.heure_depart && this.form.heure_arrivee >= this.form.heure_depart) this.formErrors.push('L\'heure d\'arrivée doit être avant l\'heure de départ.');
            if (!this.form.motif.trim()) this.formErrors.push('Le motif est requis.');
            if (this.formErrors.length) return;

            let url = '/api/visites', method = 'POST';
            if (this.editingVisite) { url = `/api/visites/${this.editingVisite.id_visite}`; method = 'PUT'; }

            fetch(url, {
                method, headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '', 'Accept': 'application/json' },
                body: JSON.stringify(this.form)
            })
            .then(async response => { if (!response.ok) { const text = await response.text(); throw new Error(`HTTP ${response.status}: ${text.substring(0, 100)}`); } return response.json(); })
            .then(data => {
                if (data.success) {
                    if (this.editingVisite) {
                        const index = this.items.findIndex(i => i.id_visite === this.editingVisite.id_visite);
                        if (index !== -1) {
                            this.items[index] = { ...this.items[index], nom_visiteur: data.visite.nom_visiteur, contact_visiteur: data.visite.contact_visiteur, adresse_visiteur: data.visite.adresse_visiteur, date_visite: data.visite.date_visite, heure_arrivee: data.visite.heure_arrivee, heure_depart: data.visite.heure_depart, motif: data.visite.motif, initials: this.generateInitials(data.visite.nom_visiteur) };
                        }
                        alert('Visite modifiée.');
                    } else {
                        this.items.push({ id_visite: data.visite.id_visite, nom_visiteur: data.visite.nom_visiteur, contact_visiteur: data.visite.contact_visiteur, adresse_visiteur: data.visite.adresse_visiteur, date_visite: data.visite.date_visite, heure_arrivee: data.visite.heure_arrivee, heure_depart: data.visite.heure_depart, motif: data.visite.motif, date_create: data.visite.date_create, valeursupprimee: 0, initials: this.generateInitials(data.visite.nom_visiteur) });
                        alert('Visite créée.');
                    }
                    this.closeVisiteForm();
                } else { alert('Erreur: ' + (data.message || 'Erreur inconnue')); }
            })
            .catch(error => { console.error('Erreur:', error); alert('Erreur: ' + error.message); });
        },

        deleteItem(id) {
            if (!confirm('Supprimer cette visite ?')) return;
            fetch(`/api/visites/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '' } })
                .then(response => response.json())
                .then(data => {
                    if (data.success) { this.items = this.items.filter(item => item.id_visite !== id); if (this.selectedRow === id) this.selectedRow = null; if (this.paginatedData.length === 0 && this.currentPage > 1) this.currentPage--; alert('Visite supprimée.'); }
                    else alert('Erreur lors de la suppression');
                })
                .catch(error => { console.error('Erreur:', error); alert('Une erreur est survenue'); });
        },

        exportCSV() {
            const h = ['ID','Visiteur','Contact','Adresse','Date visite','Heure arrivée','Heure départ','Motif','Date création'];
            const rows = this.filteredData.map(i => [i.id_visite,i.nom_visiteur,i.contact_visiteur,i.adresse_visiteur,i.date_visite,i.heure_arrivee||'',i.heure_depart,i.motif,i.date_create||'']);
            const csv = [h.join(','),...rows.map(r=>r.map(c=>`"${c}"`).join(','))].join('\n');
            const b = new Blob(['\uFEFF'+csv],{type:'text/csv;charset=utf-8;'}); const l = document.createElement('a'); l.href=URL.createObjectURL(b); l.download=`visites_${new Date().toISOString().split('T')[0]}.csv`; l.click();
        },

        exportExcel() {
            const h = ['ID','Visiteur','Contact','Adresse','Date visite','Heure arrivée','Heure départ','Motif','Date création'];
            const rows = this.filteredData.map(i => [i.id_visite,i.nom_visiteur,i.contact_visiteur,i.adresse_visiteur,i.date_visite,i.heure_arrivee||'',i.heure_depart,i.motif,i.date_create||'']);
            const xl = [h.join('\t'),...rows.map(r=>r.join('\t'))].join('\n');
            const b = new Blob(['\uFEFF'+xl],{type:'application/vnd.ms-excel'}); const l = document.createElement('a'); l.href=URL.createObjectURL(b); l.download=`visites_${new Date().toISOString().split('T')[0]}.xls`; l.click();
        }
    }))
})
</script>

<style>
[x-cloak] { display: none !important; }
</style>

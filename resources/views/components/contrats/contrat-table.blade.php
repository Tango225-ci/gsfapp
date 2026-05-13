<div x-data="dataTable" x-init="init()">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
        <!-- Header avec recherche et boutons -->
        <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Tableau Récapitulatif des Contrats</h2>

                <div class="flex items-center gap-3 flex-wrap">
                    <select x-model="filtreStatut" @change="currentPage = 1"
                        class="w-40 rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">Tous les statuts</option>
                        <option value="EN_COURS">En cours</option>
                        <option value="SOLDE">Soldés</option>
                        <option value="RESILIE">Résiliés</option>
                        <option value="EN_RETARD">En retard</option>
                    </select>

                    <select x-model="filtreType" @change="currentPage = 1"
                        class="w-36 rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">Tous les types</option>
                        <option value="COMPTANT">Comptant</option>
                        <option value="ECHELONNE">Échelonné</option>
                    </select>

                    <div class="relative w-56">
                        <input type="text" x-model="search" placeholder="Rechercher référence, client..."
                            class="w-full pl-4 pr-10 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white text-sm">
                        <svg class="absolute right-3 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>

                    <button @click="newContrat"
                        class="flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Nouveau
                    </button>

                    <button @click="exportCSV"
                        class="flex items-center gap-2 border border-gray-300 dark:border-gray-600 px-4 py-2 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        CSV
                    </button>

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
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Total contrats</p>
                        <p class="text-2xl font-bold text-gray-800 dark:text-white" x-text="stats.total"></p>
                    </div>
                    <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Soldés</p>
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400" x-text="stats.soldes"></p>
                    </div>
                    <div class="w-10 h-10 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">En retard</p>
                        <p class="text-2xl font-bold text-red-600 dark:text-red-400" x-text="stats.en_retard"></p>
                    </div>
                    <div class="w-10 h-10 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">En cours</p>
                        <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400" x-text="stats.en_cours"></p>
                    </div>
                    <div class="w-10 h-10 bg-yellow-100 dark:bg-yellow-900 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Overlay formulaire création/modification contrat -->
        <div x-show="showContratForm" x-cloak @click.away="closeContratForm" @keydown.escape.window="closeContratForm" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div @click.stop class="relative w-full max-w-3xl max-h-[90vh] overflow-y-auto rounded-2xl bg-white shadow-2xl dark:bg-gray-900 mx-auto">
                <div class="sticky top-0 flex items-center justify-between border-b border-gray-200 bg-white px-6 py-4 dark:border-gray-700 dark:bg-gray-900">
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white" x-text="editingContrat ? 'Modifier le contrat' : 'Nouveau contrat'"></h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400" x-text="editingContrat ? 'Modifiez les informations du contrat' : 'Ajoutez un contrat avec un formulaire clair et rapide'"></p>
                    </div>
                    <button type="button" @click="closeContratForm" class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form @submit.prevent="saveContrat" class="p-6">
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
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Client *</label>
                            <select x-model="form.id_client" required :disabled="editingContrat" :class="editingContrat ? 'bg-gray-100 cursor-not-allowed' : ''" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                                <option value="">Sélectionnez un client</option>
                                <template x-for="client in clientsListe" :key="client.id"><option :value="client.id" x-text="client.nom_prenoms"></option></template>
                            </select>
                            <p x-show="editingContrat" class="mt-1 text-xs text-amber-600">Le client ne peut pas être modifié</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Lot *</label>
                            <template x-if="!editingContrat">
                                <select x-model="form.id_lot" required @change="updateMontantFromLot(form.id_lot)" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                                    <option value="">Sélectionnez un lot</option>
                                    <template x-for="lot in lotsDisponibles" :key="lot.id_lot"><option :value="lot.id_lot" x-text="lot.numero_lot + ' - ' + (lot.lotissement?.nom || 'Sans lotissement')"></option></template>
                                </select>
                            </template>
                            <template x-if="editingContrat">
                                <div class="relative"><input type="text" :value="getLotDisplayText()" readonly disabled class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-2 text-gray-600 cursor-not-allowed dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"><input type="hidden" x-model="form.id_lot"><div class="absolute inset-y-0 right-0 flex items-center pr-3"><svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg></div></div>
                            </template>
                            <p class="mt-1 text-xs" :class="editingContrat ? 'text-amber-600 dark:text-amber-400' : 'text-gray-500'"><span x-show="!editingContrat">Seuls les lots disponibles (non sous contrat) sont affichés</span><span x-show="editingContrat" class="flex items-center gap-1"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>Le lot ne peut pas être modifié</span></p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Date contrat *</label>
                            <input type="date" x-model="form.date_contrat" required :disabled="editingContrat" :class="editingContrat ? 'bg-gray-100 cursor-not-allowed' : ''" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                            <p x-show="editingContrat" class="mt-1 text-xs text-amber-600">La date du contrat ne peut pas être modifiée</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Date début</label>
                            <input type="date" x-model="form.date_debut" :disabled="editingContrat" :class="editingContrat ? 'bg-gray-100 cursor-not-allowed' : ''" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                            <p class="mt-1 text-xs text-gray-500">Date de début des échéances (par défaut = date contrat)</p>
                            <p x-show="editingContrat" class="mt-1 text-xs text-amber-600">La date de début ne peut pas être modifiée</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Type de paiement *</label>
                            <select x-model="form.type_paiement" required @change="handleTypePaiementChange()" :disabled="editingContrat" :class="editingContrat ? 'bg-gray-100 cursor-not-allowed' : ''" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                                <option value="COMPTANT">Comptant</option>
                                <option value="ECHELONNE">Échelonné</option>
                            </select>
                            <p x-show="editingContrat" class="mt-1 text-xs text-amber-600">Le type de paiement ne peut pas être modifié</p>
                        </div>
                        <div x-show="form.type_paiement === 'ECHELONNE'" x-transition>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Durée (mois) *</label>
                            <input type="number" x-model="form.duree_mois" min="1" max="360" required :disabled="editingContrat" :class="editingContrat ? 'bg-gray-100 cursor-not-allowed' : ''" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white" placeholder="12">
                            <p class="mt-1 text-xs text-gray-500">Nombre de mois pour les échéances</p>
                            <p x-show="editingContrat" class="mt-1 text-xs text-amber-600">La durée ne peut pas être modifiée</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Statut *</label>
                            <select x-model="form.statut" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                                <option value="EN_COURS">En cours</option>
                                <option value="SOLDE">Soldé</option>
                                <option value="RESILIE">Résilié</option>
                                <option value="EN_RETARD">En retard</option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Le statut peut être modifié</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Montant total (FCFA) *<span x-show="form.type_paiement === 'COMPTANT'" class="text-xs font-normal text-gray-500">(Prix comptant)</span><span x-show="form.type_paiement === 'ECHELONNE'" class="text-xs font-normal text-gray-500">(Prix échelonné)</span></label>
                            <input type="text" x-model="montantTotalDisplay" @input="updateMontantTotalFromDisplay" :disabled="editingContrat" :class="editingContrat ? 'bg-gray-100 cursor-not-allowed' : ''" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white" placeholder="0">
                            <p class="mt-1 text-xs text-gray-500"><span x-show="form.type_paiement === 'COMPTANT' && !editingContrat">Le montant est automatiquement basé sur le prix comptant du lot sélectionné (modifiable)</span><span x-show="form.type_paiement === 'ECHELONNE' && !editingContrat">Le montant est automatiquement basé sur le prix échelonné du lot sélectionné (modifiable)</span><span x-show="editingContrat" class="text-amber-600">Le montant ne peut pas être modifié</span></p>
                        </div>
                        <template x-if="form.type_paiement === 'COMPTANT'">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Mode de paiement *</label>
                                <select x-model="form.mode_paiement" required :disabled="editingContrat" :class="editingContrat ? 'bg-gray-100 cursor-not-allowed' : ''" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                                    <option value="">Sélectionnez un mode de paiement</option>
                                    <option value="ESPECES">Espèces</option>
                                    <option value="VIREMENT">Virement bancaire</option>
                                    <option value="CHEQUE">Chèque</option>
                                    <option value="MOBILE_MONEY">Mobile Money</option>
                                </select>
                                <p x-show="editingContrat" class="mt-1 text-xs text-amber-600">Le mode de paiement ne peut pas être modifié</p>
                            </div>
                        </template>
                        <template x-if="form.type_paiement === 'COMPTANT' && (form.mode_paiement === 'VIREMENT' || form.mode_paiement === 'CHEQUE')">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Banque *</label>
                                <select x-model="form.banque_paiement" required :disabled="editingContrat" :class="editingContrat ? 'bg-gray-100 cursor-not-allowed' : ''" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                                    <option value="">Sélectionnez une banque</option>
                                    <option value="SGBCI">SGBCI (Société Générale)</option>
                                    <option value="ECOBANK">Ecobank CI</option>
                                    <option value="NSIA">NSIA Banque</option>
                                    <option value="ORABANK">Orabank</option>
                                    <option value="BICICI">BICICI</option>
                                    <option value="BRIDGE">Bridge Bank Group</option>
                                    <option value="CORIS">Coris Bank International</option>
                                    <option value="BANK_OF_AFRICA">Bank of Africa</option>
                                </select>
                                <p x-show="editingContrat" class="mt-1 text-xs text-amber-600">La banque ne peut pas être modifiée</p>
                            </div>
                        </template>
                    </div>

                    <!-- SECTION FRAIS ACD - AFFICHÉE LORS DE LA SÉLECTION D'UN LOT -->
                    <div x-show="fraisAcdListe.length > 0 && !editingContrat" class="mt-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Frais ACD - Ville: <span x-text="fraisAcdVille" class="text-blue-600 font-medium"></span>
                        </h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead class="bg-gray-100 dark:bg-gray-700">
                                    </table>
                                        <th class="px-3 py-2 text-left">Type de document</th>
                                        <th class="px-3 py-2 text-right">Montant (FCFA)</th>
                                        <th class="px-3 py-2 text-center">Obligatoire</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <template x-for="frais in fraisAcdListe" :key="frais.id_frais">
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <td class="px-3 py-2">
                                                <p class="font-medium" x-text="frais.type_document"></p>
                                                <p class="text-xs text-gray-500" x-text="frais.code"></p>
                                            </td>
                                            <td class="px-3 py-2 text-right font-semibold" x-text="frais.montant_formatted"></td>
                                            <td class="px-3 py-2 text-center">
                                                <span x-show="frais.est_obligatoire" class="px-2 py-0.5 bg-red-100 text-red-700 rounded-full text-xs font-semibold">Obligatoire</span>
                                                <span x-show="!frais.est_obligatoire" class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded-full text-xs font-semibold">Optionnel</span>
                                            </td>
                                        </tr>
                                    </template>
                                    <tr class="bg-blue-50 dark:bg-blue-900/20 font-semibold">
                                        <td class="px-3 py-2">TOTAL FRAIS ACD</td>
                                        <td class="px-3 py-2 text-right text-blue-600 dark:text-blue-400" x-text="totalFraisAcdFormatted"></td>
                                        <td class="px-3 py-2 text-center"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Ces frais seront automatiquement ajoutés au contrat et devront être payés en plus du montant total.</p>
                    </div>

                    <div x-show="form.type_paiement === 'ECHELONNE' && form.duree_mois > 0" class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                        <p class="text-xs text-blue-700 dark:text-blue-300 flex items-center gap-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg><span x-text="'Le contrat sera divisé en ' + (form.duree_mois || 0) + ' échéances mensuelles.'"></span></p>
                    </div>
                    <div class="mt-6 flex gap-3 border-t border-gray-200 pt-6 dark:border-gray-700">
                        <button type="button" @click="closeContratForm" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">Annuler</button>
                        <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"><span x-text="editingContrat ? 'Mettre à jour' : 'Ajouter contrat'"></span></button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODALE DÉTAILS CONTRAT -->
        <div x-show="showDetailsModal" x-cloak @click.away="closeDetailsModal" @keydown.escape.window="closeDetailsModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div @click.stop class="relative w-full max-w-4xl max-h-[85vh] overflow-y-auto rounded-xl bg-white shadow-2xl dark:bg-gray-900 mx-auto">
                <div class="sticky top-0 flex items-center justify-between border-b border-gray-200 bg-white px-4 py-3 dark:border-gray-700 dark:bg-gray-900 z-10">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">Détails du contrat <span x-text="selectedContrat?.reference_contrat" class="text-blue-600"></span></h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Situation des paiements et documents ACD</p>
                    </div>
                    <button @click="closeDetailsModal" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="p-4">
                    <!-- SECTION 1: INFORMATIONS GÉNÉRALES -->
                    <div class="mb-4">
                        <h4 class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            INFORMATIONS GÉNÉRALES
                        </h4>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs bg-gray-50 dark:bg-gray-800/50 p-3 rounded-lg">
                            <div><span class="text-gray-500">Réf :</span> <span class="font-medium" x-text="selectedContrat?.reference_contrat || '-'"></span></div>
                            <div><span class="text-gray-500">Client :</span> <span class="font-medium" x-text="selectedContrat?.client_nom || '-'"></span></div>
                            <div><span class="text-gray-500">Lot :</span> <span class="font-medium" x-text="selectedContrat?.lot_numero || '-'"></span></div>
                            <div><span class="text-gray-500">Date :</span> <span class="font-medium" x-text="selectedContrat?.date_contrat_formatted || '-'"></span></div>
                            <div><span class="text-gray-500">Type :</span> <span class="font-medium" x-text="selectedContrat?.type_paiement_label || '-'"></span></div>
                            <div x-show="selectedContrat?.duree_mois"><span class="text-gray-500">Durée :</span> <span class="font-medium" x-text="selectedContrat?.duree_mois + ' mois'"></span></div>
                            <div><span class="text-gray-500">Statut :</span> <span class="font-medium" :class="{'text-yellow-600': selectedContrat?.statut === 'EN_COURS','text-green-600': selectedContrat?.statut === 'SOLDE','text-red-600': selectedContrat?.statut === 'RESILIE','text-orange-600': selectedContrat?.statut === 'EN_RETARD'}" x-text="selectedContrat?.statut_label || '-'"></span></div>
                            <div x-show="selectedContrat?.mode_paiement"><span class="text-gray-500">Mode :</span> <span class="font-medium" x-text="selectedContrat?.mode_paiement_label || '-'"></span></div>
                        </div>
                    </div>

                    <!-- SECTION 2: SITUATION DES PAIEMENTS -->
                    <div class="mb-4">
                        <h4 class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            SITUATION DES PAIEMENTS
                        </h4>
                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-800 dark:to-gray-700 p-3 rounded-lg">
                            <div class="grid grid-cols-3 gap-3 text-center mb-2">
                                <div><p class="text-xs text-gray-500">Total</p><p class="text-base font-bold" x-text="situationPaiement?.montant_total || '0 FCFA'"></p></div>
                                <div><p class="text-xs text-gray-500">Payé</p><p class="text-base font-bold text-green-600" x-text="situationPaiement?.montant_paye || '0 FCFA'"></p></div>
                                <div><p class="text-xs text-gray-500">Reste</p><p class="text-base font-bold" :class="(situationPaiement?.reste_a_payer_raw || 0) > 0 ? 'text-orange-600' : 'text-green-600'" x-text="situationPaiement?.reste_a_payer || '0 FCFA'"></p></div>
                            </div>
                            <div class="flex justify-between text-xs mb-1"><span>Progression</span><span x-text="(situationPaiement?.progression || 0) + '%'"></span></div>
                            <div class="w-full bg-gray-200 rounded-full h-2"><div class="bg-blue-600 h-2 rounded-full" :style="'width: ' + (situationPaiement?.progression || 0) + '%'"></div></div>
                        </div>
                    </div>

                    <!-- SECTION 3: ÉCHÉANCES -->
                    <div x-show="selectedContrat?.type_paiement === 'ECHELONNE' && echeances.length > 0" class="mb-4">
                        <h4 class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            ÉCHÉANCES
                            <span class="text-xs text-gray-500 ml-1">(<span x-text="echeancesStats?.payees || 0"></span>/<span x-text="echeancesStats?.total || 0"></span>)</span>
                        </h4>
                        <div class="overflow-x-auto border rounded-lg text-xs max-h-64 overflow-y-auto">
                            <table class="min-w-full">
                                <thead class="bg-gray-100 dark:bg-gray-800 sticky top-0">
                                    <tr>
                                        <th class="px-2 py-1 text-left">N°</th>
                                        <th class="px-2 py-1 text-left">Date</th>
                                        <th class="px-2 py-1 text-right">Prévu</th>
                                        <th class="px-2 py-1 text-right">Payé</th>
                                        <th class="px-2 py-1 text-right">Reste</th>
                                        <th class="px-2 py-1 text-center">Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="echeance in echeances" :key="echeance.id">
                                        <tr class="border-t">
                                            <td class="px-2 py-1" x-text="echeance.numero"></td>
                                            <td class="px-2 py-1" :class="echeance.est_en_retard ? 'text-red-600 font-medium' : ''" x-text="echeance.date_echeance"></td>
                                            <td class="px-2 py-1 text-right" x-text="echeance.montant_prevu_formatted"></td>
                                            <td class="px-2 py-1 text-right text-green-600" x-text="echeance.montant_paye_formatted"></td>
                                            <td class="px-2 py-1 text-right" :class="echeance.reste_a_payer > 0 ? 'text-orange-600' : ''" x-text="echeance.reste_a_payer_formatted"></td>
                                            <td class="px-2 py-1 text-center"><span x-text="echeance.statut_label" :class="{'bg-yellow-100 text-yellow-700 px-1 py-0.5 rounded': echeance.statut === 'A_PAYER','bg-green-100 text-green-700 px-1 py-0.5 rounded': echeance.statut === 'PAYE','bg-red-100 text-red-700 px-1 py-0.5 rounded': echeance.statut === 'EN_RETARD'}" class="text-xs"></span></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- SECTION 4: DOCUMENTS ACD -->
                    <div class="mb-4">
                        <h4 class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            DOCUMENTS ACD
                            <span class="text-xs text-gray-500 ml-1">(<span x-text="documentsStats?.total || 0"></span> documents)</span>
                            <span x-show="documentsStats?.payes > 0" class="text-xs text-green-500 ml-1">- <span x-text="documentsStats?.payes"></span> payés</span>
                            <span x-show="documentsStats?.disponibles > 0" class="text-xs text-blue-500 ml-1">- <span x-text="documentsStats?.disponibles"></span> disponibles</span>
                            <span x-show="documentsStats?.remis > 0" class="text-xs text-purple-500 ml-1">- <span x-text="documentsStats?.remis"></span> remis</span>
                            <span x-show="documentsStats?.montant_total > 0" class="text-xs text-gray-500 ml-1">- Total: <span x-text="formatPrice(documentsStats?.montant_total || 0)"></span></span>
                        </h4>

                        <div x-show="!documentsAcD || documentsAcD.length === 0" class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4 text-center">
                            <svg class="w-8 h-8 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Aucun document ACD associé à ce contrat.</p>
                            <p class="text-xs text-gray-400 mt-1">Les documents sont générés automatiquement à la création du contrat.</p>
                        </div>

                        <div x-show="documentsAcD && documentsAcD.length > 0" class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-100 dark:bg-gray-800">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Document</th>
                                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400">Montant</th>
                                        <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-400">Paiement</th>
                                        <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-400">Statut</th>
                                        <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-400">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                    <template x-for="doc in documentsAcD" :key="doc.id_contrat_document">
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                            <td class="px-3 py-2">
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900 dark:text-white" x-text="doc.document_type_nom"></p>
                                                    <p x-show="doc.date_disponible" class="text-xs text-gray-500">📅 Disponible le <span x-text="doc.date_disponible_formatted"></span></p>
                                                    <p x-show="doc.date_remis" class="text-xs text-green-600">✅ Remis le <span x-text="doc.date_remis_formatted"></span></p>
                                                    <p x-show="doc.fichier_path" class="text-xs text-blue-500">
                                                        <a :href="'/storage/' + doc.fichier_path" target="_blank" class="hover:underline">📎 Voir fichier</a>
                                                    </p>
                                                </div>
                                            </td>
                                            <td class="px-3 py-2 text-right">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white" x-text="doc.montant_formatted"></p>
                                                <p x-show="doc.statut_paiement === 'partiel'" class="text-xs text-orange-500">(partiel)</p>
                                            </td>
                                            <td class="px-3 py-2 text-center">
                                                <span x-text="doc.statut_paiement_label" :class="{
                                                    'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-200': doc.statut_paiement === 'non_paye',
                                                    'bg-orange-100 text-orange-700 dark:bg-orange-900 dark:text-orange-200': doc.statut_paiement === 'partiel',
                                                    'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-200': doc.statut_paiement === 'paye'
                                                }" class="px-2 py-0.5 rounded-full text-xs font-semibold"></span>
                                            </td>
                                            <td class="px-3 py-2 text-center">
                                                <span x-text="doc.statut_document_label" :class="{
                                                    'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300': doc.statut_document === 'en_attente',
                                                    'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-200': doc.statut_document === 'paye',
                                                    'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-200': doc.statut_document === 'disponible',
                                                    'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200': doc.statut_document === 'remis'
                                                }" class="px-2 py-0.5 rounded-full text-xs font-semibold"></span>
                                            </td>
                                            <td class="px-3 py-2 text-center">
                                                <div class="flex items-center justify-center gap-1 flex-wrap">
                                                    <button x-show="doc.statut_paiement !== 'paye' && doc.montant > 0" @click="openDocumentPaymentModal(doc)" class="text-green-600 hover:text-green-900 p-1 rounded" title="Payer">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                    </button>
                                                    <button x-show="doc.statut_paiement === 'paye' && doc.statut_document !== 'disponible' && doc.statut_document !== 'remis'" @click="marquerDocumentDisponible(doc.id_contrat_document)" class="text-blue-600 hover:text-blue-900 p-1 rounded" title="Marquer disponible">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                                    </button>
                                                    <button x-show="doc.statut_document === 'disponible'" @click="marquerDocumentRemis(doc.id_contrat_document)" class="text-purple-600 hover:text-purple-900 p-1 rounded" title="Marquer remis">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                                    </button>
                                                    <button @click="openUploadModal(doc)" class="text-gray-600 hover:text-gray-900 p-1 rounded" title="Upload fichier">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- SECTION 5: HISTORIQUE DES PAIEMENTS -->
                    <div class="mb-4">
                        <h4 class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            HISTORIQUE DES PAIEMENTS
                        </h4>
                        <div class="overflow-x-auto border rounded-lg text-xs max-h-48 overflow-y-auto">
                            <table class="min-w-full">
                                <thead class="bg-gray-100 dark:bg-gray-800 sticky top-0">
                                    <tr>
                                        <th class="px-2 py-1 text-left">Date</th>
                                        <th class="px-2 py-1 text-left">Réf</th>
                                        <th class="px-2 py-1 text-left">Type</th>
                                        <th class="px-2 py-1 text-right">Montant</th>
                                        <th class="px-2 py-1 text-center">Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="paiement in historiquePaiements" :key="paiement.id_paiement">
                                        <tr class="border-t">
                                            <td class="px-2 py-1" x-text="formatDate(paiement.date_paiement)"></td>
                                            <td class="px-2 py-1" x-text="paiement.reference || '-'"></td>
                                            <td class="px-2 py-1"><span x-text="paiement.type_paiement_label || 'Mensualité'" :class="paiement.type_paiement === 'FRAIS_DOCUMENT' ? 'text-purple-600' : ''"></span></td>
                                            <td class="px-2 py-1 text-right font-medium" x-text="formatPrice(paiement.montant)"></td>
                                            <td class="px-2 py-1 text-center"><span x-text="getStatutPaiementLabel(paiement.statut)" :class="{'bg-green-100 text-green-700 px-1 py-0.5 rounded': paiement.statut === 'VALIDE','bg-yellow-100 text-yellow-700 px-1 py-0.5 rounded': paiement.statut === 'EN_ATTENTE','bg-red-100 text-red-700 px-1 py-0.5 rounded': paiement.statut === 'REJETE'}" class="text-xs"></span></td>
                                        </tr>
                                    </template>
                                    <tr x-show="!historiquePaiements || historiquePaiements.length === 0">
                                        <td colspan="5" class="px-2 py-2 text-center text-gray-500">Aucun paiement</td>
                                    <tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Boutons d'action -->
                    <div class="flex gap-2 border-t pt-3 mt-2 justify-end">
                        <button @click="closeDetailsModal" class="px-3 py-1 text-xs rounded-lg border border-gray-300 hover:bg-gray-50">Fermer</button>
                        <button @click="imprimerRecap" class="px-3 py-1 text-xs rounded-lg bg-purple-600 text-white hover:bg-purple-700 flex items-center gap-1"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>Imprimer</button>
                        <button x-show="!selectedContrat?.est_solde && selectedContrat?.statut !== 'SOLDE'" @click="editFromDetails" class="px-3 py-1 text-xs rounded-lg bg-blue-600 text-white hover:bg-blue-700">Modifier</button>
                        <button x-show="situationPaiement?.peut_recevoir_paiement" @click="nouveauPaiementDepuisContrat" class="px-3 py-1 text-xs rounded-lg bg-green-600 text-white hover:bg-green-700 flex items-center gap-1"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>Paiement</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODALE PAIEMENT DOCUMENT ACD -->
        <div x-show="showDocumentPaymentModal" x-cloak @click.away="closeDocumentPaymentModal" @keydown.escape.window="closeDocumentPaymentModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div @click.stop class="relative w-full max-w-md rounded-xl bg-white shadow-2xl dark:bg-gray-900 mx-auto">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4"><h3 class="text-lg font-semibold text-gray-900 dark:text-white">Paiement - <span x-text="selectedDocument?.document_type_nom"></span></h3><button @click="closeDocumentPaymentModal" class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button></div>
                    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3 mb-4"><div class="flex justify-between text-sm"><span class="text-gray-500">Montant total :</span><span class="font-semibold" x-text="selectedDocument?.montant_formatted"></span></div><div class="flex justify-between text-sm mt-1"><span class="text-gray-500">Déjà payé :</span><span class="font-semibold text-green-600" x-text="formatPrice(selectedDocument?.paiements_somme_montant || 0)"></span></div><div class="flex justify-between text-sm mt-1 pt-2 border-t"><span class="font-medium">Reste :</span><span class="font-bold text-orange-600" x-text="formatPrice((selectedDocument?.montant || 0) - (selectedDocument?.paiements_somme_montant || 0))"></span></div></div>
                    <form @submit.prevent="submitDocumentPayment"><div class="space-y-4"><div><label class="mb-1 block text-sm font-medium">Montant *</label><input type="number" x-model="documentPaymentForm.montant" step="1000" required class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"></div><div><label class="mb-1 block text-sm font-medium">Date *</label><input type="date" x-model="documentPaymentForm.date_paiement" required class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"></div><div><label class="mb-1 block text-sm font-medium">Mode *</label><select x-model="documentPaymentForm.mode_paiement" required class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"><option value="">Sélectionnez</option><option value="ESPECES">Espèces</option><option value="VIREMENT">Virement</option><option value="CHEQUE">Chèque</option><option value="MOBILE_MONEY">Mobile Money</option></select></div><div x-show="documentPaymentForm.mode_paiement === 'VIREMENT' || documentPaymentForm.mode_paiement === 'CHEQUE'"><label class="mb-1 block text-sm font-medium">Banque</label><select x-model="documentPaymentForm.banque_paiement" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"><option value="">Sélectionnez</option><option value="SGBCI">SGBCI</option><option value="ECOBANK">Ecobank</option><option value="NSIA">NSIA</option><option value="ORABANK">Orabank</option><option value="BICICI">BICICI</option></select></div><div><label class="mb-1 block text-sm font-medium">Notes</label><textarea x-model="documentPaymentForm.notes" rows="2" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white" placeholder="Optionnel"></textarea></div><div x-show="documentPaymentErrors.length" class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800"><template x-for="error in documentPaymentErrors" :key="error"><p x-text="error"></p></template></div><div class="flex justify-end gap-3 pt-4 border-t"><button type="button" @click="closeDocumentPaymentModal" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Annuler</button><button type="submit" :disabled="isProcessingDocumentPayment" class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700 disabled:opacity-50"><span x-show="!isProcessingDocumentPayment">Payer</span><span x-show="isProcessingDocumentPayment">Traitement...</span></button></div></div></form>
                </div>
            </div>
        </div>

        <!-- MODALE UPLOAD FICHIER DOCUMENT -->
        <div x-show="showUploadModal" x-cloak @click.away="closeUploadModal" @keydown.escape.window="closeUploadModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div @click.stop class="relative w-full max-w-md rounded-xl bg-white shadow-2xl dark:bg-gray-900 mx-auto">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4"><h3 class="text-lg font-semibold text-gray-900 dark:text-white">Upload - <span x-text="selectedDocument?.document_type_nom"></span></h3><button @click="closeUploadModal" class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button></div>
                    <form @submit.prevent="submitUpload"><div class="space-y-4"><div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center"><input type="file" @change="handleFileSelect" accept=".pdf,.jpg,.jpeg,.png" class="hidden" id="file-upload"><label for="file-upload" class="cursor-pointer"><svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg><p class="text-sm text-gray-600">Cliquez pour sélectionner un fichier</p><p class="text-xs text-gray-500 mt-1">PDF, JPG, JPEG, PNG (max 5MB)</p></label></div><div x-show="uploadFile" class="text-sm text-green-600 text-center"><span x-text="uploadFile?.name"></span></div><div class="flex justify-end gap-3 pt-4"><button type="button" @click="closeUploadModal" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Annuler</button><button type="submit" :disabled="!uploadFile || uploadingFile" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"><span x-show="!uploadingFile">Uploader</span><span x-show="uploadingFile">Upload...</span></button></div></div></form>
                </div>
            </div>
        </div>

        <!-- Modales de confirmation -->
        <div x-show="showEditConfirmModal" x-cloak @click.away="closeEditConfirmModal" @keydown.escape.window="closeEditConfirmModal" class="fixed inset-0 z-40 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div @click.stop class="relative w-full max-w-md rounded-xl bg-white shadow-2xl dark:bg-gray-900 mx-auto"><div class="p-6 text-center"><div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-blue-100"><svg class="h-7 w-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg></div><h3 class="mb-2 text-lg font-semibold">Confirmation</h3><p class="text-sm text-gray-500">Modifier le contrat <span class="font-semibold" x-text="contratToEdit?.reference_contrat"></span> ?</p></div><div class="flex justify-center gap-3 border-t px-6 py-4"><button @click="closeEditConfirmModal" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium hover:bg-gray-50">Annuler</button><button @click="confirmEditContrat" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">Continuer</button></div></div>
        </div>

        <div x-show="showDeleteModal" x-cloak @click.away="closeDeleteModal" @keydown.escape.window="closeDeleteModal" class="fixed inset-0 z-40 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div @click.stop class="relative w-full max-w-md rounded-xl bg-white shadow-2xl dark:bg-gray-900 mx-auto"><div class="p-6 text-center"><div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-red-100"><svg class="h-7 w-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></div><h3 class="mb-2 text-lg font-semibold">Confirmation</h3><p class="text-sm text-gray-500">Supprimer le contrat <span class="font-semibold" x-text="contratToDelete?.reference_contrat"></span> ?</p><p class="mt-2 text-xs text-red-500">Action irréversible.</p></div><div class="flex justify-center gap-3 border-t px-6 py-4"><button @click="closeDeleteModal" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium hover:bg-gray-50">Annuler</button><button @click="confirmDeleteContrat" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">Supprimer</button></div></div>
        </div>

        <div x-show="showHasPaymentsDialog" x-cloak @click.away="closeHasPaymentsDialog" @keydown.escape.window="closeHasPaymentsDialog" class="fixed inset-0 z-40 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div @click.stop class="relative w-full max-w-lg rounded-xl bg-white shadow-2xl dark:bg-gray-900 mx-auto"><div class="p-6"><div class="flex items-start gap-4"><div class="flex-shrink-0"><div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-100"><svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg></div></div><div class="flex-1"><h3 class="text-lg font-semibold mb-2">Contrat avec paiements</h3><p class="text-sm text-gray-600 mb-4">Ce contrat possède <span x-text="contratToAdminDelete?.nombre_paiements || 0"></span> paiement(s). La suppression nécessite un mot de passe administrateur.</p><div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3"><p class="text-sm text-yellow-800">Voulez-vous supprimer en tant qu'administrateur ?</p></div></div></div></div><div class="flex justify-end gap-3 border-t px-6 py-4"><button @click="closeHasPaymentsDialog" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium hover:bg-gray-50">Annuler</button><button @click="confirmAdminDeleteFromDialog" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">Admin</button></div></div>
        </div>

        <div x-show="showAdminPasswordModal" x-cloak @click.away="closeAdminPasswordModal" @keydown.escape.window="closeAdminPasswordModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div @click.stop class="relative w-full max-w-md rounded-xl bg-white shadow-2xl dark:bg-gray-900 mx-auto"><div class="p-6"><div class="text-center mb-4"><div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-purple-100"><svg class="h-7 w-7 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg></div><h3 class="text-lg font-semibold">Authentification</h3><p class="mt-2 text-sm text-gray-500">Mot de passe admin pour supprimer <span x-text="contratToAdminDelete?.reference_contrat"></span></p></div><form @submit.prevent="submitAdminDeleteContrat"><div x-show="adminPasswordError" class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800"><p x-text="adminPasswordError"></p></div><div><label class="mb-1 block text-sm font-medium">Mot de passe *</label><div class="relative"><input :type="showAdminPassword ? 'text' : 'password'" x-model="adminPassword" required class="w-full rounded-lg border border-gray-300 px-3 py-2 pr-10 focus:border-purple-500 focus:outline-none focus:ring-1 focus:ring-purple-500"><button type="button" @click="showAdminPassword = !showAdminPassword" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400"><svg x-show="!showAdminPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg><svg x-show="showAdminPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243a9.97 9.97 0 01-4.243-4.243M21 12a9.97 9.97 0 01-3.029 5.543M3 3l18 18"></path></svg></button></div></div><div class="flex justify-end gap-3 mt-6 pt-4 border-t"><button type="button" @click="closeAdminPasswordModal" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium hover:bg-gray-50">Annuler</button><button type="submit" class="rounded-lg bg-purple-600 px-4 py-2 text-sm font-medium text-white hover:bg-purple-700">Confirmer</button></div></form></div></div>
        </div>

        <!-- Tableau principal -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Référence</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Client</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Lot</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date Contrat</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Montant Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Reste à payer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Paiements</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Statut</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <template x-for="item in paginatedData" :key="item.id_contrat">
                        <tr @click="selectRow(item.id_contrat)" :class="selectedRow === item.id_contrat ? 'bg-gray-200 dark:bg-gray-700 border-l-4 border-blue-500 shadow-sm' : 'hover:bg-gray-50 dark:hover:bg-gray-700/50'" class="cursor-pointer transition-all duration-200">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white" x-text="item.reference_contrat"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white" x-text="item.client_nom"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400" x-text="item.lot_numero"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400" x-text="item.date_contrat_formatted"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400" x-text="item.montant_total_formatted"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" :class="item.reste_a_payer_raw > 0 ? 'text-orange-600 dark:text-orange-400' : 'text-green-600 dark:text-green-400'" x-text="item.reste_a_payer_formatted || '0 FCFA'"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"><span x-show="item.nombre_paiements > 0" class="text-blue-600 dark:text-blue-400 font-medium" x-text="item.nombre_paiements"></span><span x-show="!item.nombre_paiements || item.nombre_paiements === 0" class="text-gray-400">-</span></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400" x-text="item.type_paiement_label"></td>
                            <td class="px-6 py-4 whitespace-nowrap"><span x-text="item.statut_label" :class="{'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-200': item.statut === 'EN_COURS','bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-200': item.statut === 'SOLDE','bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-200': item.statut === 'RESILIE','bg-orange-100 text-orange-700 dark:bg-orange-900 dark:text-orange-200': item.statut === 'EN_RETARD'}" class="px-2 py-1 rounded-full text-xs font-semibold"></span></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button @click.stop="viewDetails(item)" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300 mr-3 transition-colors" title="Voir détails"><svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg></button>
                                <button @click.stop="openEditConfirmModal(item)" x-show="!item.est_solde && item.statut !== 'SOLDE'" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 mr-3 transition-colors" title="Modifier"><svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg></button>
                                <button @click.stop="openDeleteModal(item)" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 transition-colors" title="Supprimer"><svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="filteredData.length === 0">
                        <td colspan="10" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400"><svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg><p class="text-lg font-medium">Aucun contrat trouvé</p><p class="text-sm mt-1">Essayez de modifier vos filtres</p></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                <div class="text-sm text-gray-700 dark:text-gray-300">Affichage de <span class="font-medium" x-text="startIndex"></span> à <span class="font-medium" x-text="endIndex"></span> sur <span class="font-medium" x-text="filteredData.length"></span> résultats</div>
                <div class="flex gap-2">
                    <button @click="prevPage" :disabled="currentPage === 1" :class="currentPage === 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100 dark:hover:bg-gray-700'" class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded-md text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 transition-colors">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                        Précédent
                    </button>
                    <span class="px-3 py-1 text-sm text-gray-700 dark:text-gray-300">Page <span class="font-medium" x-text="currentPage"></span> / <span class="font-medium" x-text="totalPages"></span></span>
                    <button @click="nextPage" :disabled="currentPage === totalPages" :class="currentPage === totalPages ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100 dark:hover:bg-gray-700'" class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded-md text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 transition-colors">
                        Suivant
                        <svg class="w-4 h-4 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('dataTable', () => ({
        search: '', filtreStatut: '', filtreType: '', currentPage: 1, itemsPerPage: 10, selectedRow: null,
        showContratForm: false, showDetailsModal: false, selectedContrat: null, editingContrat: null,
        formErrors: [], clientsListe: [], lotsDisponibles: [], montantTotalDisplay: '',
        situationPaiement: null, historiquePaiements: [], echeances: [],
        echeancesStats: { total: 0, payees: 0, en_retard: 0 }, selectedLotInfo: null,
        fraisAcdListe: [], fraisAcdVille: '', totalFraisAcd: 0, totalFraisAcdFormatted: '',
        form: { id_client: '', id_lot: '', date_contrat: '', date_debut: '', montant_total: '', type_paiement: 'COMPTANT', duree_mois: 12, statut: 'EN_COURS', mode_paiement: '', banque_paiement: '' },
        items: [], showDeleteModal: false, contratToDelete: null, showEditConfirmModal: false, contratToEdit: null,
        showHasPaymentsDialog: false, contratToAdminDelete: null, showAdminPasswordModal: false, adminPassword: '', showAdminPassword: false, adminPasswordError: '',
        documentsAcD: [], documentsStats: { total: 0, payes: 0, disponibles: 0, remis: 0, montant_total: 0, montant_paye: 0 },
        showDocumentPaymentModal: false, selectedDocument: null,
        documentPaymentForm: { montant: 0, date_paiement: new Date().toISOString().split('T')[0], mode_paiement: '', banque_paiement: '', statut: 'VALIDE', notes: '' },
        documentPaymentErrors: [], isProcessingDocumentPayment: false, showUploadModal: false, uploadFile: null, uploadingFile: false,

        get stats() { const total = this.items.length; const soldes = this.items.filter(i => i.statut === 'SOLDE').length; const en_retard = this.items.filter(i => i.statut === 'EN_RETARD').length; const en_cours = this.items.filter(i => i.statut === 'EN_COURS').length; return { total, soldes, en_retard, en_cours }; },
        get filteredData() { let data = [...this.items]; if (this.filtreStatut) data = data.filter(i => i.statut === this.filtreStatut); if (this.filtreType) data = data.filter(i => i.type_paiement === this.filtreType); if (this.search) { const s = this.search.toLowerCase(); data = data.filter(item => (item.reference_contrat && item.reference_contrat.toLowerCase().includes(s)) || (item.client_nom && item.client_nom.toLowerCase().includes(s)) || (item.lot_numero && item.lot_numero.toLowerCase().includes(s)) || (item.statut_label && item.statut_label.toLowerCase().includes(s))); } return data; },
        get totalPages() { return Math.ceil(this.filteredData.length / this.itemsPerPage) || 1; },
        get paginatedData() { const s = (this.currentPage - 1) * this.itemsPerPage; return this.filteredData.slice(s, s + this.itemsPerPage); },
        get startIndex() { return this.filteredData.length === 0 ? 0 : (this.currentPage - 1) * this.itemsPerPage + 1; },
        get endIndex() { return Math.min(this.currentPage * this.itemsPerPage, this.filteredData.length); },
        nextPage() { if (this.currentPage < this.totalPages) { this.currentPage++; this.selectedRow = null; } },
        prevPage() { if (this.currentPage > 1) { this.currentPage--; this.selectedRow = null; } },
        selectRow(id) { this.selectedRow = id; },
        getStatutLabel(s) { const l = {'EN_COURS':'En cours','SOLDE':'Soldé','RESILIE':'Résilié','EN_RETARD':'En retard'}; return l[s] || s; },
        getTypePaiementLabel(t) { const l = {'COMPTANT':'Comptant','ECHELONNE':'Échelonné'}; return l[t] || t; },
        getModePaiementLabel(m) { const l = {'ESPECES':'Espèces','VIREMENT':'Virement bancaire','CHEQUE':'Chèque','MOBILE_MONEY':'Mobile Money'}; return l[m] || m || '-'; },
        getBanquePaiementLabel(b) { const l = {'SGBCI':'SGBCI','ECOBANK':'Ecobank CI','NSIA':'NSIA Banque','ORABANK':'Orabank','BICICI':'BICICI','BRIDGE':'Bridge Bank','CORIS':'Coris Bank','BANK_OF_AFRICA':'Bank of Africa'}; return l[b] || b || '-'; },
        getStatutPaiementLabel(s) { const l = {'EN_ATTENTE':'En attente','VALIDE':'Validé','REJETE':'Rejeté','ANNULE':'Annulé'}; return l[s] || s; },
        formatPrice(p) { if (p === null || p === undefined || p === '') return '0 FCFA'; const n = parseFloat(p); if (isNaN(n) || n === 0) return '0 FCFA'; return new Intl.NumberFormat('fr-FR').format(n) + ' FCFA'; },
        formatDate(d) { if (!d) return '-'; const dt = new Date(d); return isNaN(dt.getTime()) ? d : dt.toLocaleDateString('fr-FR'); },
        getLotDisplayText() { if (!this.editingContrat) return ''; const lot = this.lotsDisponibles.find(l => l.id_lot == this.form.id_lot); if (lot) return `${lot.numero_lot} - ${lot.lotissement?.nom || 'Sans lotissement'}`; if (this.editingContrat) return `${this.editingContrat.lot_numero} - ${this.editingContrat.lot_ilot || ''}`; return 'Lot non trouvé'; },
        updateMontantTotalFromDisplay() { let raw = this.montantTotalDisplay.replace(/\s/g, '').replace('FCFA', '').trim(); let n = parseFloat(raw); if (isNaN(n)) n = 0; this.form.montant_total = n; },
        
        async loadFraisAcdByLot(lotId) {
            if (!lotId) { this.fraisAcdListe = []; this.fraisAcdVille = ''; this.totalFraisAcd = 0; this.totalFraisAcdFormatted = ''; return; }
            try {
                const response = await fetch(`/api/contrats/frais-acd/lot/${lotId}`);
                if (response.ok) {
                    const data = await response.json();
                    if (data.success) {
                        this.fraisAcdListe = data.frais || [];
                        this.fraisAcdVille = data.ville || '';
                        this.totalFraisAcd = data.total || 0;
                        this.totalFraisAcdFormatted = data.total_formatted || '0 FCFA';
                    } else {
                        this.fraisAcdListe = [];
                        this.fraisAcdVille = '';
                        this.totalFraisAcd = 0;
                        this.totalFraisAcdFormatted = '';
                    }
                }
            } catch (error) {
                console.error('Erreur chargement frais ACD:', error);
                this.fraisAcdListe = [];
                this.fraisAcdVille = '';
                this.totalFraisAcd = 0;
                this.totalFraisAcdFormatted = '';
            }
        },
        
        async loadDocumentsAcD(idContrat) { try { const response = await fetch(`/api/contrat-documents/contrat/${idContrat}`); if (response.ok) { const data = await response.json(); if (data.success) { this.documentsAcD = data.documents || []; this.documentsStats = data.resume || { total: 0, payes: 0, disponibles: 0, remis: 0, montant_total: 0, montant_paye: 0 }; } } } catch (error) { console.error('Erreur chargement documents:', error); } },
        async marquerDocumentDisponible(documentId) { if (!confirm('Marquer ce document comme disponible ?')) return; try { const response = await fetch(`/api/contrat-documents/${documentId}/disponible`, { method: 'PUT', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '', 'Accept': 'application/json' } }); const data = await response.json(); if (data.success) { await this.loadDocumentsAcD(this.selectedContrat?.id_contrat); alert('Document marqué comme disponible'); } else { alert(data.message || 'Erreur'); } } catch (error) { alert('Erreur de connexion'); } },
        async marquerDocumentRemis(documentId) { if (!confirm('Marquer ce document comme remis au client ?')) return; try { const response = await fetch(`/api/contrat-documents/${documentId}/remis`, { method: 'PUT', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '', 'Accept': 'application/json' } }); const data = await response.json(); if (data.success) { await this.loadDocumentsAcD(this.selectedContrat?.id_contrat); alert('Document marqué comme remis au client'); } else { alert(data.message || 'Erreur'); } } catch (error) { alert('Erreur de connexion'); } },
        openDocumentPaymentModal(document) { this.selectedDocument = document; const resteAPayer = document.montant - (document.paiements_somme_montant || 0); this.documentPaymentForm = { montant: resteAPayer, date_paiement: new Date().toISOString().split('T')[0], mode_paiement: '', banque_paiement: '', statut: 'VALIDE', notes: `Paiement des frais pour ${document.document_type_nom}` }; this.documentPaymentErrors = []; this.showDocumentPaymentModal = true; },
        closeDocumentPaymentModal() { this.showDocumentPaymentModal = false; this.selectedDocument = null; this.documentPaymentForm = { montant: 0, date_paiement: new Date().toISOString().split('T')[0], mode_paiement: '', banque_paiement: '', statut: 'VALIDE', notes: '' }; this.documentPaymentErrors = []; this.isProcessingDocumentPayment = false; },
        async submitDocumentPayment() { this.documentPaymentErrors = []; if (!this.documentPaymentForm.mode_paiement) { this.documentPaymentErrors.push('Le mode de paiement est requis.'); return; } if ((this.documentPaymentForm.mode_paiement === 'VIREMENT' || this.documentPaymentForm.mode_paiement === 'CHEQUE') && !this.documentPaymentForm.banque_paiement) { this.documentPaymentErrors.push('La banque est requise pour ce mode de paiement.'); return; } if (this.documentPaymentForm.montant <= 0) { this.documentPaymentErrors.push('Le montant doit être supérieur à 0.'); return; } this.isProcessingDocumentPayment = true; try { const response = await fetch('/api/paiements/frais-document', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '', 'Accept': 'application/json' }, body: JSON.stringify({ id_contrat: this.selectedContrat.id_contrat, id_contrat_document: this.selectedDocument.id_contrat_document, date_paiement: this.documentPaymentForm.date_paiement, montant: this.documentPaymentForm.montant, mode_paiement: this.documentPaymentForm.mode_paiement, banque_paiement: this.documentPaymentForm.banque_paiement, statut: this.documentPaymentForm.statut, notes: this.documentPaymentForm.notes }) }); const data = await response.json(); if (data.success) { await this.loadDocumentsAcD(this.selectedContrat?.id_contrat); await this.loadSituationPaiement(this.selectedContrat?.id_contrat); this.closeDocumentPaymentModal(); alert('Paiement enregistré avec succès'); window.dispatchEvent(new CustomEvent('paiement-updated')); } else { this.documentPaymentErrors.push(data.message || 'Erreur lors du paiement'); } } catch (error) { this.documentPaymentErrors.push('Erreur de connexion au serveur'); } finally { this.isProcessingDocumentPayment = false; } },
        openUploadModal(document) { this.selectedDocument = document; this.uploadFile = null; this.showUploadModal = true; },
        closeUploadModal() { this.showUploadModal = false; this.selectedDocument = null; this.uploadFile = null; this.uploadingFile = false; },
        handleFileSelect(event) { this.uploadFile = event.target.files[0]; },
        async submitUpload() { if (!this.uploadFile) { alert('Veuillez sélectionner un fichier'); return; } this.uploadingFile = true; const formData = new FormData(); formData.append('fichier', this.uploadFile); try { const response = await fetch(`/api/contrat-documents/${this.selectedDocument.id_contrat_document}/upload`, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '' }, body: formData }); const data = await response.json(); if (data.success) { await this.loadDocumentsAcD(this.selectedContrat?.id_contrat); this.closeUploadModal(); alert('Fichier uploadé avec succès'); } else { alert(data.message || 'Erreur lors de l\'upload'); } } catch (error) { alert('Erreur de connexion'); } finally { this.uploadingFile = false; } },
        openDeleteModal(item) { if ((item.nombre_paiements && item.nombre_paiements > 0) || item.statut === 'SOLDE' || item.est_solde) { this.contratToAdminDelete = item; this.showHasPaymentsDialog = true; return; } this.contratToDelete = item; this.showDeleteModal = true; },
        closeDeleteModal() { this.showDeleteModal = false; this.contratToDelete = null; },
        openEditConfirmModal(item) { if (item.est_solde || item.statut === 'SOLDE') { alert('Ce contrat est soldé et ne peut pas être modifié.'); return; } this.contratToEdit = item; this.showEditConfirmModal = true; },
        closeEditConfirmModal() { this.showEditConfirmModal = false; this.contratToEdit = null; },
        closeHasPaymentsDialog() { this.showHasPaymentsDialog = false; this.contratToAdminDelete = null; },
        confirmAdminDeleteFromDialog() { this.showHasPaymentsDialog = false; this.adminPassword = ''; this.adminPasswordError = ''; this.showAdminPassword = false; this.showAdminPasswordModal = true; },
        closeAdminPasswordModal() { this.showAdminPasswordModal = false; this.adminPassword = ''; this.adminPasswordError = ''; this.showAdminPassword = false; this.contratToAdminDelete = null; },
        async submitAdminDeleteContrat() { this.adminPasswordError = ''; if (!this.adminPassword) { this.adminPasswordError = 'Le mot de passe administrateur est requis.'; return; } const id = this.contratToAdminDelete?.id_contrat; if (!id) { this.adminPasswordError = 'Erreur: contrat introuvable.'; return; } try { const r = await fetch(`/api/contrats/${id}/admin-delete`, { method: 'DELETE', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '', 'Accept': 'application/json' }, body: JSON.stringify({ admin_password: this.adminPassword }) }); const d = await r.json(); if (d.success) { this.items = this.items.filter(i => i.id_contrat !== id); this.closeAdminPasswordModal(); alert(d.message || 'Contrat supprimé avec succès'); window.dispatchEvent(new CustomEvent('contrat-updated')); window.dispatchEvent(new CustomEvent('paiement-updated')); } else { this.adminPasswordError = d.message || 'Mot de passe administrateur incorrect.'; } } catch(e) { this.adminPasswordError = 'Erreur de connexion au serveur.'; } },
        confirmEditContrat() { const item = this.contratToEdit; this.closeEditConfirmModal(); if (item) { this.editItem(item); } },
        async confirmDeleteContrat() { const id = this.contratToDelete?.id_contrat; this.closeDeleteModal(); if (!id) return; try { const r = await fetch(`/api/contrats/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '', 'Accept': 'application/json' } }); const d = await r.json(); if (d.success) { this.items = this.items.filter(i => i.id_contrat !== id); alert('Contrat supprimé avec succès'); window.dispatchEvent(new CustomEvent('contrat-updated')); window.dispatchEvent(new CustomEvent('paiement-updated')); } else { alert(d.message || 'Erreur lors de la suppression'); } } catch(e) { alert('Erreur de connexion'); } },
        async init() { await this.loadClients(); await this.loadLotsDisponibles(); await this.loadContratsFromDB(); window.addEventListener('paiement-updated', async () => { await this.loadContratsFromDB(); if (this.showDetailsModal && this.selectedContrat) { await this.loadSituationPaiement(this.selectedContrat.id_contrat); await this.loadDocumentsAcD(this.selectedContrat.id_contrat); } }); window.addEventListener('paiement-form-closed', async () => { await this.loadContratsFromDB(); if (this.showDetailsModal && this.selectedContrat) { await this.loadSituationPaiement(this.selectedContrat.id_contrat); await this.loadDocumentsAcD(this.selectedContrat.id_contrat); } }); },
        async loadClients() { try { const r = await fetch('/api/contrats/clients/liste'); if (r.ok) this.clientsListe = await r.json(); } catch(e) {} },
        async loadLotsDisponibles() { try { const r = await fetch('/api/contrats/lots/disponibles'); if (r.ok) this.lotsDisponibles = await r.json(); } catch(e) {} },
        async updateMontantFromLot(id) { if (id) { const lot = this.lotsDisponibles.find(l => l.id_lot == id); if (lot) { this.selectedLotInfo = { prix_comptant: parseFloat(lot.prix_comptant) || 0, prix_echelonne: parseFloat(lot.prix_echelonne) || 0 }; this.updateMontantByTypePaiement(); await this.loadFraisAcdByLot(id); } } else { this.fraisAcdListe = []; this.fraisAcdVille = ''; this.totalFraisAcd = 0; } },
        updateMontantByTypePaiement() { if (!this.selectedLotInfo) return; const prix = this.form.type_paiement === 'COMPTANT' ? this.selectedLotInfo.prix_comptant : this.selectedLotInfo.prix_echelonne; this.montantTotalDisplay = this.formatPrice(prix); this.form.montant_total = prix; },
        handleTypePaiementChange() { if (this.form.type_paiement === 'ECHELONNE') { this.form.mode_paiement = ''; this.form.banque_paiement = ''; if (!this.form.duree_mois) this.form.duree_mois = 12; } this.updateMontantByTypePaiement(); },
        newContrat() { this.editingContrat = null; this.selectedLotInfo = null; this.fraisAcdListe = []; this.fraisAcdVille = ''; this.totalFraisAcd = 0; const today = new Date().toISOString().split('T')[0]; this.form = { id_client: '', id_lot: '', date_contrat: today, date_debut: today, montant_total: '', type_paiement: 'COMPTANT', duree_mois: 12, statut: 'EN_COURS', mode_paiement: '', banque_paiement: '' }; this.montantTotalDisplay = ''; this.formErrors = []; this.showContratForm = true; },
        editItem(item) { if (item.est_solde || item.statut === 'SOLDE') { alert('Ce contrat est soldé.'); return; } this.editingContrat = item; this.selectedLotInfo = null; this.form = { id_client: item.id_client, id_lot: item.id_lot, date_contrat: item.date_contrat, date_debut: item.date_debut || item.date_contrat, montant_total: item.montant_total, type_paiement: item.type_paiement, duree_mois: item.duree_mois || 12, statut: item.statut, mode_paiement: item.mode_paiement || '', banque_paiement: item.banque_paiement || '' }; this.montantTotalDisplay = this.formatPrice(item.montant_total); this.formErrors = []; this.showContratForm = true; },
        closeContratForm() { this.showContratForm = false; this.editingContrat = null; this.selectedLotInfo = null; this.formErrors = []; this.montantTotalDisplay = ''; this.fraisAcdListe = []; this.fraisAcdVille = ''; this.totalFraisAcd = 0; },
        closeDetailsModal() { this.showDetailsModal = false; this.selectedContrat = null; this.situationPaiement = null; this.historiquePaiements = []; this.echeances = []; this.echeancesStats = { total: 0, payees: 0, en_retard: 0 }; this.documentsAcD = []; this.documentsStats = { total: 0, payes: 0, disponibles: 0, remis: 0, montant_total: 0, montant_paye: 0 }; },
        async viewDetails(item) { this.selectedContrat = item; this.showDetailsModal = true; await this.loadSituationPaiement(item.id_contrat); await this.loadDocumentsAcD(item.id_contrat); },
        async loadSituationPaiement(id) { try { const r = await fetch(`/api/contrats/${id}/situation-paiement`); if (!r.ok) return; const d = await r.json(); if (d.success) { const estSolde = d.contrat?.statut === 'SOLDE' || d.contrat?.est_solde === true; if (d.contrat) this.selectedContrat = { ...this.selectedContrat, ...d.contrat, montant_total_formatted: d.contrat.montant_total_formatted || this.formatPrice(d.contrat.montant_total), client_nom: d.contrat.client_nom || this.selectedContrat?.client_nom || '-', lot_numero: d.contrat.lot_numero || this.selectedContrat?.lot_numero || '-', duree_mois: d.contrat.duree_mois || this.selectedContrat?.duree_mois, est_solde: estSolde }; this.situationPaiement = { montant_total: d.situation?.montant_total || '0 FCFA', montant_paye: estSolde ? (d.situation?.montant_total || '0 FCFA') : (d.situation?.montant_paye || '0 FCFA'), montant_paye_raw: estSolde ? (d.situation?.montant_total_raw || 0) : (d.situation?.montant_paye_raw || 0), reste_a_payer: estSolde ? '0 FCFA' : (d.situation?.reste_a_payer || '0 FCFA'), reste_a_payer_raw: estSolde ? 0 : (d.situation?.reste_a_payer_raw || 0), progression: estSolde ? 100 : (d.situation?.progression || 0), peut_recevoir_paiement: !estSolde && (d.situation?.peut_recevoir_paiement || false) }; this.historiquePaiements = Array.isArray(d.paiements) ? d.paiements : []; if (d.echeances && Array.isArray(d.echeances)) { this.echeances = d.echeances; this.echeancesStats = { total: d.echeances.length, payees: d.echeances.filter(e => e.est_paye).length, en_retard: d.echeances.filter(e => e.est_en_retard).length }; } else { this.echeances = []; this.echeancesStats = { total: 0, payees: 0, en_retard: 0 }; } } } catch(e) {} },
        editFromDetails() { const item = this.selectedContrat; if (item.est_solde || item.statut === 'SOLDE') { alert('Contrat soldé.'); return; } this.closeDetailsModal(); this.openEditConfirmModal(item); },
        nouveauPaiementDepuisContrat() { const c = this.selectedContrat; this.closeDetailsModal(); window.dispatchEvent(new CustomEvent('open-paiement-form', { detail: { id_contrat: c.id_contrat, reference_contrat: c.reference_contrat, client_nom: c.client_nom, reste_a_payer: this.situationPaiement?.reste_a_payer_raw || 0 } })); },
        async imprimerRecap() { if (!this.selectedContrat) return; try { const r = await fetch(`/api/contrats/${this.selectedContrat.id_contrat}/imprimer`); if (r.ok) { const d = await r.json(); this.genererPDF(d); } else alert('Erreur impression'); } catch(e) { alert('Erreur'); } },
        genererPDF(data) { const contrat = data.contrat, situation = data.situation, paiements = data.paiements || [], echeances = data.echeances || [], documents = this.documentsAcD || []; const pw = window.open('', '_blank', 'width=900,height=700'); const fp = (p) => { if (!p && p !== 0) return '0 FCFA'; const n = parseFloat(p); return isNaN(n) || n === 0 ? '0 FCFA' : new Intl.NumberFormat('fr-FR').format(n) + ' FCFA'; }; const gsl = (s) => { const l = {'A_PAYER':'À payer','PAYE':'Payée','EN_RETARD':'En retard'}; return l[s] || s; }; let h = `<!DOCTYPE html><html><head><title>Contrat ${contrat.reference_contrat}</title><meta charset="UTF-8"><style>*{margin:0;padding:0}body{font-family:Arial,sans-serif;margin:30px}h1{color:#1e40af;font-size:26px}h2{color:#374151;font-size:18px;margin:25px 0 15px;border-bottom:2px solid #3b82f6;padding-bottom:8px}.grid{display:grid;grid-template-columns:repeat(2,1fr);gap:15px;background:#f9fafb;padding:20px;border-radius:10px}.label{font-size:12px;color:#6b7280;text-transform:uppercase}.value{font-size:16px;font-weight:600;color:#111827}.amount-box{background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;padding:20px;border-radius:10px}.amount-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;text-align:center}.amount-item h4{font-size:13px;font-weight:400;margin-bottom:5px}.amount-item .number{font-size:24px;font-weight:700}.progress-bar{width:100%;height:10px;background:rgba(255,255,255,.3);border-radius:5px;margin:10px 0}.progress-fill{height:100%;background:#10b981;border-radius:5px}table{width:100%;border-collapse:collapse;margin-top:15px}th{background:#f3f4f6;padding:12px;text-align:left;font-size:13px;font-weight:600}td{padding:12px;border-bottom:1px solid #e5e7eb}.status-badge{display:inline-block;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600}.status-solde{background:#d1fae5;color:#065f46}.status-encours{background:#fef3c7;color:#92400e}.status-retard{background:#fee2e2;color:#991b1b}.status-paye{background:#d1fae5;color:#065f46}.status-apayer{background:#fef3c7;color:#92400e}.footer{margin-top:40px;text-align:center;font-size:12px;color:#9ca3af;border-top:1px solid #e5e7eb;padding-top:20px}.print-btn{background:#2563eb;color:#fff;padding:12px 24px;border:none;border-radius:8px;cursor:pointer;margin-bottom:20px;font-size:16px}@media print{body{margin:15px}.print-btn{display:none}.amount-box{-webkit-print-color-adjust:exact;print-color-adjust:exact}}</style></head><body><button class="print-btn" onclick="window.print()">Imprimer</button><h1>Récapitulatif Contrat ${contrat.reference_contrat}</h1><h2>Informations Générales</h2><div class="grid"><div><span class="label">Client : </span><span class="value">${contrat.client?.nom_prenoms||'-'}</span></div><div><span class="label">Lot : </span><span class="value">${contrat.lot?.numero_lot||'-'}</span></div><div><span class="label">Date : </span><span class="value">${this.formatDate(contrat.date_contrat)}</span></div><div><span class="label">Type : </span><span class="value">${this.getTypePaiementLabel(contrat.type_paiement)}</span></div><div><span class="label">Statut : </span><span class="value"><span class="status-badge ${contrat.statut==='SOLDE'?'status-solde':(contrat.statut==='EN_RETARD'?'status-retard':'status-encours')}">${this.getStatutLabel(contrat.statut)}</span></span></div></div><h2>Situation des Paiements</h2><div class="amount-box"><div class="amount-grid"><div class="amount-item"><h4>Montant total</h4><div class="number">${situation.montant_total}</div></div><div class="amount-item"><h4>Déjà payé</h4><div class="number" style="color:#10b981">${situation.montant_paye}</div></div><div class="amount-item"><h4>Reste à payer</h4><div class="number" style="color:${contrat.reste_a_payer>0?'#f59e0b':'#10b981'}">${situation.reste_a_payer}</div></div></div><div style="margin-top:15px"><div style="display:flex;justify-content:space-between;font-size:13px"><span>Progression</span><span>${situation.progression}%</span></div><div class="progress-bar"><div class="progress-fill" style="width:${situation.progression}%"></div></div></div></div>`; if (contrat.type_paiement==='ECHELONNE' && echeances.length>0) { h+=`<h2>Échéances</h2><table><thead><tr><th>N°</th><th>Date</th><th>Montant prévu</th><th>Payé</th><th>Reste</th><th>Statut</th></tr></thead><tbody>`; echeances.forEach(e=>{const mp=parseFloat(e.montant_prevu)||0,mpe=parseFloat(e.montant_paye)||0,r=Math.max(0,mp-mpe);h+=`<tr><td class="px-2 py-1">${e.numero||'-'}</td><td class="px-2 py-1">${e.date_echeance||'-'}</td><td class="px-2 py-1 text-right">${fp(mp)}</td><td class="px-2 py-1 text-right">${fp(mpe)}</td><td class="px-2 py-1 text-right">${fp(r)}</td><td class="px-2 py-1 text-center"><span class="status-badge ${e.statut==='PAYE'?'status-paye':(e.statut==='EN_RETARD'?'status-retard':'status-apayer')}">${gsl(e.statut)}</span></td></tr>`}); h+=`</tbody></table>`; } if (documents.length > 0) { h+=`<h2>Documents ACD</h2><table><thead><tr><th>Document</th><th>Montant</th><th>Statut paiement</th><th>Statut document</th></tr></thead><tbody>`; documents.forEach(doc=>{h+=`<tr><td class="px-2 py-1">${doc.document_type_nom || '-'}</td><td class="px-2 py-1 text-right">${doc.montant_formatted || '-'}</td><td class="px-2 py-1 text-center">${doc.statut_paiement_label || '-'}</td><td class="px-2 py-1 text-center">${doc.statut_document_label || '-'}</td></tr>`}); h+=`</tbody></table>`; } h+=`<h2>Historique des Paiements</h2><table><thead><tr><th>Date</th><th>Référence</th><th>Type</th><th>Mode</th><th>Montant</th><th>Statut</th></tr></thead><tbody>`; if (paiements.length>0) { paiements.forEach(p=>{h+=`<tr><td class="px-2 py-1">${this.formatDate(p.date_paiement)}</td><td class="px-2 py-1">${p.reference||'-'}</td><td class="px-2 py-1">${p.type_paiement_label || 'Mensualité'}</td><td class="px-2 py-1">${this.getModePaiementLabel(p.mode_paiement)}</td><td class="px-2 py-1 text-right">${this.formatPrice(p.montant)}</td><td class="px-2 py-1 text-center">${this.getStatutPaiementLabel(p.statut)}</td></tr>`}); } else h+=`<tr><td colspan="6" style="text-align:center;padding:30px">Aucun paiement</td></tr>`; h+=`</tbody></table><div class="footer">Document généré automatiquement - ${new Date().toLocaleString('fr-FR')}</div></body></html>`; pw.document.write(h); pw.document.close(); },
        
        async loadContratsFromDB() { 
            try { 
                const r = await fetch('/api/contrats'); 
                const d = await r.json(); 
                
                let contratsData = [];
                if (Array.isArray(d)) {
                    contratsData = d;
                } else if (d.success && Array.isArray(d.data)) {
                    contratsData = d.data;
                } else if (d.success && Array.isArray(d.contrats)) {
                    contratsData = d.contrats;
                } else if (d.data && Array.isArray(d.data)) {
                    contratsData = d.data;
                } else {
                    contratsData = [];
                }
                
                if (contratsData.length > 0) { 
                    this.items = contratsData.map(c => { 
                        const mt = parseFloat(c.montant_total) || 0; 
                        let rap = 0; 
                        if (c.statut === 'SOLDE') rap = 0; 
                        else if (c.reste_a_payer_calcule !== undefined && c.reste_a_payer_calcule !== null) rap = parseFloat(c.reste_a_payer_calcule); 
                        else if (c.reste_a_payer !== undefined && c.reste_a_payer !== null) rap = parseFloat(c.reste_a_payer); 
                        else { 
                            const mpe = parseFloat(c.montant_paye) || 0; 
                            rap = mt - mpe; 
                        } 
                        if (rap < 0) rap = 0; 
                        return { 
                            id_contrat: c.id_contrat, 
                            id_client: c.id_client, 
                            id_lot: c.id_lot, 
                            reference_contrat: c.reference_contrat, 
                            client_nom: c.client?.nom_prenoms || c.client_nom || '-', 
                            lot_numero: c.lot?.numero_lot || c.lot_numero || '-', 
                            lot_ilot: c.lot?.numero_ilot || '', 
                            date_contrat: c.date_contrat, 
                            date_contrat_formatted: this.formatDate(c.date_contrat), 
                            date_debut: c.date_debut, 
                            montant_total: mt, 
                            montant_total_formatted: this.formatPrice(mt), 
                            reste_a_payer_raw: rap, 
                            reste_a_payer_formatted: this.formatPrice(rap), 
                            nombre_paiements: c.nombre_paiements || 0, 
                            type_paiement: c.type_paiement, 
                            type_paiement_label: this.getTypePaiementLabel(c.type_paiement), 
                            statut: c.statut, 
                            statut_label: this.getStatutLabel(c.statut), 
                            est_solde: c.statut === 'SOLDE' || rap <= 0, 
                            duree_mois: c.duree_mois, 
                            mode_paiement: c.mode_paiement, 
                            banque_paiement: c.banque_paiement 
                        }; 
                    }); 
                } else { 
                    this.items = []; 
                }
                this.currentPage = 1;
            } catch(e) { 
                console.error('Erreur chargement contrats:', e);
                this.items = [];
            } 
        },
        
        async saveContrat() { 
            this.formErrors = []; 
            if (!this.form.id_client) this.formErrors.push('Le client est requis.'); 
            if (!this.form.id_lot) this.formErrors.push('Le lot est requis.'); 
            if (!this.form.date_contrat) this.formErrors.push('La date du contrat est requise.'); 
            if (!this.form.montant_total || parseFloat(this.form.montant_total) <= 0) this.formErrors.push('Le montant total doit être supérieur à 0.'); 
            if (this.form.type_paiement === 'COMPTANT') { 
                if (!this.form.mode_paiement) this.formErrors.push('Le mode de paiement est requis.'); 
                if ((this.form.mode_paiement === 'VIREMENT' || this.form.mode_paiement === 'CHEQUE') && !this.form.banque_paiement) this.formErrors.push('La banque est requise.'); 
            } 
            if (this.form.type_paiement === 'ECHELONNE' && (!this.form.duree_mois || this.form.duree_mois < 1)) this.formErrors.push('La durée en mois est requise (minimum 1).'); 
            if (this.formErrors.length) return; 
            
            const dts = { 
                id_client: this.form.id_client, 
                id_lot: this.form.id_lot, 
                date_contrat: this.form.date_contrat, 
                date_debut: this.form.date_debut || this.form.date_contrat, 
                montant_total: parseFloat(this.form.montant_total), 
                type_paiement: this.form.type_paiement, 
                duree_mois: this.form.type_paiement === 'ECHELONNE' ? parseInt(this.form.duree_mois) : null, 
                statut: this.form.statut 
            }; 
            
            if (this.form.type_paiement === 'COMPTANT') { 
                dts.mode_paiement = this.form.mode_paiement; 
                if (this.form.banque_paiement) dts.banque_paiement = this.form.banque_paiement; 
            } 
            
            const url = this.editingContrat ? `/api/contrats/${this.editingContrat.id_contrat}` : '/api/contrats'; 
            const method = this.editingContrat ? 'PUT' : 'POST'; 
            
            try { 
                const r = await fetch(url, { 
                    method, 
                    headers: { 
                        'Content-Type': 'application/json', 
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '', 
                        'Accept': 'application/json' 
                    }, 
                    body: JSON.stringify(dts) 
                }); 
                
                const d = await r.json(); 
                
                if (d.success) { 
                    await new Promise(resolve => setTimeout(resolve, 500)); 
                    await this.loadContratsFromDB(); 
                    await this.loadLotsDisponibles(); 
                    
                    // Si c'est une création, recharger les documents ACD pour ce contrat
                    if (!this.editingContrat && d.contrat && d.contrat.id_contrat) {
                        await this.loadDocumentsAcD(d.contrat.id_contrat);
                    }
                    
                    this.closeContratForm(); 
                    window.dispatchEvent(new CustomEvent('contrat-updated')); 
                    window.dispatchEvent(new CustomEvent('paiement-updated')); 
                    alert(this.editingContrat ? 'Contrat modifié avec succès.' : 'Contrat créé avec succès.'); 
                } else { 
                    alert(d.message || 'Erreur lors de l\'enregistrement'); 
                } 
            } catch(e) { 
                console.error('Erreur:', e);
                alert('Une erreur est survenue'); 
            } 
        },
        
        exportCSV() { const headers = ['Référence', 'Client', 'Lot', 'Date', 'Montant', 'Reste', 'Paiements', 'Type', 'Statut']; const rows = this.filteredData.map(i => [i.reference_contrat, i.client_nom, i.lot_numero, i.date_contrat_formatted, i.montant_total, i.reste_a_payer_raw, i.nombre_paiements || 0, i.type_paiement_label, i.statut_label]); const csv = [headers.join(','), ...rows.map(r => r.map(c => `"${c}"`).join(','))].join('\n'); const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' }); const link = document.createElement('a'); link.href = URL.createObjectURL(blob); link.download = `contrats_${new Date().toISOString().split('T')[0]}.csv`; link.click(); URL.revokeObjectURL(link.href); },
        exportExcel() { const headers = ['Référence', 'Client', 'Lot', 'Date', 'Montant', 'Reste', 'Paiements', 'Type', 'Statut']; const rows = this.filteredData.map(i => [i.reference_contrat, i.client_nom, i.lot_numero, i.date_contrat_formatted, i.montant_total, i.reste_a_payer_raw, i.nombre_paiements || 0, i.type_paiement_label, i.statut_label]); const excel = [headers.join('\t'), ...rows.map(r => r.join('\t'))].join('\n'); const blob = new Blob(['\uFEFF' + excel], { type: 'application/vnd.ms-excel' }); const link = document.createElement('a'); link.href = URL.createObjectURL(blob); link.download = `contrats_${new Date().toISOString().split('T')[0]}.xls`; link.click(); URL.revokeObjectURL(link.href); }
    }));
});
</script>

<style>
[x-cloak] { display: none !important; }
</style>
<div x-data="dataTable" x-init="init()">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
        <!-- Header avec recherche et boutons -->
        <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Tableau Récapitulatif des Paiements</h2>

                <div class="flex items-center gap-3 flex-wrap">
                    <!-- Filtre par statut -->
                    <select x-model="filtreStatut" @change="currentPage = 1"
                        class="w-40 rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">Tous les statuts</option>
                        <option value="VALIDE">Validés</option>
                        <option value="EN_ATTENTE">En attente</option>
                        <option value="REJETE">Rejetés</option>
                        <option value="ANNULE">Annulés</option>
                    </select>

                    <!-- Filtre par mode -->
                    <select x-model="filtreMode" @change="currentPage = 1"
                        class="w-40 rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">Tous les modes</option>
                        <option value="ESPECES">Espèces</option>
                        <option value="VIREMENT">Virement</option>
                        <option value="CHEQUE">Chèque</option>
                        <option value="MOBILE_MONEY">Mobile Money</option>
                    </select>

                    <!-- Filtre par type de paiement -->
                    <select x-model="filtreTypePaiement" @change="currentPage = 1"
                        class="w-44 rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">Tous les types</option>
                        <option value="MENSUALITE">Mensualités</option>
                        <option value="FRAIS_DOCUMENT">Frais document ACD</option>
                        <option value="AUTRE">Autres</option>
                    </select>

                    <!-- Barre de recherche -->
                    <div class="relative w-56">
                        <input type="text" x-model="search" placeholder="Rechercher référence, contrat..."
                            class="w-full pl-4 pr-10 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white text-sm">
                        <svg class="absolute right-3 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>

                    <!-- Bouton Nouveau Paiement -->
                    <button @click="newPaiement"
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
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Total paiements</p>
                        <p class="text-2xl font-bold text-gray-800 dark:text-white" x-text="stats.total"></p>
                    </div>
                    <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Validés</p>
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400" x-text="stats.valides"></p>
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
                        <p class="text-sm text-gray-500 dark:text-gray-400">En attente</p>
                        <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400" x-text="stats.en_attente"></p>
                    </div>
                    <div class="w-10 h-10 bg-yellow-100 dark:bg-yellow-900 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Montant total</p>
                        <p class="text-2xl font-bold text-purple-600 dark:text-purple-400" x-text="stats.montant_total_formatted"></p>
                    </div>
                    <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Overlay formulaire création/modification paiement -->
        <div x-show="showPaiementForm" x-cloak @keydown.escape.window="closePaiementForm" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div @click.stop class="relative w-full max-w-3xl max-h-[90vh] overflow-y-auto rounded-2xl bg-white shadow-2xl dark:bg-gray-900">

                <div class="sticky top-0 flex items-center justify-between border-b border-gray-200 bg-white px-6 py-4 dark:border-gray-700 dark:bg-gray-900">
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white" x-text="editingPaiement ? 'Modifier le paiement' : 'Nouveau paiement'"></h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400" x-text="editingPaiement ? 'Modifiez les informations du paiement' : 'Ajoutez un paiement avec un formulaire clair et rapide'"></p>
                    </div>
                    <button type="button" @click="closePaiementForm" class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form @submit.prevent="savePaiement" class="p-6">
                    <template x-if="formErrors.length">
                        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800 dark:border-red-800 dark:bg-red-900/20 dark:text-red-200">
                            <p class="font-semibold">Veuillez corriger les erreurs :</p>
                            <ul class="mt-2 list-inside list-disc">
                                <template x-for="error in formErrors" :key="error"><li x-text="error"></li></template>
                            </ul>
                        </div>
                    </template>

                    <!-- Sélecteur type de paiement -->
                    <div class="grid gap-5 sm:grid-cols-2 mb-4">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Type de paiement *</label>
                            <select x-model="form.type_paiement" required @change="onTypePaiementChange" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                                <option value="MENSUALITE">Mensualité du contrat</option>
                                <option value="FRAIS_DOCUMENT">Frais document ACD</option>
                                <option value="AUTRE">Autre</option>
                            </select>
                        </div>

                        <!-- Sélection du document (si type = FRAIS_DOCUMENT) -->
                        <div x-show="form.type_paiement === 'FRAIS_DOCUMENT'" x-transition>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Document ACD *</label>
                            <select x-model="form.id_contrat_document" :required="form.type_paiement === 'FRAIS_DOCUMENT'" @change="onDocumentChange" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                                <option value="">-- Sélectionnez un document --</option>
                                <template x-for="doc in documentsDisponibles" :key="doc.id_contrat_document">
                                    <option :value="doc.id_contrat_document" x-text="doc.document_type_nom + ' - ' + doc.montant_formatted"></option>
                                </template>
                            </select>
                            <p class="mt-1 text-xs text-gray-500" x-show="documentsDisponibles.length === 0 && form.type_paiement === 'FRAIS_DOCUMENT'">
                                Aucun document disponible. Tous les documents sont déjà payés.
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Contrat *</label>
                            <select x-model="form.id_contrat" required @change="onContratChange" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                                <option value="">Sélectionnez un contrat</option>
                                <template x-for="contrat in contratsListe" :key="contrat.id_contrat">
                                    <option :value="contrat.id_contrat" x-text="contrat.reference_contrat + ' - ' + (contrat.client?.nom_prenoms || 'Client')" :disabled="contrat.est_solde || contrat.statut === 'SOLDE' || (contrat.type_paiement === 'COMPTANT' && contrat.montant_paye > 0)"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Référence *</label>
                            <input type="text" x-model="form.reference" required readonly class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-2 text-gray-600 cursor-not-allowed dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400" placeholder="Générée automatiquement">
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Date paiement *</label>
                            <input type="date" x-model="form.date_paiement" required @change="generateReference" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Montant (FCFA) *</label>
                            <div class="relative">
                                <input type="number" x-model="form.montant" required min="0" step="1000" @input="validateMontant" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white" placeholder="0">
                                <button type="button" x-show="montantSuggere > 0 && !editingPaiement && form.type_paiement !== 'FRAIS_DOCUMENT'" @click="appliquerMontantSuggere" class="absolute right-2 top-1/2 -translate-y-1/2 px-2 py-1 bg-green-100 text-green-700 text-xs rounded hover:bg-green-200 transition-colors">
                                    Suggéré: <span x-text="formatPrice(montantSuggere)"></span>
                                </button>
                            </div>
                            <p class="mt-1 text-xs" :class="montantWarning ? 'text-red-500' : 'text-gray-500'">
                                <span x-show="montantWarning" x-text="montantWarning"></span>
                                <span x-show="!montantWarning && montantSuggereMessage && form.type_paiement !== 'FRAIS_DOCUMENT'" x-text="montantSuggereMessage"></span>
                            </p>
                            <p x-show="form.type_paiement === 'FRAIS_DOCUMENT' && documentSelectionne" class="mt-1 text-xs text-green-600">
                                Document sélectionné: <span x-text="documentSelectionne?.document_type_nom"></span> -
                                Montant: <span x-text="documentSelectionne?.montant_formatted"></span>
                            </p>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Mode de paiement *</label>
                            <select x-model="form.mode_paiement" required @change="resetBanque" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                                <option value="ESPECES">Espèces</option>
                                <option value="VIREMENT">Virement bancaire</option>
                                <option value="CHEQUE">Chèque</option>
                                <option value="MOBILE_MONEY">Mobile Money</option>
                            </select>
                        </div>

                        <div x-show="form.mode_paiement === 'VIREMENT' || form.mode_paiement === 'CHEQUE'" x-transition>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Banque *</label>
                            <select x-model="form.banque_paiement" :required="form.mode_paiement === 'VIREMENT' || form.mode_paiement === 'CHEQUE'" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
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
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Statut *</label>
                            <select x-model="form.statut" required @change="validateMontant" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                                <option value="VALIDE">Validé</option>
                                <option value="EN_ATTENTE">En attente</option>
                            </select>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
                            <textarea x-model="form.notes" rows="2" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white" placeholder="Notes ou commentaires (optionnel)"></textarea>
                        </div>
                    </div>

                    <!-- Info contrat sélectionné -->
                    <div x-show="form.id_contrat && contratSelectionne" class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                        <h4 class="text-sm font-semibold text-blue-800 dark:text-blue-300 mb-2">Informations du contrat</h4>
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div><span class="text-gray-600 dark:text-gray-400">Référence : </span><span class="font-medium text-gray-800 dark:text-white" x-text="contratSelectionne?.reference_contrat"></span></div>
                            <div><span class="text-gray-600 dark:text-gray-400">Client : </span><span class="font-medium text-gray-800 dark:text-white" x-text="contratSelectionne?.client_nom"></span></div>
                            <div><span class="text-gray-600 dark:text-gray-400">Montant total : </span><span class="font-medium text-gray-800 dark:text-white" x-text="contratSelectionne?.montant_total_formatted"></span></div>
                            <div><span class="text-gray-600 dark:text-gray-400">Reste à payer : </span><span class="font-medium" :class="contratSelectionne?.reste_a_payer > 0 ? 'text-orange-600' : 'text-green-600'" x-text="formatPrice(contratSelectionne?.reste_a_payer)"></span></div>
                            <div>
                                <span class="text-gray-600 dark:text-gray-400">Statut :</span>
                                <span class="font-medium" x-text="contratSelectionne?.statut_label" :class="{
                                    'text-yellow-600 dark:text-yellow-400': contratSelectionne?.statut === 'EN_COURS',
                                    'text-green-600 dark:text-green-400': contratSelectionne?.statut === 'SOLDE',
                                    'text-red-600 dark:text-red-400': contratSelectionne?.statut === 'RESILIE',
                                    'text-orange-600 dark:text-orange-400': contratSelectionne?.statut === 'EN_RETARD'
                                }"></span>
                            </div>
                            <div><span class="text-gray-600 dark:text-gray-400">Type : </span><span class="font-medium text-gray-800 dark:text-white" x-text="contratSelectionne?.type_paiement_label"></span></div>
                            <div x-show="contratSelectionne?.type_paiement === 'ECHELONNE' && prochaineEcheanceInfo && form.type_paiement !== 'FRAIS_DOCUMENT'" class="col-span-2">
                                <span class="text-gray-600 dark:text-gray-400">Prochaine échéance : </span>
                                <span class="font-medium text-blue-700 dark:text-blue-300" x-text="'Échéance n°' + prochaineEcheanceInfo?.numero + ' - ' + prochaineEcheanceInfo?.montant_prevu_formatted + ' (' + prochaineEcheanceInfo?.date_echeance + ')'"></span>
                            </div>
                            <div x-show="form.type_paiement === 'FRAIS_DOCUMENT' && documentSelectionne" class="col-span-2 mt-2 p-2 bg-green-50 dark:bg-green-900/20 rounded">
                                <span class="text-gray-600 dark:text-gray-400">Document : </span>
                                <span class="font-medium text-green-700 dark:text-green-300" x-text="documentSelectionne?.document_type_nom + ' - Reste à payer: ' + ((documentSelectionne?.montant - (documentSelectionne?.paiements_somme_montant || 0)).toLocaleString('fr-FR')) + ' FCFA'"></span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex gap-3 border-t border-gray-200 pt-6 dark:border-gray-700">
                        <button type="button" @click="closePaiementForm" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">Annuler</button>
                        <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"><span x-text="editingPaiement ? 'Mettre à jour' : 'Ajouter paiement'"></span></button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Détails paiement -->
        <div x-show="showDetailsModal" x-cloak @keydown.escape.window="closeDetailsModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div @click.stop class="relative w-full max-w-2xl max-h-[85vh] overflow-y-auto rounded-xl bg-white shadow-2xl dark:bg-gray-900">
                <div class="sticky top-0 flex items-center justify-between border-b border-gray-200 bg-white px-5 py-3 dark:border-gray-700 dark:bg-gray-900">
                    <div><h3 class="text-lg font-semibold text-gray-900 dark:text-white">Détails du paiement</h3></div>
                    <button type="button" @click="closeDetailsModal" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
                </div>
                <div class="p-5">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div><label class="mb-1 block text-xs font-medium text-gray-500">Référence</label><input type="text" x-model="selectedPaiement?.reference" readonly disabled class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-1.5 text-sm text-gray-600 cursor-not-allowed"></div>
                        <div><label class="mb-1 block text-xs font-medium text-gray-500">Contrat</label><input type="text" x-model="selectedPaiement?.contrat_reference" readonly disabled class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-1.5 text-sm text-gray-600 cursor-not-allowed"></div>
                        <div><label class="mb-1 block text-xs font-medium text-gray-500">Type</label><input type="text" x-model="selectedPaiement?.type_paiement_label" readonly disabled class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-1.5 text-sm text-gray-600 cursor-not-allowed"></div>
                        <div><label class="mb-1 block text-xs font-medium text-gray-500">Date</label><input type="text" x-model="selectedPaiement?.date_paiement_formatted" readonly disabled class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-1.5 text-sm text-gray-600 cursor-not-allowed"></div>
                        <div><label class="mb-1 block text-xs font-medium text-gray-500">Montant</label><input type="text" x-model="selectedPaiement?.montant_formatted" readonly disabled class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-1.5 text-sm text-gray-600 cursor-not-allowed"></div>
                        <div><label class="mb-1 block text-xs font-medium text-gray-500">Mode</label><input type="text" x-model="selectedPaiement?.mode_paiement_label" readonly disabled class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-1.5 text-sm text-gray-600 cursor-not-allowed"></div>
                        <div x-show="selectedPaiement?.banque_paiement"><label class="mb-1 block text-xs font-medium text-gray-500">Banque</label><input type="text" x-model="selectedPaiement?.banque_label" readonly disabled class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-1.5 text-sm text-gray-600 cursor-not-allowed"></div>
                        <div><label class="mb-1 block text-xs font-medium text-gray-500">Statut</label><input type="text" x-model="selectedPaiement?.statut_label" readonly disabled class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-1.5 text-sm text-gray-600 cursor-not-allowed"></div>
                    </div>
                    <div class="mt-5 flex gap-3 border-t border-gray-200 pt-4">
                        <button type="button" @click="closeDetailsModal" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50">Fermer</button>
                        <button type="button" @click="printReceipt(selectedPaiement)" class="rounded-lg bg-purple-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-purple-700">Imprimer reçu</button>
                        <button type="button" @click="editFromDetails" x-show="selectedPaiement?.peut_etre_modifie !== false" class="rounded-lg bg-blue-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-blue-700">Modifier</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modales de confirmation -->
        <div x-show="showValiderModal" x-cloak @keydown.escape.window="closeValiderModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div @click.stop class="relative w-full max-w-md rounded-xl bg-white shadow-2xl dark:bg-gray-900">
                <div class="p-6 text-center">
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-green-100 dark:bg-green-900">
                        <svg class="h-7 w-7 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">Confirmation de validation</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Êtes-vous sûr de vouloir valider le paiement <br>
                        <span class="font-semibold text-gray-700 dark:text-gray-300" x-text="paiementToAction?.reference"></span> ?
                    </p>
                    <p class="mt-2 text-xs text-gray-400 dark:text-gray-500">Cette action est irréversible.</p>
                </div>
                <div class="flex justify-center gap-3 border-t border-gray-200 px-6 py-4 dark:border-gray-700">
                    <button @click="closeValiderModal" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">Annuler</button>
                    <button @click="confirmValiderPaiement" class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">Valider</button>
                </div>
            </div>
        </div>

        <div x-show="showRejeterModal" x-cloak @keydown.escape.window="closeRejeterModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div @click.stop class="relative w-full max-w-md rounded-xl bg-white shadow-2xl dark:bg-gray-900">
                <div class="p-6 text-center">
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-orange-100 dark:bg-orange-900">
                        <svg class="h-7 w-7 text-orange-600 dark:text-orange-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                    <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">Confirmation de rejet</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Êtes-vous sûr de vouloir rejeter le paiement <br>
                        <span class="font-semibold text-gray-700 dark:text-gray-300" x-text="paiementToAction?.reference"></span> ?
                    </p>
                    <p class="mt-2 text-xs text-gray-400 dark:text-gray-500">Cette action est irréversible.</p>
                </div>
                <div class="flex justify-center gap-3 border-t border-gray-200 px-6 py-4 dark:border-gray-700">
                    <button @click="closeRejeterModal" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Annuler</button>
                    <button @click="confirmRejeterPaiement" class="rounded-lg bg-orange-600 px-4 py-2 text-sm font-medium text-white hover:bg-orange-700">Rejeter</button>
                </div>
            </div>
        </div>

        <div x-show="showDeleteModal" x-cloak @keydown.escape.window="closeDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div @click.stop class="relative w-full max-w-md rounded-xl bg-white shadow-2xl dark:bg-gray-900">
                <div class="p-6 text-center">
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-red-100 dark:bg-red-900">
                        <svg class="h-7 w-7 text-red-600 dark:text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </div>
                    <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">Confirmation de suppression</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Êtes-vous sûr de vouloir supprimer définitivement le paiement <br>
                        <span class="font-semibold text-gray-700 dark:text-gray-300" x-text="paiementToDelete?.reference"></span> ?
                    </p>
                    <p class="mt-2 text-xs text-red-500 dark:text-red-400">Action irréversible.</p>
                </div>
                <div class="flex justify-center gap-3 border-t border-gray-200 px-6 py-4 dark:border-gray-700">
                    <button @click="closeDeleteModal" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Annuler</button>
                    <button @click="confirmDeletePaiement" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">Supprimer</button>
                </div>
            </div>
        </div>

        <div x-show="showEditConfirmModal" x-cloak @keydown.escape.window="closeEditConfirmModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div @click.stop class="relative w-full max-w-md rounded-xl bg-white shadow-2xl dark:bg-gray-900">
                <div class="p-6 text-center">
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900">
                        <svg class="h-7 w-7 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </div>
                    <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">Confirmation de modification</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Êtes-vous sûr de vouloir modifier le paiement <br>
                        <span class="font-semibold text-gray-700 dark:text-gray-300" x-text="paiementToEdit?.reference"></span> ?
                    </p>
                    <p class="mt-2 text-xs text-gray-400 dark:text-gray-500">Vous pourrez modifier les informations dans le formulaire.</p>
                </div>
                <div class="flex justify-center gap-3 border-t border-gray-200 px-6 py-4 dark:border-gray-700">
                    <button @click="closeEditConfirmModal" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Annuler</button>
                    <button @click="confirmEditPaiement" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">Continuer</button>
                </div>
            </div>
        </div>

        <!-- Modales admin -->
        <div x-show="showValidatedActionDialog" x-cloak @keydown.escape.window="closeValidatedActionDialog" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div @click.stop class="relative w-full max-w-lg rounded-xl bg-white shadow-2xl dark:bg-gray-900">
                <div class="p-6">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 mt-1">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900">
                                <svg class="h-6 w-6 text-red-600 dark:text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Paiement validé détecté</h3>
                            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 mb-4">
                                <p class="text-sm text-red-800 dark:text-red-200">
                                    <span class="font-semibold">Attention :</span> Le paiement
                                    <span class="font-semibold" x-text="paiementToAdminAction?.reference"></span>
                                    est actuellement <span class="font-semibold">validé</span>.
                                </p>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                <span x-show="adminActionType === 'delete'">La suppression nécessite des privilèges d'administrateur.</span>
                                <span x-show="adminActionType === 'edit'">La modification nécessite des privilèges d'administrateur.</span>
                            </p>
                            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                                <p class="text-sm text-yellow-800 dark:text-yellow-200">Voulez-vous procéder en tant qu'administrateur ?</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end gap-3 border-t border-gray-200 px-6 py-4 dark:border-gray-700">
                    <button @click="closeValidatedActionDialog" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Annuler</button>
                    <button @click="openAdminPasswordFromDialog" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700"><span x-text="adminActionType === 'delete' ? 'Supprimer' : 'Modifier'"></span> en tant qu'admin</button>
                </div>
            </div>
        </div>

        <div x-show="showAdminPasswordModal" x-cloak @keydown.escape.window="closeAdminPasswordModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div @click.stop class="relative w-full max-w-md rounded-xl bg-white shadow-2xl dark:bg-gray-900">
                <div class="p-6">
                    <div class="text-center mb-4">
                        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-purple-100 dark:bg-purple-900">
                            <svg class="h-7 w-7 text-purple-600 dark:text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Authentification requise</h3>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            Mot de passe administrateur pour
                            <span x-text="adminActionType === 'delete' ? 'supprimer' : 'modifier'"></span>
                            le paiement <span x-text="paiementToAdminAction?.reference"></span>
                        </p>
                    </div>
                    <form @submit.prevent="submitAdminAction" class="space-y-4">
                        <div x-show="adminPasswordError" class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800 dark:border-red-800 dark:bg-red-900/20 dark:text-red-200">
                            <p x-text="adminPasswordError"></p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Mot de passe *</label>
                            <div class="relative">
                                <input :type="showAdminPassword ? 'text' : 'password'" x-model="adminPassword" required class="w-full rounded-lg border border-gray-300 px-3 py-2 pr-10 focus:border-purple-500 focus:outline-none focus:ring-1 focus:ring-purple-500">
                                <button type="button" @click="showAdminPassword = !showAdminPassword" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400">
                                    <svg x-show="!showAdminPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    <svg x-show="showAdminPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243a9.97 9.97 0 01-4.243-4.243M21 12a9.97 9.97 0 01-3.029 5.543M3 3l18 18"></path></svg>
                                </button>
                            </div>
                        </div>
                        <div class="flex justify-end gap-3 pt-4 border-t">
                            <button type="button" @click="closeAdminPasswordModal" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium hover:bg-gray-50">Annuler</button>
                            <button type="submit" class="rounded-lg bg-purple-600 px-4 py-2 text-sm font-medium text-white hover:bg-purple-700">Confirmer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tableau - Colonne référence élargie -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider min-w-[160px]">RÉFÉRENCE</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider min-w-[120px]">CONTRAT</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider min-w-[130px]">TYPE</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider min-w-[120px]">DATE PAIEMENT</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider min-w-[130px]">MONTANT</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider min-w-[100px]">MODE</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider min-w-[120px]">BANQUE</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider min-w-[100px]">STATUT</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider min-w-[140px]">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <template x-for="item in paginatedData" :key="item.id_paiement">
                        <tr @click="selectRow(item.id_paiement)" :class="selectedRow === item.id_paiement ? 'bg-gray-200 dark:bg-gray-700 border-l-4 border-blue-500 shadow-sm' : 'hover:bg-gray-50 dark:hover:bg-gray-700/50'" class="cursor-pointer transition-all duration-200">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white whitespace-nowrap" x-text="item.reference"></td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap" x-text="item.contrat_reference"></td>
                            <td class="px-6 py-4 text-sm whitespace-nowrap">
                                <span x-text="item.type_paiement_label" :class="{'bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full text-xs': item.type_paiement === 'FRAIS_DOCUMENT', 'bg-gray-100 text-gray-700 px-2 py-0.5 rounded-full text-xs': item.type_paiement !== 'FRAIS_DOCUMENT'}"></span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap" x-text="item.date_paiement_formatted"></td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap" x-text="item.montant_formatted"></td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap" x-text="item.mode_paiement_label"></td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap" x-text="item.banque_label || '-'"></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span x-text="item.statut_label" :class="{'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-200': item.statut === 'EN_ATTENTE','bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-200': item.statut === 'VALIDE','bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-200': item.statut === 'REJETE' || item.statut === 'ANNULE'}" class="px-2 py-1 rounded-full text-xs font-semibold"></span>
                            </td>
                            <td class="px-6 py-4 text-sm font-medium whitespace-nowrap">
                                <button @click.stop="viewDetails(item)" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300 mr-2 transition-colors" title="Détails"><svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg></button>
                                <button @click.stop="printReceipt(item)" class="text-purple-600 hover:text-purple-900 dark:text-purple-400 dark:hover:text-purple-300 mr-2 transition-colors" title="Reçu"><svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2z"></path></svg></button>
                                <button x-show="item.statut === 'EN_ATTENTE'" @click.stop="openValiderModal(item)" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300 mr-2 transition-colors" title="Valider"><svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></button>
                                <button x-show="item.statut === 'EN_ATTENTE'" @click.stop="openRejeterModal(item)" class="text-orange-600 hover:text-orange-900 dark:text-orange-400 dark:hover:text-orange-300 mr-2 transition-colors" title="Rejeter"><svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                                <button x-show="item.peut_etre_modifie !== false" @click.stop="openEditConfirmModal(item)" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 mr-2 transition-colors" title="Modifier"><svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg></button>
                                <button @click.stop="openDeleteModal(item)" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 transition-colors" title="Supprimer"><svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="filteredData.length === 0">
                        <td colspan="9" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                            <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <p class="text-lg font-medium">Aucun paiement trouvé</p>
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
        filtreStatut: '',
        filtreMode: '',
        filtreTypePaiement: '',
        currentPage: 1,
        itemsPerPage: 10,
        selectedRow: null,
        showPaiementForm: false,
        showDetailsModal: false,
        selectedPaiement: null,
        editingPaiement: null,
        formErrors: [],
        contratsListe: [],
        contratSelectionne: null,
        montantWarning: '',
        montantSuggere: 0,
        montantSuggereMessage: '',
        prochaineEcheanceInfo: null,
        documentsDisponibles: [],
        documentSelectionne: null,
        form: { id_contrat: '', date_paiement: '', montant: '', mode_paiement: 'ESPECES', reference: '', banque_paiement: '', statut: 'VALIDE', notes: '', type_paiement: 'MENSUALITE', id_contrat_document: '' },
        items: [],

        showDeleteModal: false,
        paiementToDelete: null,
        showValiderModal: false,
        paiementToAction: null,
        showRejeterModal: false,
        showEditConfirmModal: false,
        paiementToEdit: null,
        showValidatedActionDialog: false,
        paiementToAdminAction: null,
        adminActionType: 'delete',
        showAdminPasswordModal: false,
        adminPassword: '',
        showAdminPassword: false,
        adminPasswordError: '',

        get stats() {
            const total = this.items.length;
            const valides = this.items.filter(i => i.statut === 'VALIDE').length;
            const en_attente = this.items.filter(i => i.statut === 'EN_ATTENTE').length;
            const montantTotal = this.items.reduce((sum, i) => sum + (parseFloat(i.montant) || 0), 0);
            return { total, valides, en_attente, montant_total_formatted: this.formatPrice(montantTotal) };
        },

        get filteredData() {
            let data = [...this.items];
            if (this.filtreStatut) data = data.filter(i => i.statut === this.filtreStatut);
            if (this.filtreMode) data = data.filter(i => i.mode_paiement === this.filtreMode);
            if (this.filtreTypePaiement) data = data.filter(i => i.type_paiement === this.filtreTypePaiement);
            if (this.search) {
                const s = this.search.toLowerCase();
                data = data.filter(item => (item.reference && item.reference.toLowerCase().includes(s)) || (item.contrat_reference && item.contrat_reference.toLowerCase().includes(s)) || (item.client_nom && item.client_nom.toLowerCase().includes(s)));
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

        getModePaiementLabel(m) { const l = {'ESPECES':'Espèces','VIREMENT':'Virement bancaire','CHEQUE':'Chèque','MOBILE_MONEY':'Mobile Money'}; return l[m] || m },
        getBanqueLabel(b) { const l = {'SGBCI':'SGBCI','ECOBANK':'Ecobank CI','NSIA':'NSIA Banque','ORABANK':'Orabank','BICICI':'BICICI','BRIDGE':'Bridge Bank','CORIS':'Coris Bank','BANK_OF_AFRICA':'Bank of Africa'}; return l[b] || b },
        getStatutLabel(s) { const l = {'EN_ATTENTE':'En attente','VALIDE':'Validé','ANNULE':'Annulé','REJETE':'Rejeté'}; return l[s] || s },
        getTypePaiementLabel(t) { const l = {'MENSUALITE':'Mensualité','FRAIS_DOCUMENT':'Frais document ACD','AUTRE':'Autre'}; return l[t] || t || 'Mensualité' },

        formatPrice(p) { if (!p && p !== 0) return '0 FCFA'; const n = parseFloat(p); if (isNaN(n) || n === 0) return '0 FCFA'; return new Intl.NumberFormat('fr-FR').format(n) + ' FCFA' },
        formatDate(d) { if (!d) return '-'; const dt = new Date(d); return isNaN(dt.getTime()) ? d : dt.toLocaleDateString('fr-FR') },
        getTodayDate() { const t = new Date(); return `${t.getFullYear()}-${String(t.getMonth()+1).padStart(2,'0')}-${String(t.getDate()).padStart(2,'0')}` },

        openDeleteModal(item) {
            if (item.statut === 'VALIDE') {
                this.paiementToAdminAction = item;
                this.adminActionType = 'delete';
                this.showValidatedActionDialog = true;
                return;
            }
            this.paiementToDelete = item;
            this.showDeleteModal = true;
        },
        closeDeleteModal() { this.showDeleteModal = false; this.paiementToDelete = null; },
        openValiderModal(item) { this.paiementToAction = item; this.showValiderModal = true; },
        closeValiderModal() { this.showValiderModal = false; this.paiementToAction = null; },
        openRejeterModal(item) { this.paiementToAction = item; this.showRejeterModal = true; },
        closeRejeterModal() { this.showRejeterModal = false; this.paiementToAction = null; },
        openEditConfirmModal(item) {
            if (item.statut === 'VALIDE') {
                this.paiementToAdminAction = item;
                this.adminActionType = 'edit';
                this.showValidatedActionDialog = true;
                return;
            }
            this.paiementToEdit = item;
            this.showEditConfirmModal = true;
        },
        closeEditConfirmModal() { this.showEditConfirmModal = false; this.paiementToEdit = null; },
        closeValidatedActionDialog() { this.showValidatedActionDialog = false; this.paiementToAdminAction = null; this.adminActionType = 'delete'; },
        openAdminPasswordFromDialog() { this.showValidatedActionDialog = false; this.adminPassword = ''; this.adminPasswordError = ''; this.showAdminPassword = false; this.showAdminPasswordModal = true; },
        closeAdminPasswordModal() { this.showAdminPasswordModal = false; this.adminPassword = ''; this.adminPasswordError = ''; this.showAdminPassword = false; this.paiementToAdminAction = null; this.adminActionType = 'delete'; },
        confirmEditPaiement() { const item = this.paiementToEdit; this.closeEditConfirmModal(); if (item) { this.editItem(item); } },

        async getNextSequenceNumber(ym) { try { const r = await fetch(`/api/paiements/next-sequence/${ym}`); if (r.ok) { const d = await r.json(); return d.nextSequence } } catch(e) {} return this.items.filter(p => p.date_paiement && p.date_paiement.substring(0,7) === ym).length + 1 },
        async generateReference() { if (!this.form.id_contrat || !this.form.date_paiement) { this.form.reference = ''; return } const d = new Date(this.form.date_paiement); const ym = `${d.getFullYear()}${String(d.getMonth()+1).padStart(2,'0')}`; const seq = await this.getNextSequenceNumber(this.form.date_paiement.substring(0,7)); this.form.reference = `PAI-${ym}-${String(seq).padStart(4,'0')}` },
        async chargerSuggestionMontant(id) { if (!id) { this.montantSuggere = 0; return } try { const r = await fetch(`/api/paiements/montant-suggere/${id}`); if (r.ok) { const d = await r.json(); if (d.success) { this.montantSuggere = d.montant_suggere; this.montantSuggereMessage = d.message; this.prochaineEcheanceInfo = d.prochaine_echeance } } } catch(e) {} },
        appliquerMontantSuggere() { if (this.montantSuggere > 0) { this.form.montant = this.montantSuggere; this.validateMontant() } },

        async loadDocumentsForContrat(idContrat) {
            if (!idContrat) { this.documentsDisponibles = []; return; }
            try {
                const response = await fetch(`/api/contrat-documents/contrat/${idContrat}`);
                if (response.ok) {
                    const data = await response.json();
                    if (data.success) {
                        this.documentsDisponibles = data.documents.filter(d => d.statut_paiement !== 'paye');
                    }
                }
            } catch(e) { console.error('Erreur chargement documents:', e); }
        },

        async onDocumentChange() {
            if (this.form.id_contrat_document) {
                const doc = this.documentsDisponibles.find(d => d.id_contrat_document == this.form.id_contrat_document);
                if (doc) {
                    this.documentSelectionne = doc;
                    const resteAPayer = doc.montant - (doc.paiements_somme_montant || 0);
                    this.form.montant = resteAPayer;
                    this.montantSuggere = resteAPayer;
                    this.montantSuggereMessage = `Reste à payer pour ${doc.document_type_nom}`;
                }
            } else {
                this.documentSelectionne = null;
            }
            this.validateMontant();
        },

        onTypePaiementChange() {
            if (this.form.type_paiement === 'FRAIS_DOCUMENT') {
                if (this.form.id_contrat) {
                    this.loadDocumentsForContrat(this.form.id_contrat);
                } else {
                    alert('Veuillez d\'abord sélectionner un contrat');
                    this.form.type_paiement = 'MENSUALITE';
                }
            } else {
                this.documentsDisponibles = [];
                this.form.id_contrat_document = '';
                this.documentSelectionne = null;
                if (this.form.type_paiement === 'MENSUALITE') {
                    this.chargerSuggestionMontant(this.form.id_contrat);
                }
            }
        },

        async onContratChange() {
            if (this.form.id_contrat) {
                const c = this.contratsListe.find(x => x.id_contrat == this.form.id_contrat);
                if (c) {
                    this.contratSelectionne = {
                        reference_contrat: c.reference_contrat, client_nom: c.client?.nom_prenoms || 'Client',
                        montant_total: c.montant_total, montant_total_formatted: this.formatPrice(c.montant_total),
                        reste_a_payer: c.reste_a_payer || 0, statut: c.statut, statut_label: this.getStatutLabel(c.statut),
                        type_paiement: c.type_paiement, type_paiement_label: this.getTypePaiementLabel(c.type_paiement)
                    };
                    if (!this.editingPaiement && this.form.type_paiement === 'MENSUALITE') {
                        await this.chargerSuggestionMontant(this.form.id_contrat);
                    }
                    if (this.form.type_paiement === 'FRAIS_DOCUMENT') {
                        await this.loadDocumentsForContrat(this.form.id_contrat);
                    }
                }
            } else {
                this.contratSelectionne = null;
                this.montantSuggere = 0;
                this.documentsDisponibles = [];
            }
            this.generateReference();
            this.validateMontant();
        },

        validateMontant() { this.montantWarning = ''; if (this.form.montant && this.contratSelectionne && this.form.statut === 'VALIDE' && this.form.type_paiement !== 'FRAIS_DOCUMENT' && parseFloat(this.form.montant) > this.contratSelectionne.reste_a_payer) this.montantWarning = `Le montant dépasse le reste à payer (${this.formatPrice(this.contratSelectionne.reste_a_payer)})` },
        resetBanque() { if (this.form.mode_paiement !== 'VIREMENT' && this.form.mode_paiement !== 'CHEQUE') this.form.banque_paiement = '' },

        async init() { await this.loadContrats(); await this.loadPaiementsFromDB(); window.addEventListener('open-paiement-form', (e) => { const { id_contrat, reference_contrat, client_nom, reste_a_payer } = e.detail; this.openPaiementWithContrat(id_contrat, reference_contrat, client_nom, reste_a_payer) }); window.addEventListener('paiement-updated', () => { this.loadContrats() }) },
        openPaiementWithContrat(idC, ref, nom, reste) { this.editingPaiement = null; this.form = { id_contrat: idC, date_paiement: this.getTodayDate(), montant: '', mode_paiement: 'ESPECES', reference: '', banque_paiement: '', statut: 'VALIDE', notes: '', type_paiement: 'MENSUALITE', id_contrat_document: '' }; this.contratSelectionne = { reference_contrat: ref, client_nom: nom, reste_a_payer: reste }; this.formErrors = []; this.showPaiementForm = true; this.generateReference(); this.chargerSuggestionMontant(idC) },
        async loadContrats() { try { const r = await fetch('/api/contrats'); if (r.ok) this.contratsListe = await r.json() } catch(e) {} },
        newPaiement() { this.editingPaiement = null; this.contratSelectionne = null; this.montantWarning = ''; this.montantSuggere = 0; this.montantSuggereMessage = ''; this.prochaineEcheanceInfo = null; this.documentsDisponibles = []; this.documentSelectionne = null; this.form = { id_contrat: '', date_paiement: this.getTodayDate(), montant: '', mode_paiement: 'ESPECES', reference: '', banque_paiement: '', statut: 'VALIDE', notes: '', type_paiement: 'MENSUALITE', id_contrat_document: '' }; this.formErrors = []; this.showPaiementForm = true },
        editItem(item) { this.editingPaiement = item; this.form = { id_contrat: item.id_contrat, date_paiement: item.date_paiement, montant: item.montant, mode_paiement: item.mode_paiement, reference: item.reference, banque_paiement: item.banque_paiement || '', statut: item.statut, notes: item.notes || '', type_paiement: item.type_paiement || 'MENSUALITE', id_contrat_document: item.id_contrat_document || '' }; this.onContratChange(); this.formErrors = []; this.showPaiementForm = true },
        closePaiementForm() { this.showPaiementForm = false; this.editingPaiement = null; this.contratSelectionne = null; this.montantWarning = ''; this.montantSuggere = 0; this.formErrors = [] },
        closeDetailsModal() { this.showDetailsModal = false; this.selectedPaiement = null },
        viewDetails(item) { this.selectedPaiement = { ...item, mode_paiement_label: this.getModePaiementLabel(item.mode_paiement), banque_label: this.getBanqueLabel(item.banque_paiement), type_paiement_label: this.getTypePaiementLabel(item.type_paiement) }; this.showDetailsModal = true },
        editFromDetails() { const item = this.selectedPaiement; this.closeDetailsModal(); this.openEditConfirmModal(item) },

        nombreEnLettres(nombre) {
            if (nombre === 0) return 'zéro franc CFA';
            const units = ['', 'un', 'deux', 'trois', 'quatre', 'cinq', 'six', 'sept', 'huit', 'neuf', 'dix', 'onze', 'douze', 'treize', 'quatorze', 'quinze', 'seize', 'dix-sept', 'dix-huit', 'dix-neuf'];
            const tens = ['', 'dix', 'vingt', 'trente', 'quarante', 'cinquante', 'soixante', 'soixante-dix', 'quatre-vingt', 'quatre-vingt-dix'];
            let result = '';
            const millions = Math.floor(nombre / 1000000);
            const resteMillions = nombre % 1000000;
            const milliers = Math.floor(resteMillions / 1000);
            const unite = resteMillions % 1000;
            if (millions > 0) { result += this.nombreEnLettresSimple(millions) + ' million' + (millions > 1 ? 's' : ''); if (resteMillions > 0) result += ' '; }
            if (milliers > 0) { if (milliers === 1) result += 'mille'; else result += this.nombreEnLettresSimple(milliers) + ' mille'; if (unite > 0) result += ' '; }
            if (unite > 0) { if (unite < 20) result += units[unite]; else { const d = Math.floor(unite / 10); const u = unite % 10; if (d === 7 || d === 9) result += tens[d-1] + '-' + units[u+10]; else { result += tens[d]; if (u > 0) result += (u === 1 && d !== 8 ? ' et ' : '-') + units[u]; } } }
            return result.charAt(0).toUpperCase() + result.slice(1) + ' francs CFA';
        },

        nombreEnLettresSimple(n) {
            if (n < 1000) {
                const units = ['', 'un', 'deux', 'trois', 'quatre', 'cinq', 'six', 'sept', 'huit', 'neuf', 'dix', 'onze', 'douze', 'treize', 'quatorze', 'quinze', 'seize', 'dix-sept', 'dix-huit', 'dix-neuf'];
                const tens = ['', 'dix', 'vingt', 'trente', 'quarante', 'cinquante', 'soixante', 'soixante-dix', 'quatre-vingt', 'quatre-vingt-dix'];
                let result = '';
                const centaines = Math.floor(n / 100);
                const reste = n % 100;
                if (centaines > 0) { result += (centaines === 1 ? '' : units[centaines] + ' ') + 'cent' + (centaines > 1 && reste === 0 ? 's' : ''); if (reste > 0) result += ' '; }
                if (reste > 0) { if (reste < 20) result += units[reste]; else { const d = Math.floor(reste / 10); const u = reste % 10; if (d === 7 || d === 9) result += tens[d-1] + '-' + units[u+10]; else { result += tens[d]; if (u > 0) result += (u === 1 && d !== 8 ? ' et ' : '-') + units[u]; } } }
                return result;
            }
            return n.toString();
        },

        async printReceipt(item) {
            try {
                const response = await fetch(`/api/paiements/${item.id_paiement}`)
                if (response.ok) {
                    const fullData = await response.json()
                    const contrat = fullData.contrat || {}
                    const client = contrat.client || {}
                    const lot = contrat.lot || {}
                    const data = {
                        ...item,
                        client_nom: client.nom_prenoms || '-',
                        client_contact: client.telephone || client.contact || '-',
                        client_email: client.email || '-',
                        client_adresse: client.adresse || '-',
                        lot_numero: lot.numero_lot || '-',
                        lot_ilot: lot.numero_ilot || '-',
                        montant_lettres: this.nombreEnLettres(item.montant),
                        mode_paiement_label: this.getModePaiementLabel(item.mode_paiement),
                        banque_label: this.getBanqueLabel(item.banque_paiement),
                        statut_label: this.getStatutLabel(item.statut),
                        type_paiement_label: this.getTypePaiementLabel(item.type_paiement)
                    }
                    const printWindow = window.open('', '_blank', 'width=900,height=700')
                    printWindow.document.write(`<!DOCTYPE html><html><head><title>Reçu ${data.reference}</title><meta charset="UTF-8"><style>*{margin:0;padding:0;box-sizing:border-box}body{font-family:Arial,sans-serif;margin:30px;background:#fff;color:#1f2937}.header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:30px}.company h3{font-size:18px;color:#1e40af}.company p{font-size:12px;color:#6b7280}h1{color:#1e40af;font-size:22px;text-align:center;margin-bottom:30px;text-transform:uppercase;letter-spacing:2px}.line{width:80px;height:3px;background:#2563eb;margin:10px auto 30px}.section{margin-bottom:25px}.section-title{font-size:12px;color:#6b7280;text-transform:uppercase;margin-bottom:5px;font-weight:600}.box{background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:15px;margin-bottom:15px}.box p{margin:3px 0;font-size:14px}.label{color:#6b7280;font-size:12px}.value{font-weight:600;color:#111827}table{width:100%;border-collapse:collapse;margin:15px 0}th{background:#f3f4f6;padding:10px;text-align:left;font-size:12px;font-weight:600;color:#374151;border-bottom:2px solid #e5e7eb}td{padding:10px;border-bottom:1px solid #e5e7eb;font-size:14px}.text-right{text-align:right}.total{font-size:16px;font-weight:700;color:#111827}.total-amount{font-size:20px;font-weight:700;color:#16a34a}.status-badge{display:inline-block;padding:5px 15px;border-radius:20px;font-size:12px;font-weight:600}.status-valide{background:#d1fae5;color:#065f46}.status-attente{background:#fef3c7;color:#92400e}.signatures{display:flex;justify-content:space-between;margin-top:50px}.signature-box{text-align:center;width:45%}.signature-box .line{margin:60px auto 5px;width:200px;height:1px;background:#d1d5db}.signature-box p{font-size:12px;color:#6b7280}.footer{margin-top:40px;text-align:center;font-size:11px;color:#9ca3af;border-top:1px solid #e5e7eb;padding-top:15px}.no-print{background:#2563eb;color:white;padding:10px 20px;border:none;border-radius:8px;cursor:pointer;margin-bottom:20px;font-size:14px}@media print{body{margin:15px}.no-print{display:none}}</style></head><body><button class="no-print" onclick="window.print()">Imprimer le reçu</button><div class="header"><div><h2 style="color:#1e40af">GSF SARLU</h2><p>Société de promotion immobilière</p><p style="font-size:11px">01 BP 1234 Abidjan 01 - Côte d'Ivoire</p></div><div class="text-right"><p style="font-size:12px;color:#6b7280">Fait à Abidjan, le ${new Date().toLocaleDateString('fr-FR')}</p></div></div><h1>REÇU DE PAIEMENT N° ${data.reference}</h1><div class="line"></div><div class="section"><div class="section-title">REÇU DE :</div><div class="box"><p><span class="label">Nom : </span><span class="value">${data.client_nom}</span></p><p><span class="label">Adresse : </span>${data.client_adresse}</p><p><span class="label">Tél : </span>${data.client_contact} <span style="margin-left:20px"><span class="label">Email : </span>${data.client_email}</span></p></div></div><div class="section"><div class="section-title">OBJET :</div><div class="box" style="background:#eff6ff;border-color:#bfdbfe"><p>Paiement pour le contrat <strong>${data.contrat_reference}</strong> - Lot <strong>${data.lot_numero}</strong> (Îlot ${data.lot_ilot})</p><p style="margin-top:5px"><strong>Type :</strong> ${data.type_paiement_label}</p></div></div><table><thead><tr><th>DÉSIGNATION</th><th class="text-right">MONTANT (FCFA)</th></tr></thead><tbody><tr><td>${data.type_paiement === 'FRAIS_DOCUMENT' ? 'Frais document ACD' : 'Paiement mensualité'}<br><span style="font-size:11px;color:#6b7280">Mode: ${data.mode_paiement_label}${data.banque_paiement ? ' | Banque: ' + data.banque_label : ''}</span></td><td class="text-right">${data.montant_formatted}</td></tr><tr style="background:#f9fafb"><td class="total">TOTAL</td><td class="text-right total-amount">${data.montant_formatted}</td></tr></tbody></table><div class="box"><p><span class="label">Arrêté la présente quittance de la somme de :</span></p><p style="font-weight:600;font-size:15px;margin-top:5px">${data.montant_lettres}</p></div><div style="margin:20px 0;padding:10px 15px;border-radius:8px;background:${data.statut === 'VALIDE' ? '#d1fae5' : '#fef3c7'};border:1px solid ${data.statut === 'VALIDE' ? '#6ee7b7' : '#fde047'}"><span style="font-weight:600">Statut :</span> <span class="status-badge ${data.statut === 'VALIDE' ? 'status-valide' : 'status-attente'}">${data.statut_label}</span></div><div class="signatures"><div class="signature-box"><p>Le bénéficiaire</p><div class="line"></div><p style="font-size:10px">(Signature précédée de la mention<br>"Bon pour encaissement")</p></div><div class="signature-box"><p>Pour GESTION LOTISSEMENTS</p><div class="line"></div><p style="font-size:10px">(Signature et cachet)</p></div></div><div class="footer">Document généré automatiquement - Fait foi entre les parties - Généré le ${new Date().toLocaleString('fr-FR')}</div><script>setTimeout(function(){window.print()},500)<\/script></body></html>`)
                    printWindow.document.close()
                }
            } catch (error) { console.error('Erreur impression reçu:', error); alert('Erreur lors de l\'impression du reçu') }
        },

        async loadPaiementsFromDB() {
            try {
                const r = await fetch('/api/paiements');
                const d = await r.json();
                if (Array.isArray(d)) {
                    this.items = d.map(p => ({
                        id_paiement: p.id_paiement,
                        id_contrat: p.id_contrat,
                        reference: p.reference,
                        contrat_reference: p.contrat?.reference_contrat || '-',
                        client_nom: p.contrat?.client?.nom_prenoms || '-',
                        date_paiement: p.date_paiement,
                        date_paiement_formatted: this.formatDate(p.date_paiement),
                        montant: p.montant,
                        montant_formatted: this.formatPrice(p.montant),
                        mode_paiement: p.mode_paiement,
                        mode_paiement_label: this.getModePaiementLabel(p.mode_paiement),
                        banque_paiement: p.banque_paiement,
                        banque_label: this.getBanqueLabel(p.banque_paiement),
                        statut: p.statut,
                        statut_label: this.getStatutLabel(p.statut),
                        notes: p.notes || '',
                        peut_etre_modifie: p.contrat?.statut !== 'SOLDE',
                        type_paiement: p.type_paiement || 'MENSUALITE',
                        type_paiement_label: this.getTypePaiementLabel(p.type_paiement || 'MENSUALITE'),
                        id_contrat_document: p.id_contrat_document
                    }))
                }
                this.currentPage = 1
            } catch(e) {}
        },

        async confirmValiderPaiement() {
            const id = this.paiementToAction?.id_paiement;
            this.closeValiderModal();
            if (!id) return;
            try {
                const r = await fetch(`/api/paiements/${id}/valider`, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '', 'Accept': 'application/json' } });
                const d = await r.json();
                if (d.success) { await this.loadPaiementsFromDB(); await this.loadContrats(); alert(d.message || 'Paiement validé avec succès'); window.dispatchEvent(new CustomEvent('paiement-updated')); }
                else { alert(d.message || 'Erreur lors de la validation'); }
            } catch(e) { alert('Erreur de connexion'); }
        },

        async confirmRejeterPaiement() {
            const id = this.paiementToAction?.id_paiement;
            this.closeRejeterModal();
            if (!id) return;
            try {
                const r = await fetch(`/api/paiements/${id}/rejeter`, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '', 'Accept': 'application/json' } });
                const d = await r.json();
                if (d.success) { await this.loadPaiementsFromDB(); alert(d.message || 'Paiement rejeté'); window.dispatchEvent(new CustomEvent('paiement-updated')); }
                else { alert(d.message || 'Erreur lors du rejet'); }
            } catch(e) { alert('Erreur de connexion'); }
        },

        async confirmDeletePaiement() {
            const id = this.paiementToDelete?.id_paiement;
            this.closeDeleteModal();
            if (!id) return;
            try {
                const r = await fetch(`/api/paiements/${id}`, { method: 'DELETE', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '', 'Accept': 'application/json' } });
                const d = await r.json();
                if (d.success) { this.items = this.items.filter(i => i.id_paiement !== id); alert('Paiement supprimé avec succès'); window.dispatchEvent(new CustomEvent('paiement-updated')); }
                else { alert(d.message || 'Erreur lors de la suppression'); }
            } catch(e) { alert('Erreur de connexion'); }
        },

        async submitAdminAction() {
            this.adminPasswordError = '';
            if (!this.adminPassword) { this.adminPasswordError = 'Le mot de passe administrateur est requis.'; return; }
            const item = this.paiementToAdminAction;
            if (!item || !item.id_paiement) { this.adminPasswordError = 'Erreur: paiement introuvable.'; return; }
            if (this.adminActionType === 'delete') {
                try {
                    const r = await fetch(`/api/paiements/${item.id_paiement}`, {
                        method: 'DELETE',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '', 'Accept': 'application/json' },
                        body: JSON.stringify({ admin_password: this.adminPassword })
                    });
                    const d = await r.json();
                    if (d.success) {
                        this.items = this.items.filter(i => i.id_paiement !== item.id_paiement);
                        this.closeAdminPasswordModal();
                        await this.loadContrats();
                        alert(d.message || 'Paiement supprimé avec succès');
                        window.dispatchEvent(new CustomEvent('paiement-updated'));
                    } else { this.adminPasswordError = d.message || 'Mot de passe administrateur incorrect.'; }
                } catch(e) { this.adminPasswordError = 'Erreur de connexion au serveur.'; }
            } else if (this.adminActionType === 'edit') {
                try {
                    const r = await fetch(`/api/paiements/${item.id_paiement}/verify-admin`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '', 'Accept': 'application/json' },
                        body: JSON.stringify({ admin_password: this.adminPassword })
                    });
                    const d = await r.json();
                    if (d.success) {
                        this.closeAdminPasswordModal();
                        this.editItem(item);
                    } else { this.adminPasswordError = d.message || 'Mot de passe administrateur incorrect.'; }
                } catch(e) { this.adminPasswordError = 'Erreur de connexion au serveur.'; }
            }
        },

        async savePaiement() {
            this.formErrors = [];
            this.montantWarning = '';

            if (!this.form.id_contrat) this.formErrors.push('Le contrat est requis.');
            if (!this.form.date_paiement) this.formErrors.push('La date de paiement est requise.');
            if (!this.form.montant || parseFloat(this.form.montant) <= 0) this.formErrors.push('Le montant doit être supérieur à 0.');
            if ((this.form.mode_paiement === 'VIREMENT' || this.form.mode_paiement === 'CHEQUE') && !this.form.banque_paiement) this.formErrors.push('La banque est requise pour ce mode de paiement.');
            if (this.form.type_paiement === 'FRAIS_DOCUMENT' && !this.form.id_contrat_document) this.formErrors.push('Le document ACD est requis.');
            if (this.form.statut === 'VALIDE' && this.form.type_paiement !== 'FRAIS_DOCUMENT' && this.contratSelectionne && parseFloat(this.form.montant) > this.contratSelectionne.reste_a_payer) {
                this.formErrors.push(`Le montant dépasse le reste à payer (${this.formatPrice(this.contratSelectionne.reste_a_payer)}).`);
            }

            if (!this.editingPaiement && !this.form.reference) {
                await this.generateReference();
                if (!this.form.reference) this.formErrors.push('Impossible de générer la référence.');
            }

            if (this.formErrors.length) return;

            let url;
            if (this.editingPaiement) {
                url = `/api/paiements/${this.editingPaiement.id_paiement}`;
            } else if (this.form.type_paiement === 'FRAIS_DOCUMENT') {
                url = '/api/paiements/frais-document';
            } else {
                url = '/api/paiements';
            }

            const method = this.editingPaiement ? 'PUT' : 'POST';

            const dataToSend = {
                id_contrat: this.form.id_contrat,
                date_paiement: this.form.date_paiement,
                montant: parseFloat(this.form.montant),
                mode_paiement: this.form.mode_paiement,
                reference: this.form.reference,
                banque_paiement: this.form.banque_paiement || null,
                statut: this.form.statut,
                notes: this.form.notes || null,
                type_paiement: this.form.type_paiement
            };

            if (this.form.type_paiement === 'FRAIS_DOCUMENT' && this.form.id_contrat_document) {
                dataToSend.id_contrat_document = parseInt(this.form.id_contrat_document);
            }

            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(dataToSend)
                });

                const data = await response.json();

                if (data.success) {
                    await this.loadPaiementsFromDB();
                    await this.loadContrats();
                    let message = this.editingPaiement ? 'Paiement modifié avec succès.' : 'Paiement créé avec succès.';
                    if (data.echeances_affectees && data.echeances_affectees.length > 0) {
                        message += ` ${data.echeances_affectees.length} échéance(s) mise(s) à jour.`;
                    }
                    this.closePaiementForm();
                    alert(message);
                    window.dispatchEvent(new CustomEvent('paiement-updated'));
                    window.dispatchEvent(new CustomEvent('contrat-updated'));
                } else {
                    if (data.errors) {
                        this.formErrors = Object.values(data.errors).flat();
                    } else {
                        alert('Erreur: ' + (data.message || 'Erreur inconnue'));
                    }
                }
            } catch(error) {
                console.error('Erreur savePaiement:', error);
                alert('Erreur de connexion au serveur: ' + error.message);
            }
        },

        exportCSV() {
            const h = ['Référence','Contrat','Type','Date Paiement','Montant','Mode','Banque','Statut'];
            const rows = this.filteredData.map(i => [i.reference,i.contrat_reference,i.type_paiement_label,i.date_paiement_formatted,i.montant,i.mode_paiement_label,i.banque_label||'-',i.statut_label]);
            const csv = [h.join(','),...rows.map(r=>r.map(c=>`"${c}"`).join(','))].join('\n');
            const b = new Blob(['\uFEFF'+csv],{type:'text/csv;charset=utf-8;'});
            const l = document.createElement('a'); l.href=URL.createObjectURL(b); l.download=`paiements_${new Date().toISOString().split('T')[0]}.csv`; l.click()
        },

        exportExcel() {
            const h = ['Référence','Contrat','Type','Date Paiement','Montant','Mode','Banque','Statut'];
            const rows = this.filteredData.map(i => [i.reference,i.contrat_reference,i.type_paiement_label,i.date_paiement_formatted,i.montant,i.mode_paiement_label,i.banque_label||'-',i.statut_label]);
            const xl = [h.join('\t'),...rows.map(r=>r.join('\t'))].join('\n');
            const b = new Blob(['\uFEFF'+xl],{type:'application/vnd.ms-excel'});
            const l = document.createElement('a'); l.href=URL.createObjectURL(b); l.download=`paiements_${new Date().toISOString().split('T')[0]}.xls`; l.click()
        }
    }))
})
</script>

<style>
[x-cloak] { display: none !important; }
</style>

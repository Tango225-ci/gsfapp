<div x-data="dataTable" x-init="init()">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
        <!-- Header avec recherche et boutons -->
        <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Tableau Récapitulatif des Clients</h2>

                <div class="flex items-center gap-3 flex-wrap">
                    <!-- Filtre par statut -->
                    <select x-model="filtreStatut" @change="currentPage = 1"
                        class="w-40 rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">Tous les statuts</option>
                        <option value="ACTIF">Actifs</option>
                        <option value="SUSPENDU">Suspendus</option>
                        <option value="PROSPECT">Prospects</option>
                    </select>

                    <!-- Barre de recherche -->
                    <div class="relative w-64">
                        <input type="text" x-model="search" placeholder="Rechercher référence, nom, email..."
                            class="w-full pl-4 pr-10 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white text-sm">
                        <svg class="absolute right-3 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>

                    <!-- Bouton Nouveau Client -->
                    <button @click="newClient" x-show="window.gsfUser.can.clients.create"
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
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total clients</p>
                <p class="mt-2 text-4xl font-bold text-gray-800 dark:text-white" x-text="stats.total"></p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm text-center">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Actifs</p>
                <p class="mt-2 text-4xl font-bold text-green-600 dark:text-green-400" x-text="stats.actifs"></p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm text-center">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Prospects</p>
                <p class="mt-2 text-4xl font-bold text-yellow-600 dark:text-yellow-400" x-text="stats.prospects"></p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm text-center">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Suspendus</p>
                <p class="mt-2 text-4xl font-bold text-red-600 dark:text-red-400" x-text="stats.suspendus"></p>
            </div>
        </div>

        <!-- Overlay formulaire création client -->
        <template x-teleport="body">
        <div x-show="showClientForm" x-cloak @keydown.escape.window="closeClientForm" class="fixed inset-0 z-[100000] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div @click.stop class="relative w-full max-w-3xl max-h-[90vh] overflow-y-auto rounded-2xl bg-white shadow-2xl dark:bg-gray-900">

                <!-- En-tête -->
                <div class="sticky top-0 border-b border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900">
                    <div class="flex items-center justify-between px-6 py-4">
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white" x-text="editingClient ? 'Modifier le client' : 'Nouveau client'"></h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400" x-text="editingClient ? 'Modifiez les informations du client' : 'Ajoutez un client avec un formulaire clair et rapide'"></p>
                        </div>
                        <button type="button" @click="closeClientForm"
                            class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Onglets (uniquement à la création d'un client) -->
                    <div x-show="!editingClient" class="flex gap-1 px-6">
                        <button type="button" @click="modalTab = 'form'"
                            :class="modalTab === 'form' ? 'border-blue-600 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 dark:text-gray-400'"
                            class="px-3 py-2 text-sm font-medium border-b-2 -mb-px transition-colors">
                            1. Informations
                        </button>
                        <button type="button" @click="createdAccountInfo && (modalTab = 'account')"
                            :disabled="!createdAccountInfo"
                            :class="modalTab === 'account' ? 'border-blue-600 text-blue-600 dark:text-blue-400' : (createdAccountInfo ? 'border-transparent text-gray-500 dark:text-gray-400' : 'border-transparent text-gray-300 dark:text-gray-600 cursor-not-allowed')"
                            class="px-3 py-2 text-sm font-medium border-b-2 -mb-px transition-colors">
                            2. Accès portail
                        </button>
                    </div>
                </div>

                <!-- Onglet 2 : Accès portail (affiché juste après la création du client) -->
                <div x-show="modalTab === 'account'" class="p-6">
                    <div class="rounded-lg border border-green-200 bg-green-50 p-4 mb-5 dark:border-green-800 dark:bg-green-900/20">
                        <p class="text-sm font-medium text-green-800 dark:text-green-300">
                            Client créé avec succès — son compte pour l'espace client (portail) a été généré.
                        </p>
                        <p class="mt-1 text-xs text-green-700 dark:text-green-400">
                            Communiquez les informations ci-dessous au client. Il devra changer ce mot de passe à sa première connexion.
                        </p>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Identifiant de connexion (téléphone)</label>
                            <div class="flex items-center gap-2">
                                <code class="flex-1 rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm font-mono select-all dark:border-gray-600 dark:bg-gray-800 dark:text-white" x-text="createdAccountInfo?.identifiant"></code>
                                <button type="button" @click="copyToClipboard(createdAccountInfo?.identifiant)" title="Copier"
                                    class="rounded-lg border border-gray-300 p-2 text-gray-500 hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-800">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Mot de passe temporaire</label>
                            <div class="flex items-center gap-2">
                                <code class="flex-1 rounded-lg border border-amber-300 bg-amber-50 px-3 py-2 text-sm font-mono select-all dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-300" x-text="createdAccountInfo?.password"></code>
                                <button type="button" @click="copyToClipboard(createdAccountInfo?.password)" title="Copier"
                                    class="rounded-lg border border-gray-300 p-2 text-gray-500 hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-800">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                </button>
                            </div>
                            <p class="mt-1 text-xs text-gray-400">À changer obligatoirement lors de la première connexion du client.</p>
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Lien de connexion au portail</label>
                            <div class="flex items-center gap-2">
                                <a :href="createdAccountInfo?.login_url" target="_blank"
                                    class="flex-1 rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm font-mono text-blue-600 hover:underline truncate dark:border-gray-600 dark:bg-gray-800"
                                    x-text="createdAccountInfo?.login_url"></a>
                                <button type="button" @click="copyToClipboard(createdAccountInfo?.login_url)" title="Copier"
                                    class="rounded-lg border border-gray-300 p-2 text-gray-500 hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-800">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end border-t border-gray-200 pt-6 dark:border-gray-700">
                        <button type="button" @click="closeClientForm"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Terminé
                        </button>
                    </div>
                </div>

                <!-- Formulaire -->
                <form x-show="modalTab === 'form'" @submit.prevent="saveClient" class="p-6">
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
                        <!-- Nom complet -->
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Nom complet *</label>
                            <input type="text" x-model="form.nom_prenoms" required
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                placeholder="Jean Dupont">
                        </div>

                        <!-- Contact -->
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Contact * (10 chiffres)</label>
                            <input type="tel" x-model="form.contact" required @input="validatePhoneNumber" maxlength="10"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                placeholder="0123456789">
                            <p class="mt-1 text-xs text-gray-500">Le numéro doit contenir exactement 10 chiffres</p>
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                            <input type="email" x-model="form.email"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                placeholder="client@example.com">
                        </div>

                        <!-- Statut -->
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Statut</label>
                            <select x-model="form.statut"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                                <option value="ACTIF">Actif</option>
                                <option value="SUSPENDU">Suspendu</option>
                                <option value="PROSPECT">Prospect</option>
                            </select>
                        </div>

                        <!-- Adresse -->
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Adresse</label>
                            <input type="text" x-model="form.adresse"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                placeholder="Adresse">
                        </div>

                        <!-- Pièce d'identité -->
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Pièce d'identité</label>
                            <select x-model="form.piece_identite"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                                <option value="">Sélectionnez</option>
                                <option value="Passeport">Passeport</option>
                                <option value="CNI">CNI</option>
                                <option value="Carte Consulaire">Carte Consulaire</option>
                            </select>
                        </div>

                        <!-- Numéro de pièce -->
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Numéro de pièce</label>
                            <input type="text" x-model="form.numero_piece"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                placeholder="123456789">
                        </div>

                        <!-- Date de validité -->
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Date de validité</label>
                            <input type="date" x-model="form.date_validite"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        </div>
                    </div>

                    <!-- Boutons -->
                    <div class="mt-6 flex gap-3 border-t border-gray-200 pt-6 dark:border-gray-700">
                        <button type="button" @click="closeClientForm"
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">
                            Annuler
                        </button>
                        <button type="submit"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            <span x-text="editingClient ? 'Mettre à jour' : 'Ajouter client'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        </template>

        <!-- Overlay formulaire affichage détails client -->
        <template x-teleport="body">
        <div x-show="showDetailsModal" x-cloak @keydown.escape.window="closeDetailsModal" class="fixed inset-0 z-[100000] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div @click.stop class="relative w-full max-w-2xl max-h-[85vh] overflow-y-auto rounded-xl bg-white shadow-2xl dark:bg-gray-900">

                <div class="sticky top-0 flex items-center justify-between border-b border-gray-200 bg-white px-5 py-3 dark:border-gray-700 dark:bg-gray-900">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Détails du client</h3>
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
                            <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Référence client</label>
                            <input type="text" x-model="selectedClient?.reference_client" readonly disabled
                                class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-1.5 text-sm font-mono font-semibold text-blue-700 cursor-not-allowed dark:border-gray-600 dark:bg-gray-800 dark:text-blue-300">
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Statut</label>
                            <input type="text" x-model="selectedClient?.status" readonly disabled
                                class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-1.5 text-sm text-gray-600 cursor-not-allowed dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400">
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Nom complet</label>
                            <input type="text" x-model="selectedClient?.name" readonly disabled
                                class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-1.5 text-sm text-gray-600 cursor-not-allowed dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400">
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Contact</label>
                            <input type="text" x-model="selectedClient?.contact" readonly disabled
                                class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-1.5 text-sm text-gray-600 cursor-not-allowed dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400">
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Email</label>
                            <input type="email" x-model="selectedClient?.email" readonly disabled
                                class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-1.5 text-sm text-gray-600 cursor-not-allowed dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400">
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Adresse</label>
                            <input type="text" x-model="selectedClient?.adresse" readonly disabled
                                class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-1.5 text-sm text-gray-600 cursor-not-allowed dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400"
                                placeholder="Non renseignée">
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Solde compte</label>
                            <input type="text" x-model="selectedClient?.solde_compte" readonly disabled
                                class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-1.5 text-sm text-gray-600 cursor-not-allowed dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400"
                                placeholder="0 FCFA">
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Pièce d'identité</label>
                            <input type="text" x-model="selectedClient?.piece_identite" readonly disabled
                                class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-1.5 text-sm text-gray-600 cursor-not-allowed dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400">
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Numéro de pièce</label>
                            <input type="text" x-model="selectedClient?.numero_piece" readonly disabled
                                class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-1.5 text-sm text-gray-600 cursor-not-allowed dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400">
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Date de validité</label>
                            <input type="text" x-model="selectedClient?.date_validite" readonly disabled
                                class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-1.5 text-sm text-gray-600 cursor-not-allowed dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400"
                                placeholder="Non renseignée">
                        </div>
                    </div>

                    <div class="mt-5 flex gap-3 border-t border-gray-200 pt-4 dark:border-gray-700">
                        <button type="button" @click="closeDetailsModal"
                            class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">
                            Fermer
                        </button>
                        <button type="button" @click="resetPortailPassword(selectedClient)" x-show="window.gsfUser.can.clients.update"
                            :disabled="resettingPortail"
                            class="rounded-lg border border-amber-300 bg-amber-50 px-3 py-1.5 text-sm font-medium text-amber-700 hover:bg-amber-100 disabled:opacity-50 dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-400">
                            <span x-show="!resettingPortail">Réinitialiser mot de passe portail</span>
                            <span x-show="resettingPortail">Réinitialisation…</span>
                        </button>
                        <button type="button" @click="editFromDetails"
                            class="rounded-lg bg-blue-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Modifier le client
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">RÉFÉRENCE</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">NOM</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">EMAIL</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">CONTACT</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">ADRESSE</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">STATUT</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <template x-for="item in paginatedData" :key="item.id">
                        <tr @click="selectRow(item.id)"
                            :class="selectedRow === item.id ? 'bg-gray-200 dark:bg-gray-700 border-l-4 border-blue-500 shadow-sm' : 'hover:bg-gray-50 dark:hover:bg-gray-700/50'"
                            class="cursor-pointer transition-all duration-200">
                            <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-semibold text-blue-700 ring-1 ring-inset ring-blue-700/10 dark:bg-blue-900/20 dark:text-blue-300 font-mono"
                                        x-text="item.reference_client"></span>
                                </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center">
                                        <span class="text-sm font-medium text-white" x-text="item.initials"></span>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white" x-text="item.name"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400" x-text="item.email || '-'"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400" x-text="item.contact"></td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400" x-text="item.adresse || '-'"></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span x-text="item.status"
                                    :class="{
                                        'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-200': item.status === 'Actif',
                                        'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-200': item.status === 'Suspendu',
                                        'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-200': item.status === 'Prospect'
                                    }"
                                    class="px-2 py-1 rounded-full text-xs font-semibold"></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button @click.stop="viewDetails(item)"
                                    class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300 mr-3 transition-colors" title="Détails">
                                    <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </button>
                                <button @click.stop="editItem(item)" x-show="window.gsfUser.can.clients.update"
                                    class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 mr-3 transition-colors" title="Modifier">
                                    <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>
                                <button @click.stop="deleteItem(item.id)" x-show="window.gsfUser.can.clients.delete"
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
                            <p class="text-lg font-medium">Aucun client trouvé</p>
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
        currentPage: 1,
        itemsPerPage: 10,
        selectedRow: null,
        showClientForm: false,
        showDetailsModal: false,
        selectedClient: null,
        editingClient: null,
        formErrors: [],
        resettingPortail: false,
        modalTab: 'form',
        createdAccountInfo: null,
        form: {
            nom_prenoms: '',
            contact: '',
            email: '',
            adresse: '',
            piece_identite: '',
            numero_piece: '',
            date_validite: '',
            statut: 'ACTIF',
        },
        items: [],

        get stats() {
            const total = this.items.length;
            const actifs = this.items.filter(i => i.status === 'Actif').length;
            const prospects = this.items.filter(i => i.status === 'Prospect').length;
            const suspendus = this.items.filter(i => i.status === 'Suspendu').length;
            return { total, actifs, prospects, suspendus };
        },

        get filteredData() {
            let data = [...this.items];

            if (this.filtreStatut) {
                const statutMap = {
                    'ACTIF': 'Actif',
                    'SUSPENDU': 'Suspendu',
                    'PROSPECT': 'Prospect'
                };
                data = data.filter(i => i.status === statutMap[this.filtreStatut]);
            }

            if (this.search) {
                const searchLower = this.search.toLowerCase();
                data = data.filter(item =>
                    item.name.toLowerCase().includes(searchLower) ||
                    item.reference_client.toLowerCase().includes(searchLower) ||
                    (item.email && item.email.toLowerCase().includes(searchLower)) ||
                    item.contact.includes(searchLower) ||
                    (item.adresse && item.adresse.toLowerCase().includes(searchLower))
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

        generateInitials(name) {
            if (!name) return '?';
            return name
                .split(' ')
                .filter(part => part.length)
                .slice(0, 2)
                .map(part => part[0].toUpperCase())
                .join('');
        },

        escapeHtml(value) {
            const div = document.createElement('div');
            div.textContent = value ?? '';
            return div.innerHTML;
        },

        validatePhoneNumber() {
            this.form.contact = this.form.contact.replace(/\D/g, '');
            if (this.form.contact.length > 10) {
                this.form.contact = this.form.contact.slice(0, 10);
            }
        },

        async init() {
            await this.loadClientsFromDB();
        },

        async loadClientsFromDB() {
            try {
                const response = await fetch('/api/clients');
                if (!response.ok) throw new Error('HTTP ' + response.status);
                const data = await response.json();

                if (Array.isArray(data) && data.length > 0) {
                    this.items = data.map(client => ({
                        id: client.id,
                        reference_client: client.reference_client || ('CLI-ID' + client.id),
                        name: client.nom_prenoms,
                        email: client.email || '',
                        contact: client.contact,
                        adresse: client.adresse || '',
                        piece_identite: client.piece_identite || '',
                        numero_piece: client.numero_piece || '',
                        date_validite: client.date_validite || '',
                        solde_compte: client.solde_compte ? client.solde_compte.toLocaleString('fr-FR') + ' FCFA' : '0 FCFA',
                        status: client.statut === 'ACTIF' ? 'Actif' :
                                (client.statut === 'SUSPENDU' ? 'Suspendu' : 'Prospect'),
                        initials: this.generateInitials(client.nom_prenoms),
                    }));
                } else {
                    this.items = [];
                }
                this.currentPage = 1;
            } catch (error) {
                console.error('Erreur chargement clients:', error);
                this.items = [];
            }
        },

        newClient() {
            this.editingClient = null;
            this.form = {
                nom_prenoms: '',
                contact: '',
                email: '',
                adresse: '',
                piece_identite: '',
                numero_piece: '',
                date_validite: '',
                statut: 'ACTIF',
            };
            this.formErrors = [];
            this.modalTab = 'form';
            this.createdAccountInfo = null;
            this.showClientForm = true;
        },

        editItem(item) {
            this.editingClient = item;
            this.form = {
                nom_prenoms: item.name,
                contact: item.contact,
                email: item.email || '',
                adresse: item.adresse || '',
                piece_identite: item.piece_identite || '',
                numero_piece: item.numero_piece || '',
                date_validite: item.date_validite || '',
                statut: item.status === 'Actif' ? 'ACTIF' : (item.status === 'Suspendu' ? 'SUSPENDU' : 'PROSPECT'),
            };
            this.formErrors = [];
            this.modalTab = 'form';
            this.createdAccountInfo = null;
            this.showClientForm = true;
        },

        closeClientForm() {
            this.showClientForm = false;
            this.editingClient = null;
            this.formErrors = [];
            this.modalTab = 'form';
            this.createdAccountInfo = null;
        },

        async copyToClipboard(text) {
            if (!text) return;
            try {
                await navigator.clipboard.writeText(text);
                window.gsfToast('Copié dans le presse-papiers.', 'success', 2000);
            } catch (error) {
                console.error('Erreur copie:', error);
            }
        },

        closeDetailsModal() {
            this.showDetailsModal = false;
            this.selectedClient = null;
        },

        viewDetails(item) {
            this.selectedClient = item;
            this.showDetailsModal = true;
        },

        editFromDetails() {
            const item = this.selectedClient;
            this.closeDetailsModal();
            this.editItem(item);
        },

        async saveClient() {
            this.formErrors = [];

            if (!this.form.nom_prenoms.trim()) {
                this.formErrors.push('Le nom complet est requis.');
            }
            if (!this.form.contact.trim()) {
                this.formErrors.push('Le contact est requis.');
            } else if (this.form.contact.length !== 10) {
                this.formErrors.push('Le numéro de téléphone doit contenir exactement 10 chiffres.');
            }

            if (this.formErrors.length) return;

            const confirmed = await window.gsfConfirm({
                title: this.editingClient ? 'Modifier le client' : 'Créer le client',
                message: this.editingClient
                    ? `Confirmer la modification de <strong>${this.editingClient.nom_prenoms}</strong> ?`
                    : `Confirmer la création du nouveau client <strong>${this.form.nom_prenoms}</strong> ?`,
                type: 'info',
                confirmText: this.editingClient ? 'Modifier' : 'Créer'
            });
            if (!confirmed) return;

            let url = '/api/clients';
            let method = 'POST';

            if (this.editingClient) {
                url = `/api/clients/${this.editingClient.id}`;
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
                    await this.loadClientsFromDB();

                    if (this.editingClient) {
                        this.closeClientForm();
                        window.gsfToast('Client modifié avec succès.', 'success');
                    } else {
                        // Création : on garde la modale ouverte pour afficher l'accès portail (onglet 2)
                        this.createdAccountInfo = data.portail || null;
                        this.modalTab = this.createdAccountInfo ? 'account' : 'form';
                        window.gsfToast('Client créé avec succès.', 'success');
                        if (!this.createdAccountInfo) {
                            this.closeClientForm();
                        }
                    }
                } else {
                    window.gsfToast('Erreur: ' + (data.message || 'Erreur inconnue'), 'error');
                }
            } catch (error) {
                console.error('Erreur:', error);
                window.gsfToast('Erreur: ' + error.message, 'error');
            }
        },

        async resetPortailPassword(item) {
            if (!item) return;

            const confirmed = await window.gsfConfirm({
                title: 'Réinitialiser le mot de passe portail',
                message: `Générer un nouveau mot de passe temporaire pour <strong>${item.name}</strong> ?<br><span class="text-xs text-gray-500 mt-1 block">Le client devra en choisir un nouveau à sa prochaine connexion. Utile aussi pour activer le compte d'un client qui n'a jamais reçu ses identifiants.</span>`,
                type: 'info',
                confirmText: 'Réinitialiser'
            });
            if (!confirmed) return;

            this.resettingPortail = true;
            try {
                const response = await fetch(`/api/clients/${item.id}/reset-portail`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    await window.gsfConfirm({
                        title: 'Mot de passe régénéré',
                        message: `Identifiant : <strong>${this.escapeHtml(item.contact)}</strong> (ou email)<br>Mot de passe temporaire :<br>
                            <code class="block mt-2 p-2 bg-gray-100 dark:bg-gray-800 rounded text-sm font-mono select-all">${this.escapeHtml(data.password)}</code>
                            <span class="text-xs text-gray-500 mt-2 block">Communiquez-le au client — il devra le changer à sa première connexion sur l'espace client.</span>`,
                        type: 'info',
                        confirmText: 'OK'
                    });
                    window.gsfToast('Mot de passe portail réinitialisé.', 'success');
                } else {
                    window.gsfToast('Erreur: ' + (data.message || 'Erreur inconnue'), 'error');
                }
            } catch (error) {
                console.error('Erreur:', error);
                window.gsfToast('Erreur: ' + error.message, 'error');
            } finally {
                this.resettingPortail = false;
            }
        },

        async deleteItem(id) {
            const item = this.items.find(c => c.id === id);
            const confirmed = await window.gsfConfirm({
                title: 'Supprimer le client',
                message: `Êtes-vous sûr de vouloir supprimer <strong>${item?.nom_prenoms || 'ce client'}</strong> ?<br><span class="text-xs text-red-500 mt-1 block">Cette action est irréversible.</span>`,
                type: 'danger',
                confirmText: 'Supprimer'
            });
            if (!confirmed) return;

            try {
                const response = await fetch(`/api/clients/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    await this.loadClientsFromDB();
                    window.gsfToast('Client supprimé avec succès', 'success');
                } else {
                    window.gsfToast('Erreur lors de la suppression', 'error');
                }
            } catch (error) {
                console.error('Erreur:', error);
                window.gsfToast('Une erreur est survenue', 'error');
            }
        },

        exportCSV() {
            const headers = ['Référence', 'Nom', 'Email', 'Contact', 'Adresse', 'Statut', 'Solde compte'];
            const rows = this.filteredData.map(item => [
                item.reference_client,
                item.name,
                item.email,
                item.contact,
                item.adresse || '',
                item.status,
                item.solde_compte || '0 FCFA'
            ]);

            const csvContent = [
                headers.join(','),
                ...rows.map(row => row.map(cell => `"${cell}"`).join(','))
            ].join('\n');

            const blob = new Blob(['\uFEFF' + csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `clients_${new Date().toISOString().split('T')[0]}.csv`;
            link.click();
            URL.revokeObjectURL(link.href);
        },

        exportExcel() {
            const headers = ['Référence', 'Nom', 'Email', 'Contact', 'Adresse', 'Statut', 'Solde compte'];
            const rows = this.filteredData.map(item => [
                item.reference_client,
                item.name,
                item.email,
                item.contact,
                item.adresse || '',
                item.status,
                item.solde_compte || '0 FCFA'
            ]);

            const excelContent = [
                headers.join('\t'),
                ...rows.map(row => row.join('\t'))
            ].join('\n');

            const blob = new Blob(['\uFEFF' + excelContent], { type: 'application/vnd.ms-excel' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `clients_${new Date().toISOString().split('T')[0]}.xls`;
            link.click();
            URL.revokeObjectURL(link.href);
        }
    }));
});
</script>

<style>
[x-cloak] { display: none !important; }
</style>


<div x-data="utilisateurDataTable" x-init="init()">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
        <!-- Header avec recherche et boutons -->
        <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
            <div class="flex flex-col lg:flex-row justify-end items-start lg:items-center gap-4">

                <div class="flex w-full lg:w-auto items-center gap-3 flex-wrap">
                    <!-- Filtre par rôle -->
                    <select x-model="filtreRole" @change="currentPage = 1"
                        class="w-36 rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">Tous les rôles</option>
                        <option value="ADMIN">Administrateurs</option>
                        <option value="ASSISTANT">Assistants</option>
                        <option value="DIRECTEUR">Directeurs</option>
                    </select>

                    <!-- Filtre par statut -->
                    <select x-model="filtreStatut" @change="currentPage = 1"
                        class="w-32 rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">Tous</option>
                        <option value="actif">Actifs</option>
                        <option value="inactif">Inactifs</option>
                    </select>

                    <!-- Barre de recherche -->
                    <div class="relative w-full sm:w-56">
                        <input type="text" x-model="search" placeholder="Rechercher nom, login, email..."
                            class="w-full pl-4 pr-10 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white text-sm">
                        <svg class="absolute right-3 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>

                    <!-- Bouton Nouvel utilisateur -->
                    <button @click="openCreateModal"
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
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 p-4 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm text-center">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total utilisateurs</p>
                <p class="mt-2 text-2xl sm:text-3xl xl:text-4xl font-bold break-words text-gray-800 dark:text-white" x-text="stats.total"></p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm text-center">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Actifs</p>
                <p class="mt-2 text-2xl sm:text-3xl xl:text-4xl font-bold break-words text-green-600 dark:text-green-400" x-text="stats.actifs"></p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm text-center">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Inactifs</p>
                <p class="mt-2 text-2xl sm:text-3xl xl:text-4xl font-bold break-words text-red-600 dark:text-red-400" x-text="stats.inactifs"></p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm text-center">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Administrateurs</p>
                <p class="mt-2 text-2xl sm:text-3xl xl:text-4xl font-bold break-words text-purple-600 dark:text-purple-400" x-text="stats.admins"></p>
            </div>
        </div>

        <!-- Modal Création/Modification Utilisateur -->
        <template x-teleport="body">
        <div x-show="showFormModal" x-cloak @keydown.escape.window="closeFormModal" class="fixed inset-0 z-[100000] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div @click.stop class="relative w-full max-w-lg max-h-[90vh] overflow-y-auto rounded-xl bg-white shadow-2xl dark:bg-gray-900">

                <div class="sticky top-0 flex items-center justify-between border-b border-gray-200 bg-white px-5 py-3 dark:border-gray-700 dark:bg-gray-900 z-10">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white" x-text="isEditing ? 'Modifier l\'utilisateur' : 'Nouvel utilisateur'"></h3>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400" x-text="isEditing ? 'Modifier les informations du compte' : 'Créer un nouveau compte utilisateur'"></p>
                    </div>
                    <button type="button" @click="closeFormModal"
                        class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="saveUtilisateur" class="p-5">
                    <div class="grid gap-4">
                        <!-- Nom -->
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Nom complet <span class="text-red-500">*</span>
                            </label>
                            <input type="text" x-model="form.nom" required
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Ex: Jean Dupont">
                        </div>

                        <!-- Login -->
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Nom d'utilisateur <span class="text-red-500">*</span>
                            </label>
                            <input type="text" x-model="form.login" required
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Ex: jean.dupont">
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Adresse email <span class="text-red-500">*</span>
                            </label>
                            <input type="email" x-model="form.email" required
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="exemple@email.com">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Sera utilisé pour la récupération de mot de passe
                            </p>
                        </div>

                        <!-- Mot de passe -->
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                <span x-show="!isEditing">Mot de passe temporaire</span>
                                <span x-show="isEditing">Mot de passe</span>
                                <span x-show="!isEditing" class="text-red-500">*</span>
                                <span x-show="isEditing" class="text-xs text-gray-500">(optionnel)</span>
                            </label>
                            <div class="relative" x-data="{ showPassword: false }">
                                <input :type="showPassword ? 'text' : 'password'"
                                    x-model="form.password"
                                    :required="!isEditing"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 pr-10 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    :placeholder="isEditing ? 'Laisser vide pour ne pas changer' : 'Minimum 6 caractères'">
                                <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600">
                                    <svg x-show="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    <svg x-show="showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                                    </svg>
                                </button>
                            </div>
                            <p x-show="isEditing" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Laissez vide pour conserver le mot de passe actuel. Si vous en définissez un nouveau, l'utilisateur devra le changer à sa prochaine connexion.
                            </p>
                        </div>
                        <!-- Note première connexion (création uniquement) -->
                        <div x-show="!isEditing" class="flex items-start gap-2 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg">
                            <svg class="w-4 h-4 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-xs text-amber-700 dark:text-amber-300">
                                L'utilisateur devra <strong>obligatoirement changer ce mot de passe</strong> lors de sa première connexion.
                            </p>
                        </div>

                        <!-- Confirmation du mot de passe -->
                        <div x-show="!isEditing || form.password.length > 0">
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Confirmer le mot de passe
                                <span x-show="!isEditing" class="text-red-500">*</span>
                                <span x-show="isEditing && form.password.length > 0" class="text-red-500">*</span>
                            </label>
                            <div class="relative" x-data="{ showConfirmPassword: false }">
                                <input :type="showConfirmPassword ? 'text' : 'password'"
                                    x-model="form.password_confirmation"
                                    :required="!isEditing || form.password.length > 0"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 pr-10 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    :class="{
                                        'border-green-500 focus:ring-green-500': form.password_confirmation && form.password === form.password_confirmation,
                                        'border-red-500 focus:ring-red-500': form.password_confirmation && form.password !== form.password_confirmation
                                    }"
                                    placeholder="Répétez le mot de passe">
                                <button type="button" @click="showConfirmPassword = !showConfirmPassword" class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600">
                                    <svg x-show="!showConfirmPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    <svg x-show="showConfirmPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                                    </svg>
                                </button>
                            </div>
                            <!-- Indicateur de correspondance -->
                            <div x-show="form.password_confirmation.length > 0" class="mt-1 flex items-center gap-1">
                                <template x-if="form.password === form.password_confirmation">
                                    <span class="text-xs text-green-600 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        Les mots de passe correspondent
                                    </span>
                                </template>
                                <template x-if="form.password_confirmation && form.password !== form.password_confirmation">
                                    <span class="text-xs text-red-600 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                        Les mots de passe ne correspondent pas
                                    </span>
                                </template>
                            </div>
                        </div>

                        <!-- Rôle -->
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Rôle <span class="text-red-500">*</span>
                            </label>
                            <select x-model="form.role" required
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Sélectionner un rôle</option>
                                <option value="ADMIN">Administrateur</option>
                                <option value="ASSISTANT">Assistant</option>
                                <option value="DIRECTEUR">Directeur</option>
                            </select>
                        </div>

                        <!-- Statut -->
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Statut du compte</label>
                            <div class="flex items-center gap-4">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" x-model="form.statut" :value="true"
                                        class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">Actif</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" x-model="form.statut" :value="false"
                                        class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">Inactif</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Messages d'erreur -->
                    <div x-show="formErrors.length > 0" class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                        <div class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <ul class="list-disc list-inside text-sm text-red-600">
                                <template x-for="error in formErrors" :key="error">
                                    <li x-text="error"></li>
                                </template>
                            </ul>
                        </div>
                    </div>

                    <!-- Boutons -->
                    <div class="mt-6 flex gap-3 border-t border-gray-200 pt-4 dark:border-gray-700">
                        <button type="button" @click="closeFormModal"
                            class="flex-1 rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800 transition-colors">
                            Annuler
                        </button>
                        <button type="submit"
                            class="flex-1 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                            :disabled="isSubmitting || (form.password.length > 0 && form.password !== form.password_confirmation)">
                            <span x-show="!isSubmitting" class="flex items-center justify-center gap-2">
                                <svg x-show="!isEditing" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                <svg x-show="isEditing" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span x-text="isEditing ? 'Enregistrer les modifications' : 'Créer l\'utilisateur'"></span>
                            </span>
                            <span x-show="isSubmitting" class="flex items-center justify-center gap-2">
                                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                En cours...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        </template>

        <!-- Overlay Confirmation Suppression -->
        <template x-teleport="body">
        <div x-show="showDeleteModal" x-cloak @keydown.escape.window="closeDeleteModal" class="fixed inset-0 z-[100000] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div @click.stop class="relative w-full max-w-md rounded-xl bg-white shadow-2xl dark:bg-gray-900 p-6">
                <div class="text-center">
                    <svg class="mx-auto h-12 w-12 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    <h3 class="mt-2 text-lg font-semibold text-gray-900 dark:text-white">Confirmer la suppression</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Êtes-vous sûr de vouloir supprimer l'utilisateur <strong x-text="utilisateurToDelete?.nom"></strong> ?
                    </p>
                    <p class="mt-2 text-xs text-red-600 dark:text-red-400">Cette action est irréversible</p>
                </div>
                <div class="mt-6 flex justify-center gap-3">
                    <button @click="closeDeleteModal"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">
                        Annuler
                    </button>
                    <button @click="confirmDelete"
                        class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 transition-colors">
                        Supprimer définitivement
                    </button>
                </div>
            </div>
        </div>
        </template>

        <!-- Tableau -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">NOM</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">LOGIN</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">EMAIL</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">RÔLE</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">STATUT</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">CRÉÉ LE</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <template x-for="utilisateur in paginatedData" :key="utilisateur.id">
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-200">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center">
                                        <span class="text-sm font-medium text-white" x-text="utilisateur.nom.charAt(0).toUpperCase()"></span>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white" x-text="utilisateur.nom"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400" x-text="utilisateur.login"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <a :href="'mailto:' + utilisateur.email" x-text="utilisateur.email" class="text-blue-600 dark:text-blue-400 hover:underline"></a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span x-text="utilisateur.role_label"
                                    :class="{
                                        'bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-200': utilisateur.role === 'ADMIN',
                                        'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200': utilisateur.role === 'ASSISTANT',
                                        'bg-indigo-100 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-200': utilisateur.role === 'DIRECTEUR'
                                    }"
                                    class="px-2 py-1 rounded-full text-xs font-semibold"></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button @click="toggleUtilisateurStatus(utilisateur)"
                                    :class="utilisateur.statut ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-200'"
                                    class="px-2 py-1 rounded-full text-xs font-semibold hover:opacity-80 transition-opacity cursor-pointer">
                                    <span x-text="utilisateur.statut_label"></span>
                                </button>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400" x-text="utilisateur.date_creation"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button @click="openEditModal(utilisateur)"
                                    class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 mr-3 transition-colors" title="Modifier">
                                    <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>
                                <button @click="openDeleteModal(utilisateur)"
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
                            <p class="text-lg font-medium">Aucun utilisateur trouvé</p>
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
    Alpine.data('utilisateurDataTable', () => ({
        search: '',
        filtreRole: '',
        filtreStatut: '',
        currentPage: 1,
        itemsPerPage: 10,
        showFormModal: false,
        showDeleteModal: false,
        isEditing: false,
        isSubmitting: false,
        editingUtilisateurId: null,
        utilisateurToDelete: null,
        formErrors: [],
        utilisateurs: [],

        form: {
            nom: '',
            login: '',
            email: '',
            password: '',
            password_confirmation: '',
            role: '',
            statut: true
        },

        get stats() {
            const total = this.utilisateurs.length;
            const actifs = this.utilisateurs.filter(u => u.statut).length;
            const inactifs = this.utilisateurs.filter(u => !u.statut).length;
            const admins = this.utilisateurs.filter(u => u.role === 'ADMIN').length;

            return { total, actifs, inactifs, admins };
        },

        get filteredData() {
            let data = [...this.utilisateurs];

            if (this.filtreRole) {
                data = data.filter(u => u.role === this.filtreRole);
            }

            if (this.filtreStatut) {
                data = data.filter(u => {
                    return this.filtreStatut === 'actif' ? u.statut : !u.statut;
                });
            }

            if (this.search) {
                const searchLower = this.search.toLowerCase();
                data = data.filter(u =>
                    (u.nom && u.nom.toLowerCase().includes(searchLower)) ||
                    (u.login && u.login.toLowerCase().includes(searchLower)) ||
                    (u.email && u.email.toLowerCase().includes(searchLower))
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
            }
        },

        prevPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
            }
        },

        async init() {
            await this.loadUtilisateurs();
        },

        async loadUtilisateurs() {
            try {
                const response = await fetch('/api/utilisateurs');
                if (response.ok) {
                    const data = await response.json();
                    if (data.success && Array.isArray(data.utilisateurs)) {
                        this.utilisateurs = data.utilisateurs;
                    }
                }
                this.currentPage = 1;
            } catch (error) {
                console.error('Erreur chargement utilisateurs:', error);
            }
        },

        openCreateModal() {
            this.isEditing = false;
            this.editingUtilisateurId = null;
            this.form = {
                nom: '',
                login: '',
                email: '',
                password: '',
                password_confirmation: '',
                role: '',
                statut: true
            };
            this.formErrors = [];
            this.showFormModal = true;
        },

        openEditModal(utilisateur) {
            this.isEditing = true;
            this.editingUtilisateurId = utilisateur.id;
            this.form = {
                nom: utilisateur.nom,
                login: utilisateur.login,
                email: utilisateur.email || '',
                password: '',
                password_confirmation: '',
                role: utilisateur.role,
                statut: utilisateur.statut
            };
            this.formErrors = [];
            this.showFormModal = true;
        },

        closeFormModal() {
            this.showFormModal = false;
            this.formErrors = [];
        },

        openDeleteModal(utilisateur) {
            this.utilisateurToDelete = utilisateur;
            this.showDeleteModal = true;
        },

        closeDeleteModal() {
            this.showDeleteModal = false;
            this.utilisateurToDelete = null;
        },

        async saveUtilisateur() {
            this.isSubmitting = true;
            this.formErrors = [];

            if (this.form.password || !this.isEditing) {
                if (this.form.password !== this.form.password_confirmation) {
                    this.formErrors = ['Les mots de passe ne correspondent pas'];
                    this.isSubmitting = false;
                    return;
                }
            }

            const confirmed = await window.gsfConfirm({
                title: this.isEditing ? 'Modifier l\'utilisateur' : 'Créer l\'utilisateur',
                message: this.isEditing
                    ? `Confirmer la modification de <strong>${this.form.nom}</strong> ?`
                    : `Confirmer la création de l'utilisateur <strong>${this.form.nom}</strong> ?`,
                type: 'info',
                confirmText: this.isEditing ? 'Modifier' : 'Créer'
            });
            if (!confirmed) { this.isSubmitting = false; return; }

            try {
                const url = this.isEditing
                    ? `/api/utilisateurs/${this.editingUtilisateurId}`
                    : '/api/utilisateurs';

                const method = this.isEditing ? 'PUT' : 'POST';

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
                    await this.loadUtilisateurs();
                    this.closeFormModal();

                    const message = this.isEditing
                        ? 'Utilisateur modifié avec succès !'
                        : 'Utilisateur créé avec succès !';
                    window.gsfToast(message, 'success');
                } else {
                    if (data.errors) {
                        this.formErrors = Object.values(data.errors).flat();
                    } else {
                        window.gsfToast(data.message || 'Une erreur est survenue', 'error');
                    }
                }
            } catch (error) {
                console.error('Erreur:', error);
                window.gsfToast('Une erreur est survenue lors de la communication avec le serveur', 'error');
            } finally {
                this.isSubmitting = false;
            }
        },

        async confirmDelete() {
            if (!this.utilisateurToDelete) return;

            try {
                const response = await fetch(`/api/utilisateurs/${this.utilisateurToDelete.id}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    await this.loadUtilisateurs();
                    this.closeDeleteModal();
                    window.gsfToast('Utilisateur supprimé avec succès', 'success');
                } else {
                    window.gsfToast(data.message || 'Erreur lors de la suppression', 'error');
                }
            } catch (error) {
                console.error('Erreur:', error);
                window.gsfToast('Une erreur est survenue lors de la suppression', 'error');
            }
        },

        async toggleUtilisateurStatus(utilisateur) {
            try {
                const response = await fetch(`/api/utilisateurs/${utilisateur.id}/toggle-status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    await this.loadUtilisateurs();
                } else {
                    window.gsfToast(data.message || 'Erreur lors du changement de statut', 'error');
                }
            } catch (error) {
                console.error('Erreur:', error);
                window.gsfToast('Une erreur est survenue', 'error');
            }
        },

        exportCSV() {
            const headers = ['Nom', 'Login', 'Email', 'Rôle', 'Statut', 'Créé le'];
            const rows = this.filteredData.map(u => [
                u.nom,
                u.login,
                u.email,
                u.role_label,
                u.statut_label,
                u.date_creation
            ]);
            const csv = [headers.join(','), ...rows.map(r => r.map(c => `"${c}"`).join(','))].join('\n');
            const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `utilisateurs_${new Date().toISOString().split('T')[0]}.csv`;
            link.click();
            URL.revokeObjectURL(link.href);
        },

        exportExcel() { window.location.href = '/export/utilisateurs'; }
    }));
});
</script>

<style>
[x-cloak] { display: none !important; }
</style>


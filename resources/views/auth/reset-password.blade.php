<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Nouveau mot de passe – GSF</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex items-center justify-center bg-gray-100">

    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <img src="{{ asset('images/logo/gsf-logo.png') }}" alt="GSF Logo" class="h-20 mx-auto mb-4">
            <h1 class="text-2xl font-bold text-gray-800">Nouveau mot de passe</h1>
            <p class="text-sm text-gray-500 mt-1">Choisissez un nouveau mot de passe sécurisé</p>
        </div>

        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="h-1.5 bg-red-700"></div>

            <div class="p-8">

                @if ($errors->any())
                    <div class="mb-5 p-4 bg-red-50 border border-red-200 rounded-lg">
                        <ul class="text-sm text-red-700 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li class="flex items-center gap-2">
                                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    {{ $error }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.update') }}"
                    x-data="{ show1: false, show2: false, pw: '', pw2: '' }">
                    @csrf

                    <input type="hidden" name="token" value="{{ $token }}">
                    <input type="hidden" name="email" value="{{ $email }}">

                    <!-- Email affiché -->
                    <div class="mb-5 flex items-center gap-3 p-3 bg-gray-50 rounded-lg border border-gray-200">
                        <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <span class="text-sm text-gray-600">{{ $email }}</span>
                    </div>

                    <!-- Nouveau mot de passe -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Nouveau mot de passe <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input name="password" :type="show1 ? 'text' : 'password'"
                                x-model="pw" required minlength="8"
                                placeholder="Minimum 8 caractères"
                                class="w-full pl-4 pr-10 py-2.5 border border-gray-300 rounded-lg text-sm text-gray-700 focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none">
                            <button type="button" @click="show1 = !show1" class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600">
                                <svg x-show="!show1" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg x-show="show1" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>
                        <!-- Barre de force -->
                        <div class="mt-2 grid grid-cols-4 gap-1">
                            <div :class="pw.length >= 2 ? 'bg-red-400' : 'bg-gray-200'" class="h-1 rounded transition-colors"></div>
                            <div :class="pw.length >= 5 ? 'bg-orange-400' : 'bg-gray-200'" class="h-1 rounded transition-colors"></div>
                            <div :class="pw.length >= 8 && /[A-Z]/.test(pw) ? 'bg-yellow-400' : 'bg-gray-200'" class="h-1 rounded transition-colors"></div>
                            <div :class="pw.length >= 8 && /[A-Z]/.test(pw) && /[0-9]/.test(pw) ? 'bg-green-500' : 'bg-gray-200'" class="h-1 rounded transition-colors"></div>
                        </div>
                        <p class="mt-1 text-xs text-gray-400">Au moins 8 caractères, une majuscule et un chiffre</p>
                    </div>

                    <!-- Confirmation -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Confirmer le mot de passe <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input name="password_confirmation" :type="show2 ? 'text' : 'password'"
                                x-model="pw2" required
                                placeholder="Répétez le nouveau mot de passe"
                                :class="pw2.length > 0 ? (pw === pw2 ? 'border-green-500 focus:ring-green-500' : 'border-red-400 focus:ring-red-500') : 'border-gray-300 focus:ring-red-500'"
                                class="w-full pl-4 pr-10 py-2.5 border rounded-lg text-sm text-gray-700 outline-none transition">
                            <button type="button" @click="show2 = !show2" class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600">
                                <svg x-show="!show2" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg x-show="show2" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>
                        <div x-show="pw2.length > 0" class="mt-1">
                            <span x-show="pw === pw2" class="text-xs text-green-600 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Les mots de passe correspondent
                            </span>
                            <span x-show="pw !== pw2" class="text-xs text-red-600 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                Les mots de passe ne correspondent pas
                            </span>
                        </div>
                    </div>

                    <button type="submit"
                        :disabled="pw !== pw2 || pw.length < 8"
                        class="w-full py-2.5 px-4 bg-red-700 hover:bg-red-800 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold rounded-lg text-sm transition-colors duration-200 shadow-sm">
                        Enregistrer le nouveau mot de passe
                    </button>
                </form>
            </div>

            <div class="h-1.5 bg-red-700"></div>
        </div>

        <p class="text-center text-xs text-gray-400 mt-6">&copy; {{ date('Y') }} GSF – Tous droits réservés</p>
    </div>

    <script src="//unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</body>
</html>

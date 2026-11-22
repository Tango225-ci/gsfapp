<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#b91c1c">
    <link rel="manifest" href="{{ route('portail.manifest') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo/gsf-logo.png') }}">
    <title>Connexion – Espace Client GSF</title>
    @vite(['resources/css/app.css', 'resources/js/portail.js'])
    <style>
        body { background: #f3f4f6; font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center bg-gray-100">

    <div class="w-full max-w-md px-4">
        <!-- Logo & titre -->
        <div class="text-center mb-8">
            <img src="{{ asset('images/logo/gsf-logo.png') }}" alt="GSF Logo" class="h-20 mx-auto mb-4">
            <h1 class="text-2xl font-bold text-gray-800">Espace Client GSF</h1>
            <p class="text-sm text-gray-500 mt-1">Consultez vos lots, paiements et documents</p>
        </div>

        <!-- Carte de connexion -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="h-1.5 bg-red-700"></div>

            <div class="p-8">
                @if (session('success'))
                    <div class="mb-5 p-4 bg-green-50 border border-green-200 rounded-lg flex items-start gap-3">
                        <svg class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-sm text-green-700">{{ session('success') }}</p>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-5 p-4 bg-red-50 border border-red-200 rounded-lg flex items-start gap-3">
                        <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-sm text-red-700">{{ $errors->first() }}</p>
                    </div>
                @endif

                <form method="POST" action="{{ route('portail.login') }}">
                    @csrf

                    <!-- Identifiant -->
                    <div class="mb-5">
                        <label for="identifiant" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Numéro client ou email
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-2.5 text-gray-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </span>
                            <input id="identifiant" name="identifiant" type="text"
                                value="{{ old('identifiant') }}"
                                autocomplete="username"
                                required autofocus
                                placeholder="Ex: CLI00001 ou vous@email.com"
                                class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm text-gray-700 focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition @error('identifiant') border-red-500 @enderror">
                        </div>
                    </div>

                    <!-- Mot de passe -->
                    <div class="mb-6" x-data="{ show: false }">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Mot de passe
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-2.5 text-gray-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </span>
                            <input id="password" name="password" :type="show ? 'text' : 'password'"
                                autocomplete="current-password"
                                required
                                placeholder="Entrez votre mot de passe"
                                class="w-full pl-10 pr-10 py-2.5 border border-gray-300 rounded-lg text-sm text-gray-700 focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition">
                            <button type="button" @click="show = !show" class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600">
                                <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg x-show="show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Se souvenir de moi + Mot de passe oublié -->
                    <div class="flex items-center justify-between mb-6">
                        <label class="flex items-center gap-2">
                            <input id="remember" name="remember" type="checkbox"
                                class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                            <span class="text-sm text-gray-600">Se souvenir de moi</span>
                        </label>
                        <a href="{{ route('portail.otp.request') }}"
                            class="text-sm text-red-700 hover:text-red-800 hover:underline font-medium transition-colors">
                            Mot de passe oublié ?
                        </a>
                    </div>

                    <!-- Bouton connexion -->
                    <button type="submit"
                        class="w-full py-2.5 px-4 bg-red-700 hover:bg-red-800 text-white font-semibold rounded-lg text-sm transition-colors duration-200 shadow-sm">
                        Se connecter
                    </button>
                </form>
            </div>

            <div class="h-1.5 bg-red-700"></div>
        </div>

        <p class="text-center text-xs text-gray-400 mt-6">&copy; {{ date('Y') }} GSF – Tous droits réservés</p>
    </div>

</body>
</html>

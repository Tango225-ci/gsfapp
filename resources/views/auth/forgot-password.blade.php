<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Mot de passe oublié – GSF</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex items-center justify-center bg-gray-100">

    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <img src="{{ asset('images/logo/gsf-logo.png') }}" alt="GSF Logo" class="h-20 mx-auto mb-4">
            <h1 class="text-2xl font-bold text-gray-800">Mot de passe oublié ?</h1>
            <p class="text-sm text-gray-500 mt-1">Entrez votre email pour recevoir un lien de réinitialisation</p>
        </div>

        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="h-1.5 bg-red-700"></div>

            <div class="p-8">

                @if (session('success'))
                    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg flex items-start gap-3">
                        <svg class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-green-800">Email envoyé !</p>
                            <p class="text-sm text-green-700 mt-0.5">{{ session('success') }}</p>
                        </div>
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

                @unless(session('success'))
                <form method="POST" action="{{ route('password.email') }}">
                    @csrf

                    <div class="mb-6">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Adresse email du compte
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-2.5 text-gray-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </span>
                            <input id="email" name="email" type="email"
                                value="{{ old('email') }}"
                                required autofocus
                                placeholder="votre@email.com"
                                class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm text-gray-700 focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition @error('email') border-red-400 @enderror">
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full py-2.5 px-4 bg-red-700 hover:bg-red-800 text-white font-semibold rounded-lg text-sm transition-colors duration-200 shadow-sm">
                        Envoyer le lien de réinitialisation
                    </button>
                </form>
                @endunless

                <div class="mt-5 text-center">
                    <a href="{{ route('login') }}" class="text-sm text-gray-500 hover:text-red-700 flex items-center justify-center gap-1.5 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Retour à la connexion
                    </a>
                </div>
            </div>

            <div class="h-1.5 bg-red-700"></div>
        </div>

        <p class="text-center text-xs text-gray-400 mt-6">&copy; {{ date('Y') }} GSF – Tous droits réservés</p>
    </div>

</body>
</html>

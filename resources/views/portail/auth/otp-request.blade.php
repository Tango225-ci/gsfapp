<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#b91c1c">
    <link rel="manifest" href="{{ route('portail.manifest') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo/gsf-logo.png') }}">
    <title>Mot de passe oublié – Espace Client GSF</title>
    @vite(['resources/css/app.css', 'resources/js/portail.js'])
    <style>
        body { background: #f3f4f6; font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center bg-gray-100">

    <div class="w-full max-w-md px-4">
        <div class="text-center mb-8">
            <img src="{{ asset('images/logo/gsf-logo.png') }}" alt="GSF Logo" class="h-20 mx-auto mb-4">
            <h1 class="text-2xl font-bold text-gray-800">Mot de passe oublié</h1>
            <p class="text-sm text-gray-500 mt-1">Nous vous enverrons un code de vérification par SMS</p>
        </div>

        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="h-1.5 bg-red-700"></div>

            <div class="p-8">
                @if ($errors->any())
                    <div class="mb-5 p-4 bg-red-50 border border-red-200 rounded-lg flex items-start gap-3">
                        <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-sm text-red-700">{{ $errors->first() }}</p>
                    </div>
                @endif

                <form method="POST" action="{{ route('portail.otp.send') }}">
                    @csrf

                    @if ($client)
                        <div class="mb-6 flex items-center gap-3 p-3 bg-gray-50 rounded-lg border border-gray-200">
                            <div class="w-9 h-9 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                                <span class="text-sm font-bold text-red-700">{{ substr($client->nom_prenoms, 0, 1) }}</span>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-800">{{ $client->nom_prenoms }}</p>
                                <p class="text-xs text-gray-500">Un code sera envoyé au {{ $client->contact }}</p>
                            </div>
                        </div>
                    @else
                        <div class="mb-5">
                            <label for="identifiant" class="block text-sm font-medium text-gray-700 mb-1.5">
                                Téléphone ou email
                            </label>
                            <input id="identifiant" name="identifiant" type="text"
                                value="{{ old('identifiant') }}"
                                required autofocus
                                placeholder="Ex: 0700000000 ou vous@email.com"
                                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm text-gray-700 focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition @error('identifiant') border-red-500 @enderror">
                        </div>
                    @endif

                    <button type="submit"
                        class="w-full py-2.5 px-4 bg-red-700 hover:bg-red-800 text-white font-semibold rounded-lg text-sm transition-colors duration-200 shadow-sm">
                        Envoyer le code
                    </button>

                    <div class="mt-4 text-center">
                        <a href="{{ $client ? route('portail.profile') : route('portail.login') }}" class="text-xs text-gray-400 hover:text-gray-600 underline">
                            Annuler
                        </a>
                    </div>
                </form>
            </div>

            <div class="h-1.5 bg-red-700"></div>
        </div>

        <p class="text-center text-xs text-gray-400 mt-6">&copy; {{ date('Y') }} GSF – Tous droits réservés</p>
    </div>

</body>
</html>

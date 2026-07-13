@extends('layouts.portail')

@section('title', 'Mon profil')

@section('content')
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-5 mb-4">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-14 h-14 rounded-2xl flex items-center justify-center flex-shrink-0 text-white font-bold text-xl" style="background: linear-gradient(135deg, #7f1d1d, #dc2626);">
                {{ substr($client->nom_prenoms, 0, 1) }}
            </div>
            <div>
                <p class="font-bold text-gray-800">{{ $client->nom_prenoms }}</p>
                <p class="text-xs text-gray-400">{{ $client->reference_display }}</p>
            </div>
        </div>

        <div class="space-y-1 border-t border-gray-100 pt-4">
            <div class="flex justify-between items-center py-2.5">
                <span class="text-xs text-gray-400 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    Téléphone
                </span>
                <span class="text-sm font-medium text-gray-700">{{ $client->contact }}</span>
            </div>
            <div class="flex justify-between items-center py-2.5 border-t border-gray-50">
                <span class="text-xs text-gray-400 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    Email
                </span>
                <span class="text-sm font-medium text-gray-700">{{ $client->email ?? '—' }}</span>
            </div>
            <div class="flex justify-between items-center py-2.5 border-t border-gray-50">
                <span class="text-xs text-gray-400 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Adresse
                </span>
                <span class="text-sm font-medium text-gray-700 text-right">{{ $client->adresse ?? '—' }}</span>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('portail.otp.send') }}" class="mb-4">
        @csrf
        <button type="submit"
            class="w-full flex items-center justify-center gap-2 py-3 text-sm font-semibold text-white rounded-2xl shadow-sm hover:shadow-md transition-shadow"
            style="background: linear-gradient(135deg, #7f1d1d, #dc2626);">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            Changer mon mot de passe
        </button>
        <p class="text-[10px] text-gray-400 text-center mt-1.5">Un code de vérification vous sera envoyé par SMS au {{ $client->contact }}</p>
    </form>

    <div class="bg-blue-50 border border-blue-100 rounded-2xl p-4 flex items-start gap-3">
        <svg class="w-5 h-5 text-blue-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <p class="text-xs text-blue-700">Pour modifier vos informations, veuillez contacter GSF directement.</p>
    </div>
@endsection

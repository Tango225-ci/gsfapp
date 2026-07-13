@extends('layouts.portail')

@section('title', 'Accueil')

@section('content')
    @php
        $totalDu = $contrats->sum('montant_total');
        $totalPaye = $contrats->sum('montant_paye');
        $totalReste = $contrats->sum('reste_a_payer_calcule');
        $progressionGlobale = $totalDu > 0 ? min(100, round($totalPaye / $totalDu * 100)) : 0;
        $globalSolde = $progressionGlobale >= 100;
    @endphp

    <div class="mb-4 px-1">
        <p class="text-xs text-gray-400">Bonjour</p>
        <p class="text-lg font-bold text-gray-800">{{ explode(' ', auth('clients')->user()->nom_prenoms)[0] }} 👋</p>
    </div>

    {{-- Carte solde --}}
    <div class="relative overflow-hidden rounded-3xl p-5 mb-5 bg-white border border-gray-100 shadow-lg">
        <div class="absolute -right-10 -top-10 w-36 h-36 rounded-full" style="background: {{ $globalSolde ? 'rgba(22,163,74,0.05)' : 'rgba(220,38,38,0.05)' }};"></div>

        @if ($totalDu > 0)
            <div class="relative flex items-center gap-4 mb-5">
                <div class="relative w-[72px] h-[72px] shrink-0">
                    <div class="absolute inset-0 rounded-full" style="background: conic-gradient({{ $globalSolde ? '#16a34a' : '#dc2626' }} {{ $progressionGlobale }}%, #f3f4f6 0);"></div>
                    <div class="absolute inset-[5px] rounded-full bg-white flex items-center justify-center">
                        <span class="text-base font-bold text-gray-800">{{ $progressionGlobale }}%</span>
                    </div>
                </div>
                <div>
                    <p class="text-[10px] text-gray-400 uppercase tracking-wide mb-0.5">Progression globale</p>
                    <p class="text-sm font-semibold {{ $globalSolde ? 'text-green-600' : 'text-red-600' }}">
                        {{ $globalSolde ? 'Tous vos contrats sont soldés' : 'Paiements en cours' }}
                    </p>
                </div>
            </div>
        @else
            <p class="relative text-sm text-gray-400 mb-5">Aucun montant enregistré pour le moment.</p>
        @endif

        <div class="relative grid grid-cols-3 gap-2 pt-4 border-t border-gray-100">
            <div>
                <p class="text-[10px] text-gray-400 uppercase tracking-wide mb-0.5">Total dû</p>
                <p class="text-sm font-bold text-gray-800">{{ number_format($totalDu, 0, ',', ' ') }}</p>
            </div>
            <div>
                <p class="text-[10px] text-gray-400 uppercase tracking-wide mb-0.5">Payé</p>
                <p class="text-sm font-bold text-green-600">{{ number_format($totalPaye, 0, ',', ' ') }}</p>
            </div>
            <div>
                <p class="text-[10px] text-gray-400 uppercase tracking-wide mb-0.5">Reste</p>
                <p class="text-sm font-bold {{ $totalReste > 0 ? 'text-red-600' : 'text-green-600' }}">{{ number_format($totalReste, 0, ',', ' ') }}</p>
            </div>
        </div>
    </div>

    <h2 class="text-sm font-bold text-gray-700 mb-3 px-1">Mes contrats</h2>

    @if ($contrats->isEmpty())
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-8 text-center text-gray-400">
            <svg class="w-10 h-10 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-sm">Aucun contrat associé à votre compte pour le moment.</p>
        </div>
    @else
        <div class="space-y-2.5">
            @foreach ($contrats as $contrat)
                @php $accent = $contrat->statut === 'SOLDE' ? '#16a34a' : '#dc2626'; @endphp
                <a href="{{ route('portail.contrats.show', $contrat->id_contrat) }}"
                    class="flex items-center gap-3 bg-white rounded-2xl border border-gray-100 shadow-sm p-3.5 hover:shadow-md hover:border-gray-200 transition-all">
                    <div class="h-11 w-11 rounded-xl flex items-center justify-center shrink-0" style="background: {{ $contrat->statut === 'SOLDE' ? '#f0fdf4' : '#fef2f2' }};">
                        <svg class="w-5 h-5" style="color: {{ $accent }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2 mb-1">
                            <p class="font-semibold text-sm text-gray-800 truncate">{{ $contrat->lot->numero_lot ?? '—' }}</p>
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full shrink-0
                                {{ $contrat->statut === 'SOLDE' ? 'bg-green-100 text-green-700' : ($contrat->statut === 'EN_RETARD' ? 'bg-red-100 text-red-600' : 'bg-blue-50 text-blue-600') }}">
                                {{ $contrat->statut_label }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-400 truncate mb-2">{{ $contrat->lot->lotissement_nom ?? '—' }}</p>
                        <div class="flex items-center gap-2">
                            <div class="flex-1 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full rounded-full" style="width: {{ $contrat->progression_paiement }}%; background: {{ $accent }};"></div>
                            </div>
                            <span class="text-[10px] font-medium text-gray-400 shrink-0">{{ $contrat->progression_paiement }}%</span>
                        </div>
                    </div>
                    <svg class="w-4 h-4 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            @endforeach
        </div>
    @endif
@endsection

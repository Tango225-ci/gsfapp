@extends('layouts.portail')

@section('title', 'Mes contrats')

@section('content')
    <div class="mb-4 px-1 flex items-center justify-between">
        <div>
            <p class="text-xs text-gray-400">Mes contrats</p>
            <p class="text-lg font-bold text-gray-800">{{ $contrats->count() }} contrat{{ $contrats->count() > 1 ? 's' : '' }}</p>
        </div>
        <div class="h-10 w-10 rounded-xl bg-red-50 flex items-center justify-center">
            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        </div>
    </div>

    @if ($contrats->isEmpty())
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-8 text-center text-gray-400">
            <p class="text-sm">Aucun contrat associé à votre compte pour le moment.</p>
        </div>
    @else
        <div class="space-y-2.5">
            @foreach ($contrats as $contrat)
                @php $accent = $contrat->statut === 'SOLDE' ? '#16a34a' : '#dc2626'; @endphp
                <a href="{{ route('portail.contrats.show', $contrat->id_contrat) }}"
                    class="flex items-center gap-3 bg-white rounded-2xl border border-gray-100 shadow-sm p-3.5 hover:shadow-md hover:border-gray-200 transition-all">
                    <div class="h-11 w-11 rounded-xl flex items-center justify-center shrink-0" style="background: {{ $contrat->statut === 'SOLDE' ? '#f0fdf4' : '#fef2f2' }};">
                        <svg class="w-5 h-5" style="color: {{ $accent }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2 mb-1">
                            <p class="font-semibold text-sm text-gray-800 truncate">{{ $contrat->reference_contrat }}</p>
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full shrink-0
                                {{ $contrat->statut === 'SOLDE' ? 'bg-green-100 text-green-700' : ($contrat->statut === 'EN_RETARD' ? 'bg-red-100 text-red-600' : 'bg-blue-50 text-blue-600') }}">
                                {{ $contrat->statut_label }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-400 truncate mb-2">{{ $contrat->lot->numero_lot ?? '—' }} · {{ $contrat->lot->lotissement_nom ?? '—' }}</p>
                        <div class="flex justify-between text-xs">
                            <span class="text-gray-400">Reste à payer</span>
                            <span class="font-semibold" style="color: {{ $contrat->reste_a_payer_calcule > 0 ? '#dc2626' : '#16a34a' }}">{{ $contrat->reste_a_payer_formatted }}</span>
                        </div>
                    </div>
                    <svg class="w-4 h-4 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            @endforeach
        </div>
    @endif
@endsection

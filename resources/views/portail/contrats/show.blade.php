@extends('layouts.portail')

@section('title', $contrat->reference_contrat)

@section('content')
    <a href="{{ route('portail.contrats.index') }}" class="inline-flex items-center gap-1 text-xs text-gray-500 hover:text-red-700 mb-4">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Mes contrats
    </a>

    @php $contratSolde = $contrat->progression_paiement >= 100; @endphp

    {{-- En-tête du contrat --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-3 flex items-start justify-between">
        <div>
            <p class="font-bold text-gray-800">{{ $contrat->reference_contrat }}</p>
            <p class="text-xs text-gray-400 mt-0.5">{{ $contrat->lot->numero_lot ?? '—' }} · {{ $contrat->lot->lotissement_nom ?? '—' }}</p>
            @if ($contrat->lot->ville_nom ?? null)
                <p class="text-xs text-gray-400">{{ $contrat->lot->ville_nom }}</p>
            @endif
        </div>
        <span class="text-[10px] font-semibold px-2.5 py-1 rounded-full shrink-0
            {{ $contrat->statut === 'SOLDE' ? 'bg-green-100 text-green-700' : ($contrat->statut === 'EN_RETARD' ? 'bg-red-100 text-red-600' : 'bg-blue-50 text-blue-600') }}">
            {{ $contrat->statut_label }}
        </span>
    </div>

    {{-- Carte solde --}}
    <div class="relative overflow-hidden rounded-3xl p-5 mb-4 bg-white border border-gray-100 shadow-lg">
        <div class="absolute -right-10 -top-10 w-36 h-36 rounded-full" style="background: {{ $contratSolde ? 'rgba(22,163,74,0.05)' : 'rgba(220,38,38,0.05)' }};"></div>

        <div class="relative flex items-center gap-4 mb-5">
            <div class="relative w-[72px] h-[72px] shrink-0">
                <div class="absolute inset-0 rounded-full" style="background: conic-gradient({{ $contratSolde ? '#16a34a' : '#dc2626' }} {{ $contrat->progression_paiement }}%, #f3f4f6 0);"></div>
                <div class="absolute inset-[5px] rounded-full bg-white flex items-center justify-center">
                    <span class="text-base font-bold text-gray-800">{{ $contrat->progression_paiement }}%</span>
                </div>
            </div>
            <div>
                <p class="text-[10px] text-gray-400 uppercase tracking-wide mb-0.5">{{ $contrat->type_paiement_label }}</p>
                <p class="text-sm font-semibold {{ $contratSolde ? 'text-green-600' : 'text-red-600' }}">
                    {{ $contratSolde ? 'Contrat soldé' : 'Paiement en cours' }}
                </p>
            </div>
        </div>

        <div class="relative grid grid-cols-3 gap-2 pt-4 border-t border-gray-100">
            <div>
                <p class="text-[10px] text-gray-400 uppercase tracking-wide mb-0.5">Total</p>
                <p class="text-sm font-bold text-gray-800">{{ $contrat->montant_total_formatted }}</p>
            </div>
            <div>
                <p class="text-[10px] text-gray-400 uppercase tracking-wide mb-0.5">Payé</p>
                <p class="text-sm font-bold text-green-600">{{ $contrat->montant_paye_formatted }}</p>
            </div>
            <div>
                <p class="text-[10px] text-gray-400 uppercase tracking-wide mb-0.5">Reste</p>
                <p class="text-sm font-bold {{ $contratSolde ? 'text-green-600' : 'text-red-600' }}">{{ $contrat->reste_a_payer_formatted }}</p>
            </div>
        </div>
    </div>

    <a href="{{ route('portail.contrats.etat', $contrat->id_contrat) }}" target="_blank"
        class="mb-4 w-full flex items-center justify-center gap-2 py-2.5 text-xs font-semibold text-white rounded-xl shadow-sm hover:shadow-md transition-shadow"
        style="background: linear-gradient(135deg, #7f1d1d, #dc2626);">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H8a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        Télécharger l'état de contrat
    </a>

    {{-- Paiements --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-4 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 flex items-center gap-2">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 002 2v6a2 2 0 002 2z"/></svg>
            <p class="text-sm font-semibold text-gray-800">Historique des paiements</p>
        </div>
        @php $paiementsValides = $contrat->paiements->where('statut', 'VALIDE')->sortByDesc('date_paiement'); @endphp
        @if ($paiementsValides->isEmpty())
            <p class="px-4 py-6 text-center text-xs text-gray-400">Aucun paiement enregistré.</p>
        @else
            <div class="divide-y divide-gray-100">
                @foreach ($paiementsValides as $paiement)
                    <div class="px-4 py-3 flex items-center justify-between text-xs">
                        <div>
                            <p class="font-medium text-gray-700">{{ $paiement->date_paiement_formatted }}</p>
                            <p class="text-gray-400">{{ $paiement->mode_paiement_label }}</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <p class="font-semibold text-green-600">{{ $paiement->montant_formatted }}</p>
                            <a href="{{ route('portail.paiements.recu', $paiement->id_paiement) }}" target="_blank" title="Télécharger le reçu"
                                class="flex items-center justify-center h-7 w-7 rounded-lg bg-gray-50 text-gray-500 hover:bg-red-50 hover:text-red-600 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H8a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Documents --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 flex items-center gap-2">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <p class="text-sm font-semibold text-gray-800">Documents</p>
        </div>
        @if ($contrat->contratDocuments->isEmpty())
            <p class="px-4 py-6 text-center text-xs text-gray-400">Aucun document pour ce contrat.</p>
        @else
            <div class="divide-y divide-gray-100">
                @foreach ($contrat->contratDocuments as $document)
                    <div class="px-4 py-3 flex items-center justify-between text-xs">
                        <div>
                            <p class="font-medium text-gray-700">{{ $document->document_type_nom ?? 'Document' }}</p>
                            <p class="text-gray-400">{{ $document->statut_document_label }}</p>
                        </div>
                        @if ($document->fichier_path)
                            <a href="{{ route('portail.documents.download', $document->id_contrat_document) }}"
                                class="flex items-center justify-center h-7 w-7 rounded-lg bg-gray-50 text-gray-500 hover:bg-red-50 hover:text-red-600 transition-colors" title="Télécharger">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H8a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </a>
                        @else
                            <span class="text-gray-300">—</span>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection

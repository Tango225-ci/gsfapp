@extends('layouts.app')

@section('content')

@php
    $fmt = fn($v) => number_format($v, 0, ',', ' ');
    $moisFr = ['','Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
@endphp

{{-- ===== En-tête ===== --}}
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-xl font-bold text-gray-900 dark:text-white">Tableau de bord</h1>
        <p class="text-xs text-gray-400 mt-0.5">{{ $moisFr[(int)date('n')] }} {{ date('Y') }} — Géomètre Son Femme SARL</p>
    </div>
    <a href="/contrats" class="inline-flex items-center gap-1 rounded-lg bg-red-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-700 transition-colors">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
        Nouveau contrat
    </a>
</div>

{{-- ===== Ligne 1 : 5 KPI ultra-compacts ===== --}}
<div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-5 gap-3 mb-4">

    {{-- Encaissé aujourd'hui --}}
    <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 dark:border-gray-800 dark:bg-white/[0.03]">
        <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Aujourd'hui</p>
        <p class="mt-1 text-xl font-bold text-gray-900 dark:text-white leading-tight">{{ $fmt($encaisseAujourdhui) }}</p>
        <p class="text-[10px] text-gray-400 mt-0.5">FCFA encaissés</p>
    </div>

    {{-- Encaissé ce mois --}}
    <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 dark:border-gray-800 dark:bg-white/[0.03]">
        <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Ce mois</p>
        <p class="mt-1 text-xl font-bold text-gray-900 dark:text-white leading-tight">{{ $fmt($encaisseMois) }}</p>
        <p class="text-[10px] text-gray-400 mt-0.5">FCFA encaissés</p>
    </div>

    {{-- Ventes du mois --}}
    <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 dark:border-gray-800 dark:bg-white/[0.03]">
        <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Ventes du mois</p>
        <p class="mt-1 text-xl font-bold text-gray-900 dark:text-white leading-tight">{{ $nbNouveauxContratsMois }}</p>
        <p class="text-[10px] text-gray-400 mt-0.5">{{ $fmt($montantVentesMois) }} FCFA</p>
    </div>

    {{-- Lots vendus / total --}}
    <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 dark:border-gray-800 dark:bg-white/[0.03]">
        <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Lots vendus</p>
        <p class="mt-1 text-xl font-bold text-gray-900 dark:text-white leading-tight">{{ $lotsVendus }} <span class="text-sm font-normal text-gray-400">/ {{ $totalLots }}</span></p>
        @php $pctVendus = $totalLots > 0 ? round($lotsVendus / $totalLots * 100) : 0; @endphp
        <div class="mt-1.5 h-1.5 bg-gray-100 rounded-full dark:bg-gray-800">
            <div class="h-1.5 bg-red-500 rounded-full" style="width:{{ $pctVendus }}%"></div>
        </div>
        <p class="text-[10px] text-gray-400 mt-0.5">{{ $pctVendus }}% vendus · {{ $lotsDisponibles }} disponibles</p>
    </div>

    {{-- Échéances en retard --}}
    <div class="rounded-xl border px-4 py-3 {{ $nbEcheancesEnRetard > 0 ? 'border-red-200 bg-red-50 dark:border-red-900/50 dark:bg-red-500/5' : 'border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]' }}">
        <p class="text-[11px] font-medium {{ $nbEcheancesEnRetard > 0 ? 'text-red-400' : 'text-gray-400' }} uppercase tracking-wide">Retards</p>
        <p class="mt-1 text-xl font-bold {{ $nbEcheancesEnRetard > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white' }} leading-tight">{{ $nbEcheancesEnRetard }}</p>
        <a href="/echeances" class="text-[10px] {{ $nbEcheancesEnRetard > 0 ? 'text-red-500 hover:text-red-700' : 'text-gray-400' }} mt-0.5 block">échéances en retard →</a>
    </div>
</div>

{{-- ===== Ligne 2 : Paiements journaliers (large) + Mode de paiement (petit) ===== --}}
<div class="grid grid-cols-1 xl:grid-cols-3 gap-3 mb-4">

    {{-- Paiements journaliers 14j --}}
    <div class="xl:col-span-2 rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-center justify-between mb-3">
            <div>
                <p class="text-sm font-semibold text-gray-800 dark:text-white">Encaissements journaliers</p>
                <p class="text-[11px] text-gray-400">14 derniers jours</p>
            </div>
            <span class="text-xs text-gray-400">{{ $fmt(collect($paiementsJournaliers)->sum('montant')) }} FCFA</span>
        </div>
        <div id="chartJournaliers" style="min-height:180px;"></div>
    </div>

    {{-- Mode de paiement --}}
    <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
        <p class="text-sm font-semibold text-gray-800 dark:text-white mb-1">Mode de paiement</p>
        <p class="text-[11px] text-gray-400 mb-3">Encaissements ce mois</p>
        @php $totalMode = collect($paiementsParMode)->sum('montant'); @endphp
        @if($totalMode > 0)
            <div id="chartModes" style="min-height:150px;"></div>
            <div class="mt-2 space-y-1.5">
                @php
                    $modeColors = ['ESPECES'=>'bg-emerald-500','VIREMENT'=>'bg-blue-500','CHEQUE'=>'bg-violet-500','MOBILE_MONEY'=>'bg-amber-500'];
                @endphp
                @foreach($paiementsParMode as $m)
                    @if($m['montant'] > 0)
                        <div class="flex items-center justify-between text-xs">
                            <div class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full {{ $modeColors[$m['code']] ?? 'bg-gray-400' }} inline-block flex-shrink-0"></span>
                                <span class="text-gray-600 dark:text-gray-300">{{ $m['label'] }}</span>
                            </div>
                            <span class="font-semibold text-gray-800 dark:text-white">{{ $fmt($m['montant']) }}</span>
                        </div>
                    @endif
                @endforeach
            </div>
        @else
            <div class="flex flex-col items-center justify-center py-8 text-gray-300 dark:text-gray-700">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="mb-2"><path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                <p class="text-xs">Aucun paiement ce mois</p>
            </div>
        @endif
    </div>
</div>

{{-- ===== Ligne 3 : Lots par lotissement + Encaissements par semaine ===== --}}
<div class="grid grid-cols-1 xl:grid-cols-3 gap-3 mb-4">

    {{-- Lots par lotissement --}}
    <div class="xl:col-span-2 rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100 dark:border-gray-800">
            <p class="text-sm font-semibold text-gray-800 dark:text-white">Lots par lotissement</p>
            <a href="/lots" class="text-[11px] text-gray-400 hover:text-red-600">Gérer →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-4 py-2 text-left font-medium text-gray-400 uppercase tracking-wide">Lotissement</th>
                        <th class="px-3 py-2 text-center font-medium text-gray-400 uppercase tracking-wide">Total</th>
                        <th class="px-3 py-2 text-center font-medium text-emerald-600 uppercase tracking-wide">Dispo.</th>
                        <th class="px-3 py-2 text-center font-medium text-amber-500 uppercase tracking-wide">Réservés</th>
                        <th class="px-3 py-2 text-center font-medium text-red-500 uppercase tracking-wide">Vendus</th>
                        <th class="px-3 py-2 text-left font-medium text-gray-400 uppercase tracking-wide w-28">Progression</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($lotsParLotissement as $loti)
                        @php $pct = $loti['total'] > 0 ? round($loti['vendus'] / $loti['total'] * 100) : 0; @endphp
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                            <td class="px-4 py-2.5">
                                <div class="font-medium text-gray-800 dark:text-white">{{ $loti['nom'] }}</div>
                                @if($loti['ville']) <div class="text-[10px] text-gray-400">{{ $loti['ville'] }}</div> @endif
                            </td>
                            <td class="px-3 py-2.5 text-center font-semibold text-gray-700 dark:text-gray-300">{{ $loti['total'] }}</td>
                            <td class="px-3 py-2.5 text-center font-semibold text-emerald-600">{{ $loti['disponibles'] }}</td>
                            <td class="px-3 py-2.5 text-center font-semibold text-amber-500">{{ $loti['reserves'] }}</td>
                            <td class="px-3 py-2.5 text-center font-semibold text-red-500">{{ $loti['vendus'] }}</td>
                            <td class="px-3 py-2.5">
                                <div class="flex items-center gap-1.5">
                                    <div class="flex-1 h-1.5 bg-gray-100 rounded-full dark:bg-gray-800">
                                        <div class="h-1.5 rounded-full bg-red-500" style="width:{{ $pct }}%"></div>
                                    </div>
                                    <span class="text-[10px] text-gray-400 w-7 text-right">{{ $pct }}%</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Aucun lotissement</td></tr>
                    @endforelse
                    {{-- Ligne total --}}
                    @if($lotsParLotissement->count() > 1)
                        @php $pctTotal = $totalLots > 0 ? round($lotsVendus / $totalLots * 100) : 0; @endphp
                        <tr class="bg-gray-50 dark:bg-gray-900/50 font-semibold">
                            <td class="px-4 py-2 text-gray-700 dark:text-gray-300">Total</td>
                            <td class="px-3 py-2 text-center text-gray-800 dark:text-white">{{ $totalLots }}</td>
                            <td class="px-3 py-2 text-center text-emerald-600">{{ $lotsDisponibles }}</td>
                            <td class="px-3 py-2 text-center text-amber-500">{{ $lotsReserves }}</td>
                            <td class="px-3 py-2 text-center text-red-500">{{ $lotsVendus }}</td>
                            <td class="px-3 py-2">
                                <div class="flex items-center gap-1.5">
                                    <div class="flex-1 h-1.5 bg-gray-200 rounded-full dark:bg-gray-700">
                                        <div class="h-1.5 rounded-full bg-red-600" style="width:{{ $pctTotal }}%"></div>
                                    </div>
                                    <span class="text-[10px] text-gray-500 w-7 text-right">{{ $pctTotal }}%</span>
                                </div>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    {{-- Encaissements par semaine --}}
    <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
        <p class="text-sm font-semibold text-gray-800 dark:text-white mb-1">Par semaine</p>
        <p class="text-[11px] text-gray-400 mb-3">Encaissements 8 dernières semaines</p>
        <div id="chartSemaines" style="min-height:200px;"></div>
    </div>
</div>

{{-- ===== Ligne 4 : Paiements du jour + Nouveaux contrats ===== --}}
<div class="grid grid-cols-1 xl:grid-cols-2 gap-3">

    {{-- Paiements du jour --}}
    <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100 dark:border-gray-800">
            <div class="flex items-center gap-2">
                <span class="flex h-2 w-2 rounded-full bg-emerald-500{{ $paiementsAujourdhui->count() > 0 ? ' animate-pulse' : '' }}"></span>
                <p class="text-sm font-semibold text-gray-800 dark:text-white">Paiements du jour</p>
                @if($paiementsAujourdhui->count() > 0)
                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400">{{ $paiementsAujourdhui->count() }}</span>
                @endif
            </div>
            <span class="text-xs font-semibold text-emerald-600">{{ $fmt($encaisseAujourdhui) }} FCFA</span>
        </div>
        @if($paiementsAujourdhui->isEmpty())
            <div class="flex flex-col items-center justify-center py-10 text-gray-300 dark:text-gray-700">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="mb-2"><path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                <p class="text-xs">Aucun paiement enregistré aujourd'hui</p>
            </div>
        @else
            <div class="overflow-x-auto"><table class="w-full text-xs">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-4 py-2 text-left font-medium text-gray-400 uppercase tracking-wide">Client</th>
                        <th class="px-3 py-2 text-left font-medium text-gray-400 uppercase tracking-wide">Mode</th>
                        <th class="px-3 py-2 text-right font-medium text-gray-400 uppercase tracking-wide">Montant</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($paiementsAujourdhui as $p)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                            <td class="px-4 py-2.5">
                                <div class="font-medium text-gray-800 dark:text-white">{{ optional(optional($p->contrat)->client)->nom_prenoms ?? '—' }}</div>
                                <div class="text-[10px] text-gray-400 font-mono">{{ $p->reference }}</div>
                            </td>
                            <td class="px-3 py-2.5 text-gray-500 dark:text-gray-400">{{ $p->mode_paiement_label }}</td>
                            <td class="px-3 py-2.5 text-right font-semibold text-gray-800 dark:text-white whitespace-nowrap">{{ $fmt($p->montant) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table></div>
        @endif
    </div>

    {{-- Nouveaux contrats du mois --}}
    <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100 dark:border-gray-800">
            <div class="flex items-center gap-2">
                <p class="text-sm font-semibold text-gray-800 dark:text-white">Ventes du mois</p>
                @if($nbNouveauxContratsMois > 0)
                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-violet-100 text-violet-700 dark:bg-violet-500/15 dark:text-violet-400">{{ $nbNouveauxContratsMois }}</span>
                @endif
            </div>
            <a href="/contrats" class="text-[11px] text-gray-400 hover:text-red-600">Tous →</a>
        </div>
        @if($nouveauxContratsMois->isEmpty())
            <div class="flex flex-col items-center justify-center py-10 text-gray-300 dark:text-gray-700">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="mb-2"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <p class="text-xs">Aucun contrat signé ce mois</p>
            </div>
        @else
            <div class="overflow-x-auto"><table class="w-full text-xs">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-4 py-2 text-left font-medium text-gray-400 uppercase tracking-wide">Client</th>
                        <th class="px-3 py-2 text-left font-medium text-gray-400 uppercase tracking-wide">Réf.</th>
                        <th class="px-3 py-2 text-right font-medium text-gray-400 uppercase tracking-wide">Montant</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($nouveauxContratsMois as $c)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                            <td class="px-4 py-2.5">
                                <div class="font-medium text-gray-800 dark:text-white">{{ optional($c->client)->nom_prenoms ?? '—' }}</div>
                                <div class="text-[10px] text-gray-400">
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-semibold
                                        {{ $c->statut === 'EN_RETARD' ? 'bg-red-100 text-red-600' : ($c->statut === 'SOLDE' ? 'bg-emerald-100 text-emerald-700' : 'bg-blue-50 text-blue-600') }}">
                                        {{ $c->statut_label }}
                                    </span>
                                    · {{ $c->type_paiement_label }}
                                </div>
                            </td>
                            <td class="px-3 py-2.5 font-mono text-gray-500 dark:text-gray-400">{{ $c->reference_contrat }}</td>
                            <td class="px-3 py-2.5 text-right font-semibold text-gray-800 dark:text-white whitespace-nowrap">{{ $fmt($c->montant_total) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table></div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.44.2/dist/apexcharts.min.js"></script>
<script>
(function () {
    const isDark = document.documentElement.classList.contains('dark');
    const textColor = isDark ? '#6b7280' : '#9ca3af';
    const gridColor = isDark ? '#1f2937' : '#f9fafb';

    // ── Paiements journaliers ──────────────────────────────────────────────
    const journaliers = @json($paiementsJournaliers);
    new ApexCharts(document.getElementById('chartJournaliers'), {
        chart: { type: 'area', height: 180, toolbar: { show: false }, fontFamily: 'inherit', sparkline: { enabled: false } },
        series: [{ name: 'FCFA', data: journaliers.map(d => d.montant) }],
        xaxis: {
            categories: journaliers.map(d => d.label),
            labels: { style: { colors: textColor, fontSize: '10px' }, rotate: -30, rotateAlways: false },
            axisBorder: { show: false }, axisTicks: { show: false },
        },
        yaxis: {
            labels: {
                style: { colors: textColor, fontSize: '10px' },
                formatter: v => v >= 1e6 ? (v/1e6).toFixed(1)+'M' : v >= 1e3 ? Math.round(v/1e3)+'k' : v
            }
        },
        colors: ['#dc2626'],
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.25, opacityTo: 0.02 } },
        stroke: { width: 2, curve: 'smooth' },
        grid: { borderColor: gridColor, strokeDashArray: 3, padding: { left: 0, right: 0 } },
        dataLabels: { enabled: false },
        tooltip: {
            theme: isDark ? 'dark' : 'light',
            y: { formatter: v => new Intl.NumberFormat('fr-FR').format(v) + ' FCFA' }
        },
    }).render();

    // ── Mode de paiement ──────────────────────────────────────────────────
    @php $totalMode = collect($paiementsParMode)->sum('montant'); @endphp
    @if($totalMode > 0)
    const modesData = @json(collect($paiementsParMode)->filter(fn($m) => $m['montant'] > 0)->values());
    if (modesData.length > 0) {
        new ApexCharts(document.getElementById('chartModes'), {
            chart: { type: 'donut', height: 150, toolbar: { show: false }, fontFamily: 'inherit' },
            series: modesData.map(d => d.montant),
            labels: modesData.map(d => d.label),
            colors: ['#10b981', '#3b82f6', '#8b5cf6', '#f59e0b'],
            legend: { show: false },
            dataLabels: { enabled: false },
            plotOptions: { pie: { donut: { size: '70%' } } },
            tooltip: {
                theme: isDark ? 'dark' : 'light',
                y: { formatter: v => new Intl.NumberFormat('fr-FR').format(v) + ' FCFA' }
            },
        }).render();
    }
    @endif

    // ── Encaissements par semaine (barres horizontales) ──────────────────
    const semaines = @json($paiementsParSemaine);
    new ApexCharts(document.getElementById('chartSemaines'), {
        chart: { type: 'bar', height: 220, toolbar: { show: false }, fontFamily: 'inherit' },
        series: [{ name: 'FCFA', data: semaines.map(d => d.montant) }],
        plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '55%' } },
        xaxis: {
            labels: {
                style: { colors: textColor, fontSize: '9px' },
                formatter: v => v >= 1e6 ? (v/1e6).toFixed(1)+'M' : v >= 1e3 ? Math.round(v/1e3)+'k' : String(v)
            },
            axisBorder: { show: false }, axisTicks: { show: false },
        },
        yaxis: {
            labels: { style: { colors: textColor, fontSize: '10px' }, maxWidth: 80 },
            categories: semaines.map(d => d.label),
        },
        colors: ['#7c3aed'],
        fill: { opacity: 0.85 },
        grid: { borderColor: gridColor, strokeDashArray: 3, padding: { left: 0, right: 10 } },
        dataLabels: { enabled: false },
        tooltip: {
            theme: isDark ? 'dark' : 'light',
            y: { formatter: v => new Intl.NumberFormat('fr-FR').format(v) + ' FCFA' }
        },
    }).render();
})();
</script>
@endpush

{{-- Notification Dropdown – données réelles avec suppression Alpine.js --}}
@php
use App\Models\Echeance;
use App\Models\Paiement;
use App\Models\Contrat;
use App\Models\Visite;
use Carbon\Carbon;

Carbon::setLocale('fr');
$notifications = collect();
$today = Carbon::today();

// 1. Échéances en retard
$echeancesRetard = Echeance::with('contrat.client')
    ->where('statut', 'EN_RETARD')
    ->orderBy('date_echeance')
    ->limit(5)
    ->get();

foreach ($echeancesRetard as $e) {
    $joursRetard = $today->diffInDays(Carbon::parse($e->date_echeance));
    $notifications->push([
        'id'     => 'ech_' . $e->id,
        'icon'   => 'echeance',
        'color'  => 'red',
        'title'  => 'Échéance en retard',
        'body'   => ($e->contrat?->client?->nom_prenoms ?? 'Client') . ' — ' . number_format($e->montant_prevu ?? 0, 0, ',', ' ') . ' FCFA',
        'detail' => $joursRetard . ' jour' . ($joursRetard > 1 ? 's' : '') . ' de retard',
        'time'   => Carbon::parse($e->date_echeance)->translatedFormat('d M Y'),
        'link'   => '/echeances',
    ]);
}

// 2. Paiements en attente de validation
$paiementsAttente = Paiement::with('contrat.client')
    ->where('statut', 'EN_ATTENTE')
    ->orderByDesc('date_paiement')
    ->limit(4)
    ->get();

foreach ($paiementsAttente as $p) {
    $notifications->push([
        'id'     => 'pat_' . $p->id_paiement,
        'icon'   => 'paiement',
        'color'  => 'amber',
        'title'  => 'Paiement à valider',
        'body'   => ($p->contrat?->client?->nom_prenoms ?? 'Client') . ' — ' . number_format($p->montant ?? 0, 0, ',', ' ') . ' FCFA',
        'detail' => 'Réf. ' . ($p->reference ?? $p->id_paiement),
        'time'   => Carbon::parse($p->date_paiement)->translatedFormat('d M Y'),
        'link'   => '/paiements',
    ]);
}

// 3. Paiements récents validés (7 derniers jours)
$paiementsRecents = Paiement::with('contrat.client')
    ->where('statut', 'VALIDE')
    ->where('date_paiement', '>=', $today->copy()->subDays(7)->toDateString())
    ->orderByDesc('date_paiement')
    ->limit(4)
    ->get();

foreach ($paiementsRecents as $p) {
    $notifications->push([
        'id'     => 'prec_' . $p->id_paiement,
        'icon'   => 'paiement',
        'color'  => 'green',
        'title'  => 'Paiement reçu',
        'body'   => ($p->contrat?->client?->nom_prenoms ?? 'Client') . ' — ' . number_format($p->montant ?? 0, 0, ',', ' ') . ' FCFA',
        'detail' => 'Réf. ' . ($p->reference ?? $p->id_paiement),
        'time'   => Carbon::parse($p->date_paiement)->diffForHumans(),
        'link'   => '/paiements',
    ]);
}

// 4. Nouveaux contrats (7 derniers jours)
$contratsRecents = Contrat::with('client')
    ->where('created_at', '>=', $today->copy()->subDays(7))
    ->orderByDesc('created_at')
    ->limit(3)
    ->get();

foreach ($contratsRecents as $c) {
    $notifications->push([
        'id'     => 'cnt_' . $c->id_contrat,
        'icon'   => 'contrat',
        'color'  => 'blue',
        'title'  => 'Nouveau contrat',
        'body'   => ($c->client?->nom_prenoms ?? 'Client') . ' — ' . ($c->reference_contrat ?? ''),
        'detail' => number_format($c->montant_total ?? 0, 0, ',', ' ') . ' FCFA',
        'time'   => Carbon::parse($c->created_at)->diffForHumans(),
        'link'   => '/contrats',
    ]);
}

// 5. Visites du jour
$visitesAujourdhui = Visite::where('date_visite', $today->toDateString())
    ->orderBy('heure_arrivee')
    ->limit(3)
    ->get();

foreach ($visitesAujourdhui as $v) {
    $notifications->push([
        'id'     => 'vis_' . $v->id_visite,
        'icon'   => 'visite',
        'color'  => 'purple',
        'title'  => "Visite aujourd'hui",
        'body'   => $v->nom_visiteur ?? 'Visiteur',
        'detail' => $v->heure_arrivee ? substr($v->heure_arrivee, 0, 5) . ' – ' . ($v->motif ?? '') : ($v->motif ?? ''),
        'time'   => "Aujourd'hui",
        'link'   => '/visites',
    ]);
}

$totalCount = $notifications->count();
$notificationsJson = json_encode($notifications->values()->all(), JSON_UNESCAPED_UNICODE);
@endphp

<div class="relative" x-data="{
    dropdownOpen: false,
    items: [],
    storageKey: 'gsf_dismissed_notifs',
    init() {
        const all = {{ $notificationsJson }};
        const dismissed = JSON.parse(localStorage.getItem(this.storageKey) || '[]');
        this.items = all.filter(n => !dismissed.includes(n.id));
    },
    get count() { return this.items.length; },
    get hasItems() { return this.items.length > 0; },
    toggleDropdown() { this.dropdownOpen = !this.dropdownOpen; },
    closeDropdown() { this.dropdownOpen = false; },
    dismiss(id) {
        this.items = this.items.filter(n => n.id !== id);
        const dismissed = JSON.parse(localStorage.getItem(this.storageKey) || '[]');
        if (!dismissed.includes(id)) {
            dismissed.push(id);
            localStorage.setItem(this.storageKey, JSON.stringify(dismissed));
        }
    },
    go(id, link) {
        this.dismiss(id);
        window.location.href = link;
    },
    iconColor(color) {
        const map = {
            red:    'background:#fee2e2;color:#dc2626',
            amber:  'background:#fef3c7;color:#d97706',
            green:  'background:#dcfce7;color:#16a34a',
            blue:   'background:#dbeafe;color:#2563eb',
            purple: 'background:#f3e8ff;color:#9333ea',
        };
        return map[color] || map.blue;
    }
}" x-init="init()" @click.away="closeDropdown()">

    <!-- Bouton cloche -->
    <button
        class="relative flex items-center justify-center text-gray-500 transition-colors bg-white border border-gray-200 rounded-full h-11 w-11 hover:bg-gray-100 hover:text-gray-700 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white"
        @click="toggleDropdown()"
        type="button"
    >
        <!-- Badge -->
        <span x-show="hasItems"
            class="absolute -top-1 -right-1 z-10 flex items-center justify-center min-w-[18px] h-[18px] px-1 rounded-full bg-red-600 text-white text-[10px] font-bold leading-none"
            x-text="count > 9 ? '9+' : count">
        </span>

        <!-- Bell -->
        <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" clip-rule="evenodd"
                d="M10.75 2.29248C10.75 1.87827 10.4143 1.54248 10 1.54248C9.58583 1.54248 9.25004 1.87827 9.25004 2.29248V2.83613C6.08266 3.20733 3.62504 5.9004 3.62504 9.16748V14.4591H3.33337C2.91916 14.4591 2.58337 14.7949 2.58337 15.2091C2.58337 15.6234 2.91916 15.9591 3.33337 15.9591H4.37504H15.625H16.6667C17.0809 15.9591 17.4167 15.6234 17.4167 15.2091C17.4167 14.7949 17.0809 14.4591 16.6667 14.4591H16.375V9.16748C16.375 5.9004 13.9174 3.20733 10.75 2.83613V2.29248ZM14.875 14.4591V9.16748C14.875 6.47509 12.6924 4.29248 10 4.29248C7.30765 4.29248 5.12504 6.47509 5.12504 9.16748V14.4591H14.875ZM8.00004 17.7085C8.00004 18.1228 8.33583 18.4585 8.75004 18.4585H11.25C11.6643 18.4585 12 18.1228 12 17.7085C12 17.2943 11.6643 16.9585 11.25 16.9585H8.75004C8.33583 16.9585 8.00004 17.2943 8.00004 17.7085Z"
                fill=""/>
        </svg>
    </button>

    <!-- Dropdown -->
    <div
        x-show="dropdownOpen"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute -right-[240px] mt-[17px] flex flex-col w-[370px] max-h-[520px] rounded-2xl border border-gray-200 bg-white shadow-theme-lg dark:border-gray-800 dark:bg-gray-dark sm:right-0"
        style="display: none;"
    >
        <!-- Header -->
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100 dark:border-gray-800 flex-shrink-0">
            <div class="flex items-center gap-2">
                <h5 class="text-sm font-semibold text-gray-800 dark:text-white">Notifications</h5>
                <span x-show="hasItems"
                    class="flex items-center justify-center h-5 min-w-[20px] px-1.5 rounded-full bg-red-100 text-red-600 text-xs font-bold dark:bg-red-900/40 dark:text-red-400"
                    x-text="count">
                </span>
            </div>
            <button @click="closeDropdown()" class="p-1 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" type="button">
                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M6.21967 7.28131C5.92678 6.98841 5.92678 6.51354 6.21967 6.22065C6.51256 5.92775 6.98744 5.92775 7.28033 6.22065L11.999 10.9393L16.7176 6.22078C17.0105 5.92789 17.4854 5.92788 17.7782 6.22078C18.0711 6.51367 18.0711 6.98855 17.7782 7.28144L13.0597 12L17.7782 16.7186C18.0711 17.0115 18.0711 17.4863 17.7782 17.7792C17.4854 18.0721 17.0105 18.0721 16.7176 17.7792L11.999 13.0607L7.28033 17.7794C6.98744 18.0722 6.51256 18.0722 6.21967 17.7794C5.92678 17.4865 5.92678 17.0116 6.21967 16.7187L10.9384 12L6.21967 7.28131Z" fill=""/>
                </svg>
            </button>
        </div>

        <!-- Liste rendue par Alpine.js pour permettre la suppression -->
        <ul class="overflow-y-auto flex-1 divide-y divide-gray-50 dark:divide-gray-800">

            <!-- Notifications dynamiques -->
            <template x-for="notif in items" :key="notif.id">
                <li x-show="true"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-x-0"
                    x-transition:leave-end="opacity-0 -translate-x-4">
                    <div class="relative flex items-start gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors group">

                        <!-- Icône -->
                        <span class="flex-shrink-0 flex items-center justify-center w-9 h-9 rounded-full mt-0.5"
                            :style="iconColor(notif.color)">
                            <!-- Icône échéance -->
                            <template x-if="notif.icon === 'echeance'">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </template>
                            <!-- Icône paiement -->
                            <template x-if="notif.icon === 'paiement'">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                                </svg>
                            </template>
                            <!-- Icône contrat -->
                            <template x-if="notif.icon === 'contrat'">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </template>
                            <!-- Icône visite -->
                            <template x-if="notif.icon === 'visite'">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </template>
                        </span>

                        <!-- Contenu cliquable -->
                        <a :href="notif.link" @click.prevent="go(notif.id, notif.link)" class="flex-1 min-w-0 cursor-pointer">
                            <span class="flex items-center justify-between gap-2 mb-0.5">
                                <span class="text-xs font-semibold" :class="{
                                    'text-red-600': notif.color === 'red',
                                    'text-amber-600': notif.color === 'amber',
                                    'text-green-600': notif.color === 'green',
                                    'text-blue-600': notif.color === 'blue',
                                    'text-purple-600': notif.color === 'purple',
                                }" x-text="notif.title"></span>
                                <span class="text-[11px] text-gray-400 whitespace-nowrap" x-text="notif.time"></span>
                            </span>
                            <span class="block text-sm text-gray-700 dark:text-gray-300 truncate" x-text="notif.body"></span>
                            <span class="block text-xs text-gray-400 dark:text-gray-500 mt-0.5" x-text="notif.detail"></span>
                        </a>

                        <!-- Bouton ✕ fermeture -->
                        <button
                            @click.stop="dismiss(notif.id)"
                            type="button"
                            title="Fermer"
                            class="flex-shrink-0 flex items-center justify-center w-5 h-5 rounded-full text-gray-300 hover:text-gray-600 hover:bg-gray-200 dark:hover:bg-gray-600 dark:hover:text-white opacity-0 group-hover:opacity-100 transition-all duration-150 mt-0.5">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </li>
            </template>

            <!-- État vide -->
            <li x-show="!hasItems" class="flex flex-col items-center justify-center py-12 text-gray-400">
                <svg class="w-10 h-10 mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <p class="text-sm">Aucune notification</p>
            </li>
        </ul>

        <!-- Footer résumé -->
        @php
            $nbRetard          = $echeancesRetard->count();
            $nbAttente         = $paiementsAttente->count();
            $nbPaiementsRecents = $paiementsRecents->count();
            $nbContrats        = $contratsRecents->count();
            $nbVisites         = $visitesAujourdhui->count();
        @endphp
        @if($totalCount > 0)
        <div class="border-t border-gray-100 dark:border-gray-800 p-3 flex-shrink-0">
            <div class="grid grid-cols-2 gap-2 text-xs">
                @if($nbRetard > 0)
                    <a href="/echeances" @click="closeDropdown()" class="flex items-center gap-1.5 px-2 py-1.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition-colors font-medium">
                        <span class="font-bold">{{ $nbRetard }}</span> échéance{{ $nbRetard > 1 ? 's' : '' }} en retard
                    </a>
                @endif
                @if($nbAttente > 0)
                    <a href="/paiements" @click="closeDropdown()" class="flex items-center gap-1.5 px-2 py-1.5 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 transition-colors font-medium">
                        <span class="font-bold">{{ $nbAttente }}</span> paiement{{ $nbAttente > 1 ? 's' : '' }} à valider
                    </a>
                @endif
                @if($nbPaiementsRecents > 0)
                    <a href="/paiements" @click="closeDropdown()" class="flex items-center gap-1.5 px-2 py-1.5 rounded-lg bg-green-50 text-green-600 hover:bg-green-100 transition-colors font-medium">
                        <span class="font-bold">{{ $nbPaiementsRecents }}</span> paiement{{ $nbPaiementsRecents > 1 ? 's' : '' }} récent{{ $nbPaiementsRecents > 1 ? 's' : '' }}
                    </a>
                @endif
                @if($nbContrats > 0)
                    <a href="/contrats" @click="closeDropdown()" class="flex items-center gap-1.5 px-2 py-1.5 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors font-medium">
                        <span class="font-bold">{{ $nbContrats }}</span> nouveau{{ $nbContrats > 1 ? 'x' : '' }} contrat{{ $nbContrats > 1 ? 's' : '' }}
                    </a>
                @endif
                @if($nbVisites > 0)
                    <a href="/visites" @click="closeDropdown()" class="flex items-center gap-1.5 px-2 py-1.5 rounded-lg bg-purple-50 text-purple-600 hover:bg-purple-100 transition-colors font-medium">
                        <span class="font-bold">{{ $nbVisites }}</span> visite{{ $nbVisites > 1 ? 's' : '' }} aujourd'hui
                    </a>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

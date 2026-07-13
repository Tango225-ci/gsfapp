@extends('layouts.app')

@section('content')

<div class="mb-6">
    <h1 class="text-xl font-bold text-gray-900 dark:text-white">Import CSV</h1>
    <p class="text-xs text-gray-400 mt-0.5">Chargez vos données depuis des fichiers CSV</p>
</div>

{{-- Bannière ordre d'import --}}
<div class="mb-6 rounded-xl bg-blue-50 border border-blue-200 px-4 py-3 dark:bg-blue-900/10 dark:border-blue-800">
    <div class="flex items-start gap-3">
        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div>
            <p class="text-sm font-semibold text-blue-800 dark:text-blue-300">Respectez l'ordre d'import des dépendances</p>
            <p class="text-xs text-blue-700 dark:text-blue-400 mt-1">
                Le séparateur de colonne dans les fichiers CSV est le
                <span class="font-mono font-bold">;</span> (point-virgule).
                Importez dans cet ordre :
            </p>
            <div class="mt-2 flex flex-wrap items-center gap-1 text-xs">
                @foreach($templates as $key => $tpl)
                    <span class="inline-flex items-center gap-1 rounded-full bg-blue-100 dark:bg-blue-900/30 px-2.5 py-0.5 font-medium text-blue-700 dark:text-blue-300">
                        <span class="font-bold">{{ $tpl['order'] }}.</span> {{ $tpl['label'] }}
                    </span>
                    @if(!$loop->last)
                        <svg class="w-3 h-3 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- Onglets --}}
<div x-data="{ activeTab: '{{ array_key_first($templates) }}' }">

    {{-- Barre d'onglets --}}
    <div class="border-b border-gray-200 dark:border-gray-800 mb-6">
        <nav class="-mb-px flex gap-0.5 overflow-x-auto no-scrollbar">
            @foreach($templates as $key => $tpl)
            <button
                @click="activeTab = '{{ $key }}'"
                class="px-4 py-2.5 text-sm font-medium whitespace-nowrap border-b-2 transition-colors flex items-center gap-1.5"
                :class="activeTab === '{{ $key }}'
                    ? 'border-red-600 text-red-600 dark:text-red-400'
                    : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 hover:border-gray-300'"
            >
                <span
                    class="inline-flex items-center justify-center w-5 h-5 rounded-full text-[10px] font-bold transition-colors"
                    :class="activeTab === '{{ $key }}'
                        ? 'bg-red-100 text-red-700 dark:bg-red-900/30'
                        : 'bg-gray-100 text-gray-500 dark:bg-gray-800'"
                >{{ $tpl['order'] }}</span>
                {{ $tpl['label'] }}
            </button>
            @endforeach
        </nav>
    </div>

    {{-- Panneaux --}}
    @foreach($templates as $key => $tpl)
    <div x-show="activeTab === '{{ $key }}'" x-cloak>
        <div
            x-data="{
                file: null,
                fileName: '',
                loading: false,
                result: null,
                dragOver: false,

                handleFile(f) {
                    if (!f) return;
                    this.file     = f;
                    this.fileName = f.name;
                    this.result   = null;
                },
                async doImport() {
                    if (!this.file) return;
                    this.loading = true;
                    this.result  = null;
                    const fd = new FormData();
                    fd.append('file', this.file);
                    fd.append('_token', document.querySelector('meta[name=csrf-token]').content);
                    try {
                        const r = await fetch('/api/import/{{ $key }}', { method: 'POST', body: fd });
                        this.result = await r.json();
                    } catch (e) {
                        this.result = { success: false, message: 'Erreur réseau.' };
                    }
                    this.loading = false;
                }
            }"
        >
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

                {{-- Colonne gauche : description + tableau colonnes --}}
                <div class="lg:col-span-3 space-y-4">
                    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                        <div class="flex items-center gap-2 mb-1">
                            <h2 class="text-base font-semibold text-gray-900 dark:text-white">{{ $tpl['label'] }}</h2>
                            <span class="text-xs text-gray-500 dark:text-gray-400">— {{ $tpl['desc'] }}</span>
                        </div>

                        <div class="mt-4 overflow-x-auto">
                            <table class="w-full text-xs">
                                <thead>
                                    <tr class="border-b border-gray-100 dark:border-gray-800">
                                        <th class="py-2 pr-3 text-left font-semibold text-gray-600 dark:text-gray-400 w-40">Colonne</th>
                                        <th class="py-2 pr-3 text-left font-semibold text-gray-600 dark:text-gray-400 w-16">Requis</th>
                                        <th class="py-2 pr-3 text-left font-semibold text-gray-600 dark:text-gray-400">Format / Valeurs</th>
                                        <th class="py-2 text-left font-semibold text-gray-600 dark:text-gray-400 w-40">Exemple</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50 dark:divide-gray-800/50">
                                    @foreach($tpl['columns'] as $col)
                                    <tr>
                                        <td class="py-2 pr-3 font-mono text-gray-800 dark:text-gray-200">{{ $col['name'] }}</td>
                                        <td class="py-2 pr-3">
                                            @if($col['req'])
                                                <span class="inline-flex items-center rounded-full bg-red-50 px-2 py-0.5 text-[10px] font-medium text-red-700 dark:bg-red-900/20 dark:text-red-400">Oui</span>
                                            @else
                                                <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-medium text-gray-500 dark:bg-gray-800">Non</span>
                                            @endif
                                        </td>
                                        <td class="py-2 pr-3 text-gray-600 dark:text-gray-400">{{ $col['format'] }}</td>
                                        <td class="py-2 font-mono text-gray-500 dark:text-gray-500">{{ $col['example'] ?: '—' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Colonne droite : téléchargement + upload + résultat --}}
                <div class="lg:col-span-2 space-y-4">

                    {{-- Télécharger le canevas --}}
                    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                        <h3 class="text-sm font-semibold text-gray-800 dark:text-white mb-3">1. Télécharger le canevas</h3>
                        <a
                            href="{{ route('import.template', $key) }}"
                            class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800"
                        >
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            canevas_{{ $key }}.csv
                        </a>
                        <p class="mt-2 text-xs text-gray-400">Ouvrez le fichier dans Excel, remplissez les données, enregistrez en <strong>CSV</strong> (séparateur <code>;</code>).</p>
                    </div>

                    {{-- Zone d'import --}}
                    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                        <h3 class="text-sm font-semibold text-gray-800 dark:text-white mb-3">2. Importer le fichier rempli</h3>

                        {{-- Drop zone --}}
                        <label
                            class="flex flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed px-4 py-8 cursor-pointer transition-colors"
                            :class="dragOver
                                ? 'border-red-400 bg-red-50 dark:bg-red-900/10'
                                : 'border-gray-300 bg-gray-50 hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800/50 dark:hover:bg-gray-800'"
                            @dragover.prevent="dragOver = true"
                            @dragleave="dragOver = false"
                            @drop.prevent="dragOver = false; handleFile($event.dataTransfer.files[0])"
                        >
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            <div class="text-center">
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    <span x-show="!fileName">Glissez votre fichier CSV ici ou <span class="text-red-600 dark:text-red-400 font-medium">cliquez</span></span>
                                    <span x-show="fileName" class="font-medium text-gray-800 dark:text-white" x-text="fileName"></span>
                                </p>
                                <p class="text-xs text-gray-400 mt-1" x-show="!fileName">Fichier .csv — max 10 Mo</p>
                            </div>
                            <input
                                type="file"
                                accept=".csv,.txt"
                                class="hidden"
                                @change="handleFile($event.target.files[0])"
                            >
                        </label>

                        {{-- Bouton import --}}
                        <div class="mt-3 flex items-center gap-3">
                            <button
                                @click="doImport()"
                                :disabled="!file || loading"
                                class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                            >
                                <svg x-show="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                <svg x-show="!loading" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                </svg>
                                <span x-text="loading ? 'Import en cours...' : 'Lancer l\'import'"></span>
                            </button>

                            <button
                                x-show="file"
                                @click="file = null; fileName = ''; result = null;"
                                class="text-xs text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 underline"
                            >Effacer</button>
                        </div>
                    </div>

                    {{-- Résultats --}}
                    <div x-show="result" x-cloak class="rounded-2xl border p-5 transition-all"
                        :class="result && result.success
                            ? 'border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-900/10'
                            : 'border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-900/10'">

                        <div class="flex items-center gap-2 mb-3">
                            <svg x-show="result && result.success" class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <svg x-show="result && !result.success" class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-sm font-semibold"
                                :class="result && result.success ? 'text-green-800 dark:text-green-300' : 'text-red-800 dark:text-red-300'"
                                x-text="result ? result.message : ''">
                            </p>
                        </div>

                        {{-- Stats --}}
                        <div x-show="result && result.success" class="flex gap-4 mb-3">
                            <div class="text-center">
                                <p class="text-2xl font-bold text-green-700 dark:text-green-400" x-text="result ? result.imported : 0"></p>
                                <p class="text-xs text-green-600 dark:text-green-500">Importées</p>
                            </div>
                            <div class="text-center">
                                <p class="text-2xl font-bold text-amber-600 dark:text-amber-400" x-text="result ? result.skipped : 0"></p>
                                <p class="text-xs text-amber-500 dark:text-amber-500">Ignorées</p>
                            </div>
                        </div>

                        {{-- Erreurs / ignorées --}}
                        <div x-show="result && result.errors && result.errors.length > 0" class="space-y-1 max-h-48 overflow-y-auto">
                            <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Détail des lignes ignorées :</p>
                            <template x-for="err in (result ? result.errors : [])" :key="err">
                                <div class="text-xs rounded-lg bg-white/60 dark:bg-black/20 px-3 py-1.5 text-gray-700 dark:text-gray-300" x-text="err"></div>
                            </template>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    @endforeach

</div>

@endsection

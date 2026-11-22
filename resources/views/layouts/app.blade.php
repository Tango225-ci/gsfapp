<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">

    <title>{{ $title ?? 'Tableau de Bord' }} | GSF App - Géomètre Son Femme SARL</title>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine.js -->
    {{-- <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script> --}}

    <!-- Theme Store -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('theme', {
                init() {
                    const savedTheme = localStorage.getItem('theme');
                    const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' :
                        'light';
                    this.theme = savedTheme || systemTheme;
                    this.updateTheme();
                },
                theme: 'light',
                toggle() {
                    this.theme = this.theme === 'light' ? 'dark' : 'light';
                    localStorage.setItem('theme', this.theme);
                    this.updateTheme();
                },
                updateTheme() {
                    const html = document.documentElement;
                    const body = document.body;
                    if (this.theme === 'dark') {
                        html.classList.add('dark');
                        body.classList.add('dark', 'bg-gray-900');
                    } else {
                        html.classList.remove('dark');
                        body.classList.remove('dark', 'bg-gray-900');
                    }
                }
            });

            Alpine.store('sidebar', {
                // Initialize based on screen size
                isExpanded: window.innerWidth >= 1280, // true for desktop, false for mobile
                isMobileOpen: false,
                isHovered: false,

                toggleExpanded() {
                    this.isExpanded = !this.isExpanded;
                    // When toggling desktop sidebar, ensure mobile menu is closed
                    this.isMobileOpen = false;
                },

                toggleMobileOpen() {
                    this.isMobileOpen = !this.isMobileOpen;
                    // Don't modify isExpanded when toggling mobile menu
                },

                setMobileOpen(val) {
                    this.isMobileOpen = val;
                },

                setHovered(val) {
                    // Only allow hover effects on desktop when sidebar is collapsed
                    if (window.innerWidth >= 1280 && !this.isExpanded) {
                        this.isHovered = val;
                    }
                }
            });

            Alpine.store('gsfConfirm', {
                visible: false,
                title: '',
                message: '',
                type: 'danger',
                confirmText: 'Confirmer',
                cancelText: 'Annuler',
                _resolve: null,
                show(opts) {
                    this.title = opts.title || 'Confirmation';
                    this.message = opts.message || '';
                    this.type = opts.type || 'danger';
                    this.confirmText = opts.confirmText || 'Confirmer';
                    this.cancelText = opts.cancelText || 'Annuler';
                    this.visible = true;
                    return new Promise(resolve => { this._resolve = resolve; });
                },
                confirm() {
                    this.visible = false;
                    if (this._resolve) { this._resolve(true); this._resolve = null; }
                },
                cancel() {
                    this.visible = false;
                    if (this._resolve) { this._resolve(false); this._resolve = null; }
                }
            });

            Alpine.store('gsfToast', {
                items: [],
                _nextId: 1,
                add(message, type = 'success', duration = 4000) {
                    const id = this._nextId++;
                    this.items.push({ id, message, type });
                    setTimeout(() => this.remove(id), duration);
                },
                remove(id) {
                    this.items = this.items.filter(t => t.id !== id);
                }
            });

            window.gsfConfirm = (opts) => Alpine.store('gsfConfirm').show(opts);
            window.gsfToast = (message, type = 'success', duration = 4000) => Alpine.store('gsfToast').add(message, type, duration);
        });
    </script>

    <!-- Permissions utilisateur (rôle + droits) -->
    <script>
        window.gsfUser = {
            role: '{{ auth("utilisateurs")->user()?->role ?? "GUEST" }}',
            can: @json(\App\Helpers\PermissionHelper::userPermissions())
        };
    </script>

    <!-- Apply dark mode immediately to prevent flash -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme');
            const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            const theme = savedTheme || systemTheme;
            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
                document.body.classList.add('dark', 'bg-gray-900');
            } else {
                document.documentElement.classList.remove('dark');
                document.body.classList.remove('dark', 'bg-gray-900');
            }
        })();
    </script>

</head>

<body


    x-data="{ 'loaded': true}"
    x-init="$store.sidebar.isExpanded = window.innerWidth >= 1280;
    const checkMobile = () => {
        if (window.innerWidth < 1280) {
            $store.sidebar.setMobileOpen(false);
            $store.sidebar.isExpanded = false;
        } else {
            $store.sidebar.isMobileOpen = false;
            $store.sidebar.isExpanded = true;
        }
    };
    window.addEventListener('resize', checkMobile);">

    {{-- preloader --}}
    <x-common.preloader/>
    {{-- preloader end --}}

    <div class="min-h-screen xl:flex">
        @include('layouts.backdrop')
        @include('layouts.sidebar')

        <div class="flex-1 min-w-0 transition-all duration-300 ease-in-out"
            :class="{
                'xl:ml-[290px]': $store.sidebar.isExpanded || $store.sidebar.isHovered,
                'xl:ml-[90px]': !$store.sidebar.isExpanded && !$store.sidebar.isHovered,
                'ml-0': $store.sidebar.isMobileOpen
            }">
            <!-- app header start -->
            @include('layouts.app-header')
            <!-- app header end -->
            <div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
                @yield('content')
            </div>
        </div>

    </div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>

<!-- GSF: Confirmation Dialog & Toast Notifications -->
<div x-data>
    <!-- Confirmation Dialog -->
    <div x-show="$store.gsfConfirm.visible" x-cloak
         @keydown.escape.window="$store.gsfConfirm.cancel()"
         class="fixed inset-0 z-[200000] flex items-center justify-center p-4"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="$store.gsfConfirm.cancel()"></div>
        <div class="relative w-full max-w-md rounded-2xl bg-white shadow-2xl dark:bg-gray-900" @click.stop
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-4">
            <div class="p-6 text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full"
                     :class="{
                         'bg-red-100 dark:bg-red-900/30': $store.gsfConfirm.type === 'danger',
                         'bg-amber-100 dark:bg-amber-900/30': $store.gsfConfirm.type === 'warning',
                         'bg-blue-100 dark:bg-blue-900/30': $store.gsfConfirm.type === 'info',
                     }">
                    <template x-if="$store.gsfConfirm.type === 'danger'">
                        <svg class="h-8 w-8 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </template>
                    <template x-if="$store.gsfConfirm.type === 'warning'">
                        <svg class="h-8 w-8 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>
                    </template>
                    <template x-if="$store.gsfConfirm.type === 'info'">
                        <svg class="h-8 w-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </template>
                </div>
                <h3 class="mb-2 text-lg font-bold text-gray-900 dark:text-white" x-text="$store.gsfConfirm.title"></h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed" x-html="$store.gsfConfirm.message"></p>
            </div>
            <div class="flex justify-center gap-3 border-t border-gray-100 px-6 py-4 dark:border-gray-800">
                <button @click="$store.gsfConfirm.cancel()"
                        class="min-w-[100px] rounded-xl border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800"
                        x-text="$store.gsfConfirm.cancelText"></button>
                <button @click="$store.gsfConfirm.confirm()"
                        class="min-w-[100px] rounded-xl px-5 py-2.5 text-sm font-semibold text-white transition focus:outline-none focus:ring-2 focus:ring-offset-2"
                        :class="{
                            'bg-red-600 hover:bg-red-700 focus:ring-red-500': $store.gsfConfirm.type === 'danger',
                            'bg-amber-500 hover:bg-amber-600 focus:ring-amber-400': $store.gsfConfirm.type === 'warning',
                            'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500': $store.gsfConfirm.type === 'info',
                        }"
                        x-text="$store.gsfConfirm.confirmText"></button>
            </div>
        </div>
    </div>

    <!-- Toast Notifications -->
    <div class="fixed top-4 right-4 left-4 sm:left-auto sm:w-80 z-[300000] flex flex-col gap-2 pointer-events-none">
        <template x-for="toast in $store.gsfToast.items" :key="toast.id">
            <div class="flex items-start gap-3 rounded-xl p-4 shadow-xl border pointer-events-auto"
                 :class="{
                     'bg-white border-green-200 dark:bg-gray-800 dark:border-green-700': toast.type === 'success',
                     'bg-white border-red-200 dark:bg-gray-800 dark:border-red-700': toast.type === 'error',
                     'bg-white border-amber-200 dark:bg-gray-800 dark:border-amber-700': toast.type === 'warning',
                     'bg-white border-blue-200 dark:bg-gray-800 dark:border-blue-700': toast.type === 'info',
                 }"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-x-full"
                 x-transition:enter-end="opacity-100 translate-x-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-x-0"
                 x-transition:leave-end="opacity-0 translate-x-full">
                <div class="flex-shrink-0">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full"
                         :class="{
                             'bg-green-100 dark:bg-green-900/30': toast.type === 'success',
                             'bg-red-100 dark:bg-red-900/30': toast.type === 'error',
                             'bg-amber-100 dark:bg-amber-900/30': toast.type === 'warning',
                             'bg-blue-100 dark:bg-blue-900/30': toast.type === 'info',
                         }">
                        <template x-if="toast.type === 'success'"><svg class="h-4 w-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg></template>
                        <template x-if="toast.type === 'error'"><svg class="h-4 w-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></template>
                        <template x-if="toast.type === 'warning'"><svg class="h-4 w-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg></template>
                        <template x-if="toast.type === 'info'"><svg class="h-4 w-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></template>
                    </div>
                </div>
                <p class="flex-1 min-w-0 text-sm font-medium text-gray-800 dark:text-white leading-snug pt-0.5" x-text="toast.message"></p>
                <button @click="$store.gsfToast.remove(toast.id)" class="flex-shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        </template>
    </div>
</div>
</body>

@stack('scripts')

</html>

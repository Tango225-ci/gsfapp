
@php
    use App\Helpers\MenuHelper;
    $menuGroups = MenuHelper::getMenuGroups();

    // Get current path
    $currentPath = request()->path();
@endphp

<aside id="sidebar"
    class="fixed flex flex-col mt-0 top-0 px-5 left-0 bg-white dark:bg-gray-900 dark:border-gray-800 text-gray-900 h-screen transition-all duration-300 ease-in-out z-99999 border-r border-gray-200"
    x-data="{
        openSubmenus: {},
        get expanded() {
            return this.$store.sidebar.isExpanded || this.$store.sidebar.isHovered || this.$store.sidebar.isMobileOpen;
        },
        groupActive(paths) {
            return paths.some(p => this.isActive(p));
        },
        init() {
            // Auto-open Dashboard menu on page load
            this.initializeActiveMenus();
        },
        initializeActiveMenus() {
            const currentPath = '{{ $currentPath }}';

            @foreach ($menuGroups as $groupIndex => $menuGroup)
                @foreach ($menuGroup['items'] as $itemIndex => $item)
                    @if (isset($item['subItems']))
                        // Check if any submenu item matches current path
                        @foreach ($item['subItems'] as $subItem)
                            if (this.isActive('{{ $subItem['path'] }}')) {
                                this.openSubmenus['{{ $groupIndex }}-{{ $itemIndex }}'] = true;
                            } @endforeach
            @endif
            @endforeach
            @endforeach
        },
        toggleSubmenu(groupIndex, itemIndex) {
            const key = groupIndex + '-' + itemIndex;
            const newState = !this.openSubmenus[key];

            // Close all other submenus when opening a new one
            if (newState) {
                this.openSubmenus = {};
            }

            this.openSubmenus[key] = newState;
        },
        isSubmenuOpen(groupIndex, itemIndex) {
            const key = groupIndex + '-' + itemIndex;
            return this.openSubmenus[key] || false;
        },
        isActive(path) {
            const current = window.location.pathname;
            if (current === path || '{{ $currentPath }}' === path.replace(/^\//, '')) return true;
            // Sous-pages (ex: /clients/12/edit) : garde l'entrée parente active
            return path !== '/' && current.startsWith(path + '/');
        }
    }"
    :class="{
        'w-[290px]': $store.sidebar.isExpanded || $store.sidebar.isMobileOpen || $store.sidebar.isHovered,
        'w-[90px]': !$store.sidebar.isExpanded && !$store.sidebar.isHovered,
        'translate-x-0': $store.sidebar.isMobileOpen,
        '-translate-x-full xl:translate-x-0': !$store.sidebar.isMobileOpen
    }"
    @mouseenter="if (!$store.sidebar.isExpanded) $store.sidebar.setHovered(true)"
    @mouseleave="$store.sidebar.setHovered(false)">
    <!-- Logo Section -->
    <div class="pt-8 pb-7 flex"
        :class="(!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ?
        'xl:justify-center' :
        'justify-start'">
        <a href="/">
            <img x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                src="/images/logo/gsf-logo.png" alt="GSF Logo" style="height:44px;width:auto;max-width:180px;object-fit:contain;" />
            <img x-show="!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen"
                src="/images/logo/gsf-logo.png" alt="GSF" style="height:32px;width:auto;max-width:60px;object-fit:contain;" />
        </a>
    </div>

    <!-- Navigation Menu -->
    <div class="flex flex-col overflow-y-auto duration-300 ease-linear no-scrollbar">
        <nav class="mb-6">
            <div class="flex flex-col gap-6">
                @foreach ($menuGroups as $groupIndex => $menuGroup)
                    <div>
                        <!-- Titre de section -->
                        <h2 x-show="expanded" class="nav-section-title">{{ $menuGroup['title'] }}</h2>
                        <div x-show="!expanded" class="mx-auto mb-3 h-px w-6 bg-gray-200 dark:bg-gray-800"></div>

                        <ul class="flex flex-col gap-1">
                            @foreach ($menuGroup['items'] as $itemIndex => $item)
                                <li>
                                    @if (isset($item['subItems']))
                                        @php $childPaths = array_column($item['subItems'], 'path'); @endphp
                                        <!-- Entrée avec sous-menu -->
                                        <button type="button" @click="toggleSubmenu({{ $groupIndex }}, {{ $itemIndex }})"
                                            class="nav-item"
                                            :class="[
                                                groupActive(@js($childPaths)) ? 'nav-item-active' :
                                                    (isSubmenuOpen({{ $groupIndex }}, {{ $itemIndex }}) ? 'nav-item-open' : ''),
                                                expanded ? '' : 'xl:justify-center'
                                            ]"
                                            :title="expanded ? '' : @js($item['name'])">
                                            <span class="nav-icon">{!! MenuHelper::getIconSvg($item['icon']) !!}</span>
                                            <span x-show="expanded" class="flex-1 truncate text-left">{{ $item['name'] }}</span>
                                            <svg x-show="expanded" class="nav-chevron"
                                                :class="isSubmenuOpen({{ $groupIndex }}, {{ $itemIndex }}) && 'rotate-90'"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round">
                                                <path d="m9 18 6-6-6-6" />
                                            </svg>
                                        </button>

                                        <!-- Sous-menu -->
                                        <div x-show="isSubmenuOpen({{ $groupIndex }}, {{ $itemIndex }}) && expanded"
                                            x-transition:enter="transition ease-out duration-200"
                                            x-transition:enter-start="opacity-0 -translate-y-1"
                                            x-transition:enter-end="opacity-100 translate-y-0">
                                            <ul class="nav-sub">
                                                @foreach ($item['subItems'] as $subItem)
                                                    <li>
                                                        <a href="{{ $subItem['path'] }}" class="nav-sub-item"
                                                            :class="isActive('{{ $subItem['path'] }}') && 'nav-sub-item-active'">
                                                            {{ $subItem['name'] }}
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @else
                                        <!-- Entrée simple -->
                                        <a href="{{ $item['path'] }}" class="nav-item"
                                            :class="[
                                                isActive('{{ $item['path'] }}') && 'nav-item-active',
                                                expanded ? '' : 'xl:justify-center'
                                            ]"
                                            :title="expanded ? '' : @js($item['name'])">
                                            <span class="nav-icon">{!! MenuHelper::getIconSvg($item['icon']) !!}</span>
                                            <span x-show="expanded" class="flex-1 truncate">{{ $item['name'] }}</span>
                                        </a>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </nav>

        <!-- Sidebar Widget -->
        <div x-data x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen" x-transition class="mt-auto">
            @include('layouts.sidebar-widget')
        </div>

    </div>
</aside>

<!-- Mobile Overlay -->
<div x-show="$store.sidebar.isMobileOpen" @click="$store.sidebar.setMobileOpen(false)"
    class="fixed z-50 h-screen w-full bg-gray-900/50"></div>

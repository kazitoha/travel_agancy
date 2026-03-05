@php
    $navItems = [
        [
            'label' => 'Dashboard',
            'icon' => 'home',
            'route' => 'dashboard',
            'patterns' => ['dashboard'],
            'icon_theme' => 'default',
        ],
        [
            'label' => 'Users',
            'icon' => 'users',
            'route' => 'admin.users.index',
            'patterns' => ['admin.users.*'],
            'icon_theme' => 'emerald',
        ],
        [
            'label' => 'Accounts',
            'icon' => 'wallet',
            'route' => 'accounts.index',
            'patterns' => ['accounts.*'],
            'icon_theme' => 'orange',
        ],
        [
            'label' => 'Expenses',
            'icon' => 'receipt',
            'route' => 'expenses.index',
            'patterns' => ['expenses.*'],
            'icon_theme' => 'emerald',
        ],
        [
            'label' => 'Vendors',
            'icon' => 'store',
            'route' => 'vendors.index',
            'patterns' => ['vendors.*'],
            'icon_theme' => 'orange',
        ],
        [
            'label' => 'Customers',
            'icon' => 'users',
            'route' => 'customers.index',
            'patterns' => ['customers.*'],
            'icon_theme' => 'emerald',
        ],
        [
            'label' => 'References',
            'icon' => 'users',
            'route' => 'references.index',
            'patterns' => ['references.*'],
            'icon_theme' => 'orange',
        ],
        [
            'label' => 'Ticket Purchases',
            'icon' => 'receipt',
            'route' => 'ticket_purchases.index',
            'patterns' => ['ticket_purchases.*'],
            'icon_theme' => 'orange',
        ],
        [
            'label' => 'Ticket Sales',
            'icon' => 'receipt',
            'route' => 'ticket_sales.index',
            'patterns' => ['ticket_sales.*'],
            'icon_theme' => 'emerald',
        ],
    ];
@endphp

@php
    $user = auth()->user();

    $isItemActive = function (array $item): bool {
        $patterns = $item['patterns'] ?? [];
        if (!empty($item['route'])) {
            $patterns[] = $item['route'];
        }
        return !empty($patterns) ? request()->routeIs($patterns) : false;
    };

    $isChildActive = fn(array $child): bool => request()->routeIs($child['route']);

    $hasActiveChild = function (array $item) use ($isChildActive): bool {
        foreach ($item['children'] ?? [] as $child) {
            if ($isChildActive($child)) {
                return true;
            }
        }
        return false;
    };

    $navItems = array_values(
        array_filter($navItems, function (array $item) use ($user) {
            if (!$user) {
                return true;
            }

            $routes = [];
            if (!empty($item['route'])) {
                $routes[] = $item['route'];
            }
            foreach ($item['children'] ?? [] as $child) {
                $routes[] = $child['route'];
            }

            if (empty($routes)) {
                return true;
            }

            foreach ($routes as $r) {
                if (permissionExists($r)) {
                    return true;
                }
            }
            return false;
        }),
    );

    $linkClass = function (bool $active) {
        return $active
            ? 'flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm'
            : 'flex items-center gap-3 rounded-xl px-4 py-3 text-sm text-slate-700 hover:bg-slate-50';
    };

    $summaryClass = function (bool $active) {
        return $active
            ? 'flex cursor-pointer items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm list-none'
            : 'flex cursor-pointer items-center justify-between gap-3 rounded-xl px-4 py-3 text-sm text-slate-700 hover:bg-slate-50 list-none';
    };

    $childLinkClass = function (bool $active) {
        return $active
            ? 'block rounded-xl bg-slate-50 px-4 py-2 text-sm font-semibold text-slate-900'
            : 'block rounded-xl px-4 py-2 text-sm text-slate-600 hover:bg-slate-50';
    };

    $iconBox = function (string $key, bool $active) {
        if ($active) {
            return 'bg-blue-50 text-blue-600';
        }
        return match ($key) {
            'orange' => 'bg-orange-50 text-orange-600',
            'emerald' => 'bg-emerald-50 text-emerald-600',
            default => 'bg-slate-50 text-slate-700',
        };
    };

    $iconSvg = function (string $name) {
        return match ($name) {
            'home'
                => '<svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 10.5 12 3l9 7.5V21a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1v-10.5z"/></svg>',
            'users'
                => '<svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
            'shield'
                => '<svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l8 4v6c0 5-3.5 9.4-8 10-4.5-.6-8-5-8-10V6l8-4z"/></svg>',
            'wallet'
                => '<svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 7a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v2H3V7z"/><path d="M3 9h18v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9z"/><path d="M16 13h3"/></svg>',
            'receipt'
                => '<svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 3h12v18l-3-2-3 2-3-2-3 2V3z"/><path d="M9 8h6M9 12h6M9 16h4"/></svg>',
            'store'
                => '<svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 10l1-5h16l1 5"/><path d="M5 10v10h14V10"/><path d="M9 20v-6h6v6"/></svg>',
            default
                => '<svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 7h18M3 12h18M3 17h18"/></svg>',
        };
    };
@endphp

<!-- MOBILE DRAWER OVERLAY -->
<div id="drawerOverlay"
    class="fixed inset-0 z-40 hidden bg-slate-900/50 backdrop-blur-sm opacity-0 transition-opacity duration-300"></div>

<!-- MOBILE DRAWER -->
<aside id="mobileDrawer"
    class="fixed left-0 top-0 z-50 h-full w-[86%] max-w-xs -translate-x-full bg-white shadow-xl transition-transform duration-300 md:hidden"
    aria-hidden="true">
    <div class="flex items-center justify-between gap-3 border-b border-slate-200 px-4 py-4">
        <div class="flex items-center gap-3">
            <div class="grid h-10 w-10 place-items-center rounded-xl bg-slate-900 text-white">TM</div>
            <div class="leading-tight">
                <div class="text-sm font-semibold">Travel Agency</div>
                <div class="text-xs text-slate-500">Admin Panel</div>
            </div>
        </div>

        <button id="drawerClose"
            class="grid h-10 w-10 place-items-center rounded-xl border border-slate-200 hover:bg-slate-50 transition-all duration-200 hover:border-slate-300 active:scale-95"
            aria-label="Close menu">
            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 6 6 18" />
                <path d="M6 6l12 12" />
            </svg>
        </button>
    </div>

    <div class="px-4 py-4">
        <div class="text-xs font-semibold tracking-widest text-slate-400">NAVIGATION</div>

        <nav class="mt-4 space-y-1">
            @foreach ($navItems as $item)
                @php
                    $hasChildren = !empty($item['children'] ?? []);
                    $active = $isItemActive($item);
                    $open = $active || $hasActiveChild($item);
                    $iconTheme = $item['icon_theme'] ?? ($hasChildren ? 'orange' : 'default');
                @endphp

                @if (!$hasChildren)
                    <a href="{{ route($item['route']) }}" class="{{ $linkClass($active) }}">
                        <span
                            class="grid h-9 w-9 place-items-center rounded-xl {{ $iconBox($iconTheme, $active) }}">
                            {!! $iconSvg($item['icon']) !!}
                        </span>
                        {{ $item['label'] }}
                    </a>
                @else
                    <details class="group" {{ $open ? 'open' : '' }}>
                        <summary class="{{ $summaryClass($active) }}">
                            <span class="flex items-center gap-3">
                                <span
                                    class="grid h-9 w-9 place-items-center rounded-xl {{ $iconBox($iconTheme, $active) }}">
                                    {!! $iconSvg($item['icon']) !!}
                                </span>
                                {{ $item['label'] }}
                            </span>

                            <svg class="h-4 w-4 text-slate-400 transition-transform duration-200 group-open:rotate-180"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M6 9l6 6 6-6" />
                            </svg>
                        </summary>

                        <div class="mt-1 space-y-1 pl-12">
                            @foreach ($item['children'] as $child)
                                @if ($user && !permissionExists($child['route']))
                                    @continue
                                @endif

                                @php $childActive = $isChildActive($child); @endphp

                                <a href="{{ route($child['route']) }}" class="{{ $childLinkClass($childActive) }}">
                                    {{ $child['label'] }}
                                </a>
                            @endforeach
                        </div>
                    </details>
                @endif
            @endforeach
        </nav>
    </div>

    {{-- <div class="mt-auto border-t border-slate-200 p-4">
        <div class="flex items-center gap-3 rounded-xl bg-slate-50 p-3">
            <div class="grid h-10 w-10 place-items-center rounded-full bg-white text-slate-700 ring-1 ring-slate-200">
                A
            </div>
            <div class="min-w-0">
                <div class="truncate text-sm font-semibold">admin</div>
                <div class="truncate text-xs text-slate-500">admin@example.com</div>
            </div>
        </div>
    </div> --}}
</aside>

<style>
    summary::-webkit-details-marker {
        display: none;
    }
</style>

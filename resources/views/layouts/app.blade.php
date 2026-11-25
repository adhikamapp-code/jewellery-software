<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'JewelERP — Complete Jewellery Shop Management')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-800 antialiased selection:bg-amber-500 selection:text-white">
<div class="min-h-screen flex" x-data="{ sidebarOpen: false }">
    <!-- Mobile Backdrop Overlay -->
    <div x-show="sidebarOpen"
         x-transition:enter="transition-opacity ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="sidebarOpen = false"
         class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs z-40 lg:hidden"
         style="display: none;"></div>

    <!-- Sidebar (Drawer on mobile/tablet, Persistent on desktop) -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           class="no-print fixed inset-y-0 left-0 z-50 w-72 max-w-[85vw] bg-slate-900 text-slate-200 flex flex-col border-r border-slate-800 transition-transform duration-300 ease-in-out lg:static lg:w-64 flex-shrink-0 shadow-2xl lg:shadow-none">
        
        <!-- Brand Header -->
        <div class="px-5 py-4 border-b border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-amber-400 to-yellow-600 flex items-center justify-center text-slate-950 font-bold text-lg shadow-sm flex-shrink-0">
                    ✦
                </div>
                <div class="min-w-0">
                    <div class="font-bold text-white tracking-tight truncate max-w-[140px]" title="{{ $businessName }}">{{ $businessName }}</div>
                    <div class="text-[11px] text-amber-400/90 uppercase tracking-wider truncate max-w-[140px]" title="{{ $businessTagline }}">{{ $businessTagline }}</div>
                </div>
            </div>
            <!-- Mobile Close Button -->
            <button type="button" @click="sidebarOpen = false" class="lg:hidden p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 focus:outline-none" aria-label="Close menu">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-5 text-sm overscroll-contain">
            <div>
                <div class="px-3 mb-1.5 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Overview</div>
                <a href="{{ route('dashboard') }}" @click="sidebarOpen = false" class="flex items-center gap-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('dashboard') ? 'bg-amber-500/15 text-amber-300 font-medium' : 'hover:bg-slate-800/70' }}">
                    <span>📊</span> Dashboard & Analytics
                </a>
                <a href="{{ route('rates.index') }}" @click="sidebarOpen = false" class="flex items-center gap-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('rates.*') ? 'bg-amber-500/15 text-amber-300 font-medium' : 'hover:bg-slate-800/70' }}">
                    <span>🪙</span> Metal Rates
                </a>
            </div>

            <div>
                <div class="px-3 mb-1.5 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Billing & Exchange</div>
                <a href="{{ route('pos.index') }}" @click="sidebarOpen = false" class="flex items-center gap-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('pos.index') ? 'bg-amber-500/15 text-amber-300 font-medium' : 'hover:bg-slate-800/70' }}">
                    <span>🧾</span> Jewellery POS
                </a>
                <a href="{{ route('pos.invoices') }}" @click="sidebarOpen = false" class="flex items-center gap-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('pos.invoices', 'pos.invoice') ? 'bg-amber-500/15 text-amber-300 font-medium' : 'hover:bg-slate-800/70' }}">
                    <span>↩️</span> Invoices & Returns
                </a>
                <a href="{{ route('old_gold.index') }}" @click="sidebarOpen = false" class="flex items-center gap-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('old_gold.*') ? 'bg-amber-500/15 text-amber-300 font-medium' : 'hover:bg-slate-800/70' }}">
                    <span>⚖️</span> Old Gold Exchange
                </a>
            </div>

            <div>
                <div class="px-3 mb-1.5 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Catalog & Vault</div>
                <a href="{{ route('products.index') }}" @click="sidebarOpen = false" class="flex items-center gap-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('products.*') ? 'bg-amber-500/15 text-amber-300 font-medium' : 'hover:bg-slate-800/70' }}">
                    <span>💍</span> Product Master
                </a>
                <a href="{{ route('inventory.index') }}" @click="sidebarOpen = false" class="flex items-center gap-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('inventory.index') ? 'bg-amber-500/15 text-amber-300 font-medium' : 'hover:bg-slate-800/70' }}">
                    <span>📦</span> Inventory & Stock
                </a>
                <a href="{{ route('inventory.labels') }}" @click="sidebarOpen = false" class="flex items-center gap-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('inventory.labels') ? 'bg-amber-500/15 text-amber-300 font-medium' : 'hover:bg-slate-800/70' }}">
                    <span>🏷️</span> Barcode & Tags
                </a>
                <a href="{{ route('stones.hallmark') }}" @click="sidebarOpen = false" class="flex items-center gap-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('stones.hallmark') ? 'bg-amber-500/15 text-amber-300 font-medium' : 'hover:bg-slate-800/70' }}">
                    <span>💎</span> Diamonds & HUID
                </a>
            </div>

            <div>
                <div class="px-3 mb-1.5 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Operations</div>
                <a href="{{ route('crm.index') }}" @click="sidebarOpen = false" class="flex items-center gap-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('crm.*') ? 'bg-amber-500/15 text-amber-300 font-medium' : 'hover:bg-slate-800/70' }}">
                    <span>👥</span> CRM, Loyalty & Ads
                </a>
                <a href="{{ route('schemes.index') }}" @click="sidebarOpen = false" class="flex items-center gap-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('schemes.*') ? 'bg-amber-500/15 text-amber-300 font-medium' : 'hover:bg-slate-800/70' }}">
                    <span>🏦</span> Gold Savings Schemes
                </a>
                <a href="{{ route('workshop.index') }}" @click="sidebarOpen = false" class="flex items-center gap-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('workshop.*') ? 'bg-amber-500/15 text-amber-300 font-medium' : 'hover:bg-slate-800/70' }}">
                    <span>🛠️</span> Karigar, Custom & Repair
                </a>
                <a href="{{ route('purchases.index') }}" @click="sidebarOpen = false" class="flex items-center gap-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('purchases.*') ? 'bg-amber-500/15 text-amber-300 font-medium' : 'hover:bg-slate-800/70' }}">
                    <span>🚚</span> Suppliers & Purchases
                </a>
            </div>

            <div>
                <div class="px-3 mb-1.5 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Finance & Admin</div>
                <a href="{{ route('finance.index') }}" @click="sidebarOpen = false" class="flex items-center gap-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('finance.*') ? 'bg-amber-500/15 text-amber-300 font-medium' : 'hover:bg-slate-800/70' }}">
                    <span>📒</span> Accounts & Finance
                </a>
                <a href="{{ route('admin.staff') }}" @click="sidebarOpen = false" class="flex items-center gap-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('admin.staff') ? 'bg-amber-500/15 text-amber-300 font-medium' : 'hover:bg-slate-800/70' }}">
                    <span>🪪</span> Staff & Attendance
                </a>
                <a href="{{ route('admin.branches') }}" @click="sidebarOpen = false" class="flex items-center gap-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('admin.branches') ? 'bg-amber-500/15 text-amber-300 font-medium' : 'hover:bg-slate-800/70' }}">
                    <span>🏢</span> Multi-Branch
                </a>
                <a href="{{ route('admin.security') }}" @click="sidebarOpen = false" class="flex items-center gap-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('admin.security') ? 'bg-amber-500/15 text-amber-300 font-medium' : 'hover:bg-slate-800/70' }}">
                    <span>🔐</span> Approvals & Audit
                </a>
                <a href="{{ route('admin.reports') }}" @click="sidebarOpen = false" class="flex items-center gap-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('admin.reports') ? 'bg-amber-500/15 text-amber-300 font-medium' : 'hover:bg-slate-800/70' }}">
                    <span>📈</span> BI Reports
                </a>
                <a href="{{ route('settings.index') }}" @click="sidebarOpen = false" class="flex items-center gap-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('settings.*') ? 'bg-amber-500/15 text-amber-300 font-medium' : 'hover:bg-slate-800/70' }}">
                    <span>⚙️</span> Showroom Settings
                </a>
            </div>
        </nav>

        <!-- User Profile Footer -->
        <div class="p-4 border-t border-slate-800 text-xs text-slate-400 flex items-center justify-between">
            <div>
                <div class="font-medium text-slate-200 truncate max-w-[150px]">{{ auth()->user()->name }}</div>
                <div class="capitalize text-slate-400">{{ auth()->user()->role }} · {{ auth()->user()->branch->code ?? 'HQ' }}</div>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="text-rose-400 hover:text-rose-300 p-1.5 rounded hover:bg-slate-800" title="Sign Out">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Container -->
    <div class="flex-1 flex flex-col min-w-0">
        <!-- Top Responsive Header -->
        <header class="no-print bg-white border-b border-slate-200 px-3 sm:px-6 py-2.5 sm:py-3 flex items-center justify-between gap-3 sticky top-0 z-30 shadow-xs">
            <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                <!-- Hamburger Button for Mobile & Tablet -->
                <button type="button"
                        @click="sidebarOpen = !sidebarOpen"
                        class="lg:hidden p-2 -ml-1 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-100 focus:outline-none transition"
                        aria-label="Open Navigation Menu">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                <h1 class="text-base sm:text-lg font-bold sm:font-semibold text-slate-800 truncate">@yield('header', 'Dashboard')</h1>
            </div>

            <!-- Top Header Right Actions -->
            <div class="flex items-center gap-2 sm:gap-3 text-xs flex-shrink-0">
                <!-- Full Metal Rate Ticker on Large Screens -->
                <div class="hidden xl:flex items-center gap-3 bg-amber-50 border border-amber-200/80 px-3 py-1.5 rounded-full text-amber-900 font-medium">
                    <span>24K: ₹{{ number_format(\App\Models\MetalRate::latestRate('Gold', '24K'), 0) }}/g</span>
                    <span class="text-amber-300">|</span>
                    <span>22K: ₹{{ number_format(\App\Models\MetalRate::latestRate('Gold', '22K'), 0) }}/g</span>
                    <span class="text-amber-300">|</span>
                    <span>Silver: ₹{{ number_format(\App\Models\MetalRate::latestRate('Silver', '999'), 0) }}/g</span>
                </div>

                <!-- Compact Rate Pill on Mobile & Tablet -->
                <a href="{{ route('rates.index') }}" class="xl:hidden flex items-center gap-1.5 bg-amber-50 border border-amber-200/80 px-2.5 py-1 rounded-full text-amber-900 font-semibold text-[11px] hover:bg-amber-100 transition">
                    <span>🪙</span>
                    <span>24K: ₹{{ number_format(\App\Models\MetalRate::latestRate('Gold', '24K'), 0) }}</span>
                </a>

                <!-- Quick Action + New Bill Button -->
                <a href="{{ route('pos.index') }}" class="inline-flex items-center gap-1.5 bg-amber-600 hover:bg-amber-700 active:bg-amber-800 text-white px-3 sm:px-3.5 py-1.5 rounded-lg font-semibold text-xs transition shadow-xs">
                    <span class="text-sm leading-none">+</span>
                    <span class="hidden sm:inline">New Bill</span>
                    <span class="sm:hidden">POS</span>
                </a>

                <!-- Desktop Logout Button -->
                <form action="{{ route('logout') }}" method="POST" class="hidden sm:inline">
                    @csrf
                    <button type="submit" class="text-slate-500 hover:text-slate-800 font-medium px-2 py-1 transition">Logout</button>
                </form>
            </div>
        </header>

        <!-- Flash Alerts -->
        <div class="px-3 sm:px-6 pt-3 sm:pt-4 no-print">
            @if(session('success'))
                <div class="mb-3 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-2.5 rounded-lg text-sm flex items-center justify-between shadow-xs">
                    <div class="flex items-center gap-2">
                        <span>✓</span>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif
            @if($errors->any())
                <div class="mb-3 bg-rose-50 border border-rose-200 text-rose-800 px-4 py-2.5 rounded-lg text-sm shadow-xs">
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <!-- Page Body Content -->
        <main class="flex-1 px-3 sm:px-6 pb-20 lg:pb-8 pt-1 sm:pt-2 max-w-full overflow-x-hidden">
            @yield('content')
        </main>
    </div>

    <!-- Mobile Bottom Navigation Bar (Phone & Tablet < lg) -->
    <nav class="lg:hidden no-print fixed bottom-0 inset-x-0 bg-slate-900/95 backdrop-blur-md border-t border-slate-800 z-30 flex items-center justify-around py-2 px-1 text-[11px] text-slate-300 shadow-2xl safe-area-bottom">
        <a href="{{ route('dashboard') }}" class="flex flex-col items-center gap-0.5 py-1 px-3 rounded-lg transition {{ request()->routeIs('dashboard') ? 'text-amber-400 font-semibold' : 'hover:text-white' }}">
            <span class="text-lg leading-none">📊</span>
            <span>Home</span>
        </a>
        <a href="{{ route('pos.index') }}" class="flex flex-col items-center gap-0.5 py-1 px-3 rounded-lg transition {{ request()->routeIs('pos.index') ? 'text-amber-400 font-semibold' : 'hover:text-white' }}">
            <span class="text-lg leading-none">🧾</span>
            <span>POS</span>
        </a>
        <a href="{{ route('rates.index') }}" class="flex flex-col items-center gap-0.5 py-1 px-3 rounded-lg transition {{ request()->routeIs('rates.*') ? 'text-amber-400 font-semibold' : 'hover:text-white' }}">
            <span class="text-lg leading-none">🪙</span>
            <span>Rates</span>
        </a>
        <a href="{{ route('inventory.index') }}" class="flex flex-col items-center gap-0.5 py-1 px-3 rounded-lg transition {{ request()->routeIs('products.*', 'inventory.*') ? 'text-amber-400 font-semibold' : 'hover:text-white' }}">
            <span class="text-lg leading-none">💍</span>
            <span>Vault</span>
        </a>
        <button type="button" @click="sidebarOpen = true" class="flex flex-col items-center gap-0.5 py-1 px-3 rounded-lg transition hover:text-white focus:outline-none">
            <span class="text-lg leading-none">☰</span>
            <span>Menu</span>
        </button>
    </nav>
</div>
@stack('scripts')
</body>
</html>

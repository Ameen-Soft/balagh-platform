<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'لوحة الإدارة العامة والرقابة الوطنية' }} - منصة بادر</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-zinc-100 text-zinc-900 font-sans antialiased min-h-screen flex flex-col">

    <!-- Top Yemeni Flag Ribbon -->
    <div class="h-1.5 w-full flex sticky top-0 z-50">
        <div class="h-full w-1/3 bg-[#CE1126]"></div>
        <div class="h-full w-1/3 bg-white"></div>
        <div class="h-full w-1/3 bg-black"></div>
    </div>

    <div class="flex-1 flex" x-data="{ sidebarOpen: false }">
        <!-- Mobile Backdrop Overlay -->
        <div x-show="sidebarOpen" 
             x-cloak 
             @click="sidebarOpen = false" 
             class="fixed inset-0 bg-black/80 backdrop-blur-xs z-40 lg:hidden transition-opacity" 
             x-transition:enter="transition-opacity ease-linear duration-300" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100" 
             x-transition:leave="transition-opacity ease-linear duration-300" 
             x-transition:leave-start="opacity-100" 
             x-transition:leave-end="opacity-0"></div>

        <!-- Sidebar Navigation (Sticky on Desktop, Drawer on Mobile - Near-Black Obsidian Theme) -->
        <aside :class="sidebarOpen ? 'translate-x-0' : 'translate-x-full lg:translate-x-0'"
               class="fixed inset-y-0 right-0 z-50 w-72 max-w-[85vw] bg-[#08080a] text-zinc-300 flex flex-col border-l border-zinc-800/80 shrink-0 transform transition-transform duration-300 ease-in-out lg:static lg:sticky lg:top-1.5 lg:h-[calc(100vh-6px)] lg:overflow-y-auto lg:z-30 shadow-2xl lg:shadow-none">
            <!-- Brand -->
            <div class="p-5 sm:p-6 border-b border-zinc-800/80 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-red-600 to-black flex items-center justify-center text-white shadow-md shadow-red-900/30 shrink-0 border border-white/10">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-base font-black text-white leading-tight">منصة بَــــادِر</h1>
                        <span class="text-[11px] font-bold text-red-500 uppercase tracking-wide">الإدارة العامة والرقابة</span>
                    </div>
                </div>

                <!-- Close Drawer Button for Mobile -->
                <button type="button" 
                        @click="sidebarOpen = false" 
                        class="lg:hidden text-zinc-400 hover:text-white p-2 rounded-xl hover:bg-zinc-800 transition" 
                        aria-label="إغلاق القائمة">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Nav Links -->
            <nav class="flex-1 p-4 space-y-1.5 overflow-y-auto">
                <a href="{{ route('admin.dashboard') }}" 
                   @click="sidebarOpen = false"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-bold transition {{ request()->routeIs('admin.dashboard') ? 'bg-red-600 text-white shadow-sm' : 'text-zinc-400 hover:text-white hover:bg-zinc-900' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span>لوحة المؤشرات والرقابة</span>
                </a>

                <a href="{{ route('admin.complaints.index') }}" 
                   @click="sidebarOpen = false"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-bold transition {{ request()->routeIs('admin.complaints.*') ? 'bg-red-600 text-white shadow-sm' : 'text-zinc-400 hover:text-white hover:bg-zinc-900' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                    <span>الرقابة الشاملة على البلاغات</span>
                </a>

                <a href="{{ route('admin.ministries.index') }}" 
                   @click="sidebarOpen = false"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-bold transition {{ request()->routeIs('admin.ministries.*') ? 'bg-red-600 text-white shadow-sm' : 'text-zinc-400 hover:text-white hover:bg-zinc-900' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <span>الوزارات والإدارات</span>
                </a>

                <a href="{{ route('admin.categories.index') }}" 
                   @click="sidebarOpen = false"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-bold transition {{ request()->routeIs('admin.categories.*') ? 'bg-red-600 text-white shadow-sm' : 'text-zinc-400 hover:text-white hover:bg-zinc-900' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                    <span>تصنيفات البلاغات</span>
                </a>

                <a href="{{ route('admin.users.index') }}" 
                   @click="sidebarOpen = false"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-bold transition {{ request()->routeIs('admin.users.*') ? 'bg-red-600 text-white shadow-sm' : 'text-zinc-400 hover:text-white hover:bg-zinc-900' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span>المستخدمين والصلاحيات</span>
                </a>

                <a href="{{ route('admin.projects.index') }}" 
                   @click="sidebarOpen = false"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-bold transition {{ request()->routeIs('admin.projects.*') ? 'bg-red-600 text-white shadow-sm' : 'text-zinc-400 hover:text-white hover:bg-zinc-900' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <span>المشاريع التنموية</span>
                </a>
            </nav>

            <!-- Bottom User Profile & Return Link -->
            <div class="p-4 border-t border-zinc-800/80 bg-[#0d0d11]">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-9 h-9 rounded-full bg-zinc-800 border border-zinc-700 flex items-center justify-center font-black text-white shrink-0">
                        {{ mb_substr(auth()->user()->name ?? 'م', 0, 1) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-bold text-white truncate">{{ auth()->user()->name }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ url('/') }}" class="flex-1 text-center py-2 px-2 rounded-xl bg-zinc-800 text-[11px] font-bold text-zinc-300 hover:text-white hover:bg-zinc-700 transition">
                        البوابة العامة
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="py-2 px-3 rounded-xl bg-red-950/80 text-red-400 hover:bg-red-900 hover:text-white text-[11px] font-bold transition">
                            خروج
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Workspace Area -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Top Header Bar (Near-Black Obsidian) -->
            <header class="h-16 bg-[#08080a] border-b border-zinc-800/80 px-4 sm:px-6 flex items-center justify-between shadow-xs sticky top-1.5 z-20">
                <div class="flex items-center gap-3 min-w-0">
                    <!-- Hamburger button on mobile -->
                    <button type="button" 
                            @click="sidebarOpen = true" 
                            class="lg:hidden p-2 rounded-xl text-zinc-400 hover:text-white hover:bg-zinc-800 transition focus:outline-none" 
                            aria-label="فتح القائمة الجانبية">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <h2 class="text-sm sm:text-base md:text-lg font-bold text-white truncate">{{ $headerTitle ?? 'لوحة الإدارة العامة' }}</h2>
                    <span class="hidden sm:inline-block text-xs font-semibold px-2.5 py-0.5 rounded-full bg-red-950/80 text-red-400 border border-red-800/80 shrink-0">رقابة عليا</span>
                </div>
                <div class="flex items-center gap-2 sm:gap-4 text-xs font-semibold text-zinc-400 shrink-0">
                    <span class="hidden md:inline">التاريخ: {{ now()->translatedFormat('l j F Y') }}</span>
                    <span class="hidden md:inline w-1.5 h-1.5 rounded-full bg-zinc-700"></span>
                    <span class="text-emerald-400 flex items-center gap-1 font-bold">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span class="hidden sm:inline">المنظومة نشطة</span>
                    </span>
                </div>
            </header>

            <!-- Alerts / Flash Messages -->
            @if(session('success'))
                <div class="mx-4 sm:mx-6 mt-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-bold flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mx-4 sm:mx-6 mt-4 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm font-bold flex items-center gap-2">
                    <svg class="w-5 h-5 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <!-- Main Page Content -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8 overflow-y-auto">
                {{ $slot }}
            </main>
        </div>
    </div>

    @livewireScripts
</body>
</html>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'منصة بادر - البوابة الوطنية الموحدة للبلاغات والشكاوى التنموية' }}</title>
    <meta name="description" content="منصة بادر - المنظومة الوطنية الذكية لربط المواطن بالجهات الحكومية، رصد الشكاوى وإشراك المجتمع في الرقابة ومتابعة المشاريع.">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-slate-50 text-slate-900 font-sans antialiased min-h-screen flex flex-col selection:bg-red-500 selection:text-white">

    <!-- Floating Header Over Page (Takes 0 space from page flow) -->
    <header class="fixed top-0 left-0 right-0 z-50 bg-black/40 backdrop-blur-md border-b border-white/[0.08] shadow-sm transition-all duration-300" x-data="{ mobileMenuOpen: false }">
        <!-- Top Yemeni Flag Line Ribbon -->
        <div class="h-1 w-full flex">
            <div class="h-full w-1/3 bg-[#CE1126]"></div>
            <div class="h-full w-1/3 bg-white"></div>
            <div class="h-full w-1/3 bg-black"></div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="relative flex items-center justify-between h-20">
                <!-- Logo & Brand -->
                <div class="flex items-center gap-4 z-10">
                    <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                        <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-gradient-to-br from-red-600 to-black flex items-center justify-center text-white shadow-md shadow-red-600/20 group-hover:scale-105 transition-transform duration-200 shrink-0 border border-white/10">
                            <!-- Emblem / Balagh Icon -->
                            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                            </svg>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-xl sm:text-2xl font-black tracking-tight text-white flex items-center gap-1.5">
                                بــــادر
                            </span>
                        </div>
                    </a>
                </div>

                <!-- Desktop Navigation Links (Centered) -->
                <nav class="hidden md:flex items-center gap-6 lg:gap-8 absolute left-1/2 -translate-x-1/2 z-10">
                    <a href="{{ url('/') }}" class="text-sm font-bold {{ request()->is('/') ? 'text-red-500 border-b-2 border-red-500 pb-1' : 'text-zinc-300 hover:text-white' }} transition">الرئيسية</a>
                    <a href="{{ route('public.complaints.index') }}" class="text-sm font-bold {{ request()->routeIs('public.complaints.*') ? 'text-red-500 border-b-2 border-red-500 pb-1' : 'text-zinc-300 hover:text-white' }} transition">سجل البلاغات</a>
                    <a href="{{ route('public.projects.index') }}" class="text-sm font-bold {{ request()->routeIs('public.projects.*') ? 'text-red-500 border-b-2 border-red-500 pb-1' : 'text-zinc-300 hover:text-white' }} transition">المشاريع التنموية</a>
                </nav>

                <!-- Auth / Actions & Mobile Hamburger -->
                <div class="flex items-center gap-3">
                    @auth
                        @if(auth()->user()->hasRole('Super Admin'))
                            <a href="{{ route('admin.dashboard') }}" class="hidden sm:inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs sm:text-sm font-bold bg-zinc-900 text-white border border-zinc-700 hover:bg-zinc-800 shadow-sm transition">
                                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                <span>لوحة الإدارة</span>
                            </a>
                        @elseif(auth()->user()->hasRole('Ministry Admin'))
                            <a href="{{ route('ministry.dashboard') }}" class="hidden sm:inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs sm:text-sm font-bold bg-red-600 text-white hover:bg-red-700 shadow-sm transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                <span>بوابة الوزارة</span>
                            </a>
                        @else
                            <a href="{{ route('dashboard') }}" class="hidden sm:inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs sm:text-sm font-bold bg-zinc-900 text-white border border-zinc-700 hover:bg-zinc-800 transition">
                                <span>لوحة التحكم</span>
                            </a>
                        @endif
                    @endauth

                    <!-- Mobile Hamburger Button -->
                    <button type="button" 
                            @click="mobileMenuOpen = !mobileMenuOpen" 
                            class="md:hidden p-2 rounded-xl text-zinc-300 hover:text-white hover:bg-zinc-800/80 transition focus:outline-none" 
                            aria-label="القائمة الرئيسية">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            <path x-show="mobileMenuOpen" x-cloak stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Navigation Dropdown -->
            <div x-show="mobileMenuOpen" 
                 x-cloak 
                 class="md:hidden border-t border-zinc-800 py-4 px-2 space-y-2 bg-zinc-950/95 backdrop-blur-xl rounded-b-2xl shadow-2xl transition-all"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-2">
                <a href="{{ url('/') }}" 
                   @click="mobileMenuOpen = false"
                   class="block px-4 py-2.5 rounded-xl text-sm font-bold {{ request()->is('/') ? 'bg-red-500/10 text-red-500' : 'text-zinc-300 hover:bg-zinc-900 hover:text-white' }} transition">الرئيسية</a>
                <a href="{{ route('public.complaints.index') }}" 
                   @click="mobileMenuOpen = false"
                   class="block px-4 py-2.5 rounded-xl text-sm font-bold {{ request()->routeIs('public.complaints.*') ? 'bg-red-500/10 text-red-500' : 'text-zinc-300 hover:bg-zinc-900 hover:text-white' }} transition">سجل البلاغات</a>
                <a href="{{ route('public.projects.index') }}" 
                   @click="mobileMenuOpen = false"
                   class="block px-4 py-2.5 rounded-xl text-sm font-bold {{ request()->routeIs('public.projects.*') ? 'bg-red-500/10 text-red-500' : 'text-zinc-300 hover:bg-zinc-900 hover:text-white' }} transition">المشاريع التنموية</a>

                @auth
                    <div class="pt-2 border-t border-zinc-800">
                        @if(auth()->user()->hasRole('Super Admin'))
                            <a href="{{ route('admin.dashboard') }}" class="flex items-center justify-center gap-2 w-full py-2.5 rounded-xl text-sm font-bold bg-zinc-900 border border-zinc-700 text-white">
                                لوحة الإدارة العامة
                            </a>
                        @elseif(auth()->user()->hasRole('Ministry Admin'))
                            <a href="{{ route('ministry.dashboard') }}" class="flex items-center justify-center gap-2 w-full py-2.5 rounded-xl text-sm font-bold bg-red-600 text-white">
                                بوابة الوزارة
                            </a>
                        @else
                            <a href="{{ route('dashboard') }}" class="flex items-center justify-center gap-2 w-full py-2.5 rounded-xl text-sm font-bold bg-zinc-900 border border-zinc-700 text-white">
                                لوحة التحكم
                            </a>
                        @endif
                    </div>
                @endauth
            </div>
        </div>
    </header>

    <!-- Content Slot -->
    <main class="flex-1">
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="bg-black text-zinc-400 border-t border-zinc-800 mt-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="md:col-span-2 space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-red-600 flex items-center justify-center text-white font-black text-lg">ب</div>
                        <span class="text-xl font-black text-white">بــــادر - الجمهورية اليمنية</span>
                    </div>
                    <p class="text-sm text-slate-400 leading-relaxed max-w-md">
                        المنصة الوطنية الرقمية الموحدة لاستقبال وتصنيف وتوجيه شكاوى وبلاغات المواطنين، وإتاحة الرقابة المجتمعية التشاركية على المشاريع التنموية بمرونة وشفافية متكاملة.
                    </p>
                    <div class="flex items-center gap-2 text-xs text-slate-300 font-semibold">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        النظام متصل ويعمل بكامل طاقته التشغيلية
                    </div>
                </div>

                <div>
                    <h4 class="text-sm font-bold text-white uppercase tracking-wider mb-4">روابط سريعة</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ url('/') }}" class="hover:text-red-400 transition">الرئيسية</a></li>
                        <li><a href="{{ route('public.complaints.index') }}" class="hover:text-red-400 transition">سجل البلاغات المفتوح</a></li>
                        <li><a href="{{ route('public.projects.index') }}" class="hover:text-red-400 transition">المشاريع التنموية</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-sm font-bold text-white uppercase tracking-wider mb-4">تطبيق الهاتف</h4>
                    <p class="text-xs text-slate-400 leading-relaxed mb-3">
                        يمكن للمواطنين والفرق الميدانية تقديم البلاغات ومتابعتها عبر تطبيق الهاتف الذكي بالـGPS ودون انقطاع.
                    </p>
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-zinc-900 border border-zinc-800 text-xs text-zinc-300">
                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        <span>تطبيق بادر (Flutter)</span>
                    </div>
                </div>
            </div>

            <div class="border-t border-zinc-800/80 mt-12 pt-8 flex flex-col sm:flex-row items-center justify-between text-xs text-zinc-400 gap-4">
                <p>© {{ date('Y') }} منصة بادر - جميع الحقوق محفوظة للجمهورية اليمنية.</p>
                <div class="flex items-center gap-4">
                    <span>الهوية الوطنية: الأحمر والأبيض والأسود</span>
                </div>
            </div>
        </div>
    </footer>

    @livewireScripts
</body>
</html>

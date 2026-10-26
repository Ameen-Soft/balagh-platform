<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <x-site-head :title="$title ?? null" />

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
                        @if(setting_url('site_logo'))
                            <img src="{{ setting_url('site_logo') }}" alt="{{ setting('site_name', 'بــــادر') }}" class="w-12 h-12 sm:w-11 sm:h-11 object-contain rounded-xl bg-white p-1 border border-white/10 shrink-0 group-hover:scale-105 transition-transform duration-200">
                        @else
                            <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-gradient-to-br from-red-600 to-black flex items-center justify-center text-white shadow-md shadow-red-600/20 group-hover:scale-105 transition-transform duration-200 shrink-0 border border-white/10">
                                <!-- Emblem / Balagh Icon -->
                                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                                </svg>
                            </div>
                        @endif
                        <div class="flex flex-col">
                            <span class="text-xl sm:text-2xl font-black tracking-tight text-white flex items-center gap-1.5">
                                {{ setting('site_name', 'بــــادر') }}
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
                        @if(setting_url('site_logo'))
                            <img src="{{ setting_url('site_logo') }}" alt="{{ setting('site_name', 'بــــادر') }}" class="w-9 h-9 object-contain rounded-lg bg-white/5 p-1 border border-white/10 shrink-0">
                        @else
                            <div class="w-9 h-9 rounded-lg bg-red-600 flex items-center justify-center text-white font-black text-lg">ب</div>
                        @endif
                        <span class="text-xl font-black text-white">{{ setting('site_name', 'بــــادر') }} - {{ setting('meta_author', 'الجمهورية اليمنية') }}</span>
                    </div>
                    <p class="text-sm text-slate-400 leading-relaxed max-w-md">
                        {{ setting('meta_description', 'المنصة الوطنية الرقمية الموحدة لاستقبال وتصنيف وتوجيه شكاوى وبلاغات المواطنين، وإتاحة الرقابة المجتمعية التشاركية على المشاريع التنموية بمرونة وشفافية متكاملة.') }}
                    </p>

                    @if(setting('support_hotline') || setting('support_email') || setting('support_address'))
                        <div class="pt-2 flex flex-wrap items-center gap-4 text-xs text-zinc-300">
                            @if(setting('support_hotline'))
                                <a href="tel:{{ setting('support_hotline') }}" class="inline-flex items-center gap-1.5 hover:text-white transition">
                                    <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                    <span>للتواصل: {{ setting('support_hotline') }}</span>
                                </a>
                            @endif
                            @if(setting('support_email'))
                                <a href="mailto:{{ setting('support_email') }}" class="inline-flex items-center gap-1.5 hover:text-white transition">
                                    <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    <span>{{ setting('support_email') }}</span>
                                </a>
                            @endif
                        </div>
                    @endif

                    @if(setting('social_facebook') || setting('social_twitter') || setting('social_telegram'))
                        <div class="flex items-center gap-3 pt-1">
                            @if(setting('social_facebook'))
                                <a href="{{ setting('social_facebook') }}" target="_blank" rel="noopener noreferrer" class="w-8 h-8 rounded-lg bg-zinc-900 border border-zinc-800 flex items-center justify-center text-zinc-400 hover:text-white hover:bg-zinc-800 transition" aria-label="Facebook">
                                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                </a>
                            @endif
                            @if(setting('social_twitter'))
                                <a href="{{ setting('social_twitter') }}" target="_blank" rel="noopener noreferrer" class="w-8 h-8 rounded-lg bg-zinc-900 border border-zinc-800 flex items-center justify-center text-zinc-400 hover:text-white hover:bg-zinc-800 transition" aria-label="Twitter / X">
                                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                                </a>
                            @endif
                            @if(setting('social_telegram'))
                                <a href="{{ setting('social_telegram') }}" target="_blank" rel="noopener noreferrer" class="w-8 h-8 rounded-lg bg-zinc-900 border border-zinc-800 flex items-center justify-center text-zinc-400 hover:text-white hover:bg-zinc-800 transition" aria-label="Telegram">
                                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.75-.55 2.92-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .38z"/></svg>
                                </a>
                            @endif
                        </div>
                    @endif
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
                <p>© {{ date('Y') }} {{ setting('site_name', 'منصة بادر') }} - جميع الحقوق محفوظة للجمهورية اليمنية.</p>
                <div class="flex items-center gap-4">
                    <span>الهوية الوطنية: الأحمر والأبيض والأسود</span>
                </div>
            </div>
        </div>
    </footer>

    @livewireScripts
    @if(setting('custom_footer_scripts'))
        {!! setting('custom_footer_scripts') !!}
    @endif
</body>
</html>

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
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-white text-[#08080a] font-sans antialiased min-h-screen flex flex-col selection:bg-[#CE1126] selection:text-white">

    <!-- Top Yemeni Flag Ribbon -->
    <div class="h-1 w-full flex shrink-0">
        <div class="h-full w-1/3 bg-[#CE1126]"></div>
        <div class="h-full w-1/3 bg-white"></div>
        <div class="h-full w-1/3 bg-[#08080a]"></div>
    </div>

    <!-- Main Header -->
    <header class="sticky top-0 z-50 bg-white border-b border-neutral-200" x-data="{ mobileMenuOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 sm:h-20">
                
                <!-- Brand Identity (Pure Logo without box) -->
                <div class="flex items-center gap-6">
                    <a href="{{ url('/') }}" class="flex items-center gap-3">
                        @if(setting_url('site_logo'))
                            <img src="{{ setting_url('site_logo') }}" 
                                 alt="{{ setting('site_name', 'بــــادر') }}" 
                                 class="h-[60px] sm:h-[70px] w-auto object-contain shrink-0">
                        @endif
                        <span class="text-xl sm:text-2xl font-black tracking-tight text-[#08080a]">
                            {{ setting('site_name', 'بــــادر') }}
                        </span>
                    </a>

                    <!-- Desktop Navigation Links -->
                    <nav class="hidden md:flex items-center gap-2 pr-6 border-r border-neutral-200">
                        <a href="{{ url('/') }}" 
                           class="px-4 py-2 rounded-lg text-sm font-bold transition {{ request()->is('/') ? 'bg-[#08080a] text-white' : 'text-[#08080a] hover:bg-neutral-100' }}">
                            الرئيسية
                        </a>
                        <a href="{{ route('public.complaints.index') }}" 
                           class="px-4 py-2 rounded-lg text-sm font-bold transition {{ request()->routeIs('public.complaints.*') ? 'bg-[#08080a] text-white' : 'text-[#08080a] hover:bg-neutral-100' }}">
                            سجل البلاغات
                        </a>
                        <a href="{{ route('public.projects.index') }}" 
                           class="px-4 py-2 rounded-lg text-sm font-bold transition {{ request()->routeIs('public.projects.*') ? 'bg-[#08080a] text-white' : 'text-[#08080a] hover:bg-neutral-100' }}">
                            المشاريع التنموية
                        </a>
                    </nav>
                </div>

                <!-- Actions & Mobile Menu Button -->
                <div class="flex items-center gap-3">
                    @auth
                        @if(auth()->user()->hasRole('Super Admin'))
                            <a href="{{ route('admin.dashboard') }}" class="hidden sm:inline-flex items-center px-4 py-2.5 rounded-lg text-xs font-bold bg-[#08080a] text-white hover:bg-[#121217] transition">
                                لوحة الإدارة
                            </a>
                        @elseif(auth()->user()->hasRole('Ministry Admin'))
                            <a href="{{ route('ministry.dashboard') }}" class="hidden sm:inline-flex items-center px-4 py-2.5 rounded-lg text-xs font-bold bg-[#CE1126] text-white hover:bg-[#B70E20] transition">
                                بوابة الوزارة
                            </a>
                        @else
                            <a href="{{ route('dashboard') }}" class="hidden sm:inline-flex items-center px-4 py-2.5 rounded-lg text-xs font-bold bg-[#08080a] text-white hover:bg-[#121217] transition">
                                لوحة التحكم
                            </a>
                        @endif
                    @else
                        <a href="{{ route('public.complaints.index') }}" class="hidden sm:inline-flex items-center px-4 py-2.5 rounded-lg text-xs font-bold bg-[#CE1126] text-white hover:bg-[#B70E20] transition">
                            استعلام عن بلاغ
                        </a>
                    @endauth

                    <!-- Mobile Menu Toggle -->
                    <button type="button" 
                            @click="mobileMenuOpen = !mobileMenuOpen" 
                            class="md:hidden p-2 rounded-lg text-[#08080a] hover:bg-neutral-100 border border-neutral-200 transition focus:outline-none" 
                            aria-label="القائمة الرئيسية">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            <path x-show="mobileMenuOpen" x-cloak stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Navigation Drawer (Stacked cleanly) -->
            <div x-show="mobileMenuOpen" 
                 x-cloak 
                 class="md:hidden border-t border-neutral-200 py-4 space-y-2 bg-white">
                <a href="{{ url('/') }}" 
                   @click="mobileMenuOpen = false"
                   class="block px-4 py-3 rounded-lg text-sm font-bold {{ request()->is('/') ? 'bg-[#08080a] text-white' : 'text-[#08080a] hover:bg-neutral-100' }}">
                    الرئيسية
                </a>
                <a href="{{ route('public.complaints.index') }}" 
                   @click="mobileMenuOpen = false"
                   class="block px-4 py-3 rounded-lg text-sm font-bold {{ request()->routeIs('public.complaints.*') ? 'bg-[#08080a] text-white' : 'text-[#08080a] hover:bg-neutral-100' }}">
                    سجل البلاغات
                </a>
                <a href="{{ route('public.projects.index') }}" 
                   @click="mobileMenuOpen = false"
                   class="block px-4 py-3 rounded-lg text-sm font-bold {{ request()->routeIs('public.projects.*') ? 'bg-[#08080a] text-white' : 'text-[#08080a] hover:bg-neutral-100' }}">
                    المشاريع التنموية
                </a>

                <div class="pt-2 border-t border-neutral-200">
                    @auth
                        @if(auth()->user()->hasRole('Super Admin'))
                            <a href="{{ route('admin.dashboard') }}" class="block text-center w-full py-3 rounded-lg text-sm font-bold bg-[#08080a] text-white">
                                لوحة الإدارة
                            </a>
                        @elseif(auth()->user()->hasRole('Ministry Admin'))
                            <a href="{{ route('ministry.dashboard') }}" class="block text-center w-full py-3 rounded-lg text-sm font-bold bg-[#CE1126] text-white">
                                بوابة الوزارة
                            </a>
                        @else
                            <a href="{{ route('dashboard') }}" class="block text-center w-full py-3 rounded-lg text-sm font-bold bg-[#08080a] text-white">
                                لوحة التحكم
                            </a>
                        @endif
                    @else
                        <a href="{{ route('public.complaints.index') }}" class="block text-center w-full py-3 rounded-lg text-sm font-bold bg-[#CE1126] text-white">
                            استعلام عن بلاغ
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1">
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="bg-[#08080a] text-neutral-400 border-t border-neutral-800 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                
                <!-- Brand Info -->
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        @if(setting_url('site_logo'))
                            <img src="{{ setting_url('site_logo') }}" 
                                 alt="{{ setting('site_name', 'بــــادر') }}" 
                                 class="h-10 rounded-lg w-auto object-contain shrink-0 bg-white">
                        @endif
                        <span class="text-xl font-black text-white">{{ setting('site_name', 'بــــادر') }}</span>
                    </div>
                    <p class="text-xs text-neutral-400 leading-relaxed">
                        {{ setting('meta_description', 'المنصة الوطنية الرقمية الموحدة لاستقبال وتوجيه بلاغات المواطنين ومتابعة المشاريع التنموية.') }}
                    </p>
                </div>

                <!-- Quick Links -->
                <div>
                    <h4 class="text-xs font-bold text-white mb-4">روابط البوابة</h4>
                    <ul class="space-y-3 text-xs font-bold">
                        <li><a href="{{ url('/') }}" class="hover:text-white transition block">الرئيسية</a></li>
                        <li><a href="{{ route('public.complaints.index') }}" class="hover:text-white transition block">سجل البلاغات العام</a></li>
                        <li><a href="{{ route('public.projects.index') }}" class="hover:text-white transition block">المشاريع التنموية</a></li>
                    </ul>
                </div>

                <!-- App Download & Contact -->
                <div class="space-y-4">
                    <h4 class="text-xs font-bold text-white">تطبيق الهاتف والتواصل</h4>
                    <a href="{{ setting_url('app_apk_file') ?? '#' }}" 
                       @if(setting_url('app_apk_file')) download="بادر.apk" @endif 
                       class="inline-flex items-center justify-center w-full sm:w-auto px-5 py-3 rounded-lg bg-[#CE1126] hover:bg-[#B70E20] text-xs text-white font-bold transition">
                        تحميل تطبيق بادر (APK)
                    </a>

                    @if(setting('support_hotline') || setting('support_email'))
                        <div class="space-y-2 text-xs text-neutral-300 pt-1">
                            @if(setting('support_hotline'))
                                <a href="tel:{{ setting('support_hotline') }}" class="block hover:text-white transition font-mono">
                                    {{ setting('support_hotline') }}
                                </a>
                            @endif
                            @if(setting('support_email'))
                                <a href="mailto:{{ setting('support_email') }}" class="block hover:text-white transition font-mono">
                                    {{ setting('support_email') }}
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <div class="border-t border-neutral-800 mt-10 pt-6 flex flex-col sm:flex-row items-center justify-between text-xs text-neutral-500 gap-4 text-center sm:text-right">
                <p>© {{ date('Y') }} {{ setting('site_name', 'منصة بادر') }} — جميع الحقوق محفوظة.</p>
                @if(setting('social_facebook') || setting('social_twitter') || setting('social_telegram'))
                    <div class="flex items-center gap-4">
                        @if(setting('social_facebook'))
                            <a href="{{ setting('social_facebook') }}" target="_blank" rel="noopener noreferrer" class="hover:text-white transition">فيسبوك</a>
                        @endif
                        @if(setting('social_twitter'))
                            <a href="{{ setting('social_twitter') }}" target="_blank" rel="noopener noreferrer" class="hover:text-white transition">إكس</a>
                        @endif
                        @if(setting('social_telegram'))
                            <a href="{{ setting('social_telegram') }}" target="_blank" rel="noopener noreferrer" class="hover:text-white transition">تيليجرام</a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </footer>

    <!-- Floating APK Download Button (Always Visible) -->
    <div class="fixed bottom-5 left-4 sm:bottom-6 sm:left-6 z-50 print:hidden">
        <a href="{{ setting_url('app_apk_file') ?? '#' }}" 
           @if(setting_url('app_apk_file')) download="بادر.apk" @endif
           class="group inline-flex items-center gap-2.5 px-4 py-2.5 sm:px-5 sm:py-3 rounded-full bg-[#CE1126] hover:bg-[#B70E20] text-white shadow-2xl shadow-red-900/30 border border-white/20 transition-all duration-300 hover:shadow-2xl hover:-translate-y-1 active:scale-95 text-xs font-bold"
           title="تحميل تطبيق بادر (APK)">
            <svg class="w-4 h-4 shrink-0 transition-transform group-hover:translate-y-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            <span>تحميل تطبيق بادر (APK)</span>
        </a>
    </div>

    @livewireScripts
    @if(setting('custom_footer_scripts'))
        {!! setting('custom_footer_scripts') !!}
    @endif
</body>
</html>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <x-site-head :title="$title ?? 'تسجيل الدخول'" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-white text-[#08080a] font-sans antialiased min-h-screen flex flex-col selection:bg-[#CE1126] selection:text-white">

    <!-- Top Yemeni Flag Line Ribbon -->
    <div class="h-1 w-full flex shrink-0">
        <div class="h-full w-1/3 bg-[#CE1126]"></div>
        <div class="h-full w-1/3 bg-white"></div>
        <div class="h-full w-1/3 bg-[#08080a]"></div>
    </div>

    <!-- Main Full-Screen Slot -->
    <div class="flex-1 flex flex-col">
        {{ $slot }}
    </div>

    @livewireScripts
    @if(setting('custom_footer_scripts'))
        {!! setting('custom_footer_scripts') !!}
    @endif
    @stack('scripts')
</body>
</html>
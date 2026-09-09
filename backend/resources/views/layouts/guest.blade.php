<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'تسجيل الدخول - منصة بادر الوطنية' }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gradient-to-b from-black via-[#08080a] to-black text-white font-sans antialiased min-h-screen flex flex-col selection:bg-red-500 selection:text-white relative overflow-x-hidden">

    <!-- Top Yemeni Flag Line Ribbon -->
    <div class="h-1.5 w-full flex fixed top-0 left-0 right-0 z-50">
        <div class="h-full w-1/3 bg-[#CE1126]"></div>
        <div class="h-full w-1/3 bg-white"></div>
        <div class="h-full w-1/3 bg-black"></div>
    </div>

    <!-- Background Atmospheric Elements (Near-Black & Subtle Red) -->
    <div class="fixed inset-0 opacity-10 bg-[radial-gradient(#ce1126_1px,transparent_1px)] [background-size:24px_24px] pointer-events-none"></div>
    <div class="fixed -top-40 right-1/4 w-[500px] h-[500px] bg-red-600/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="fixed -bottom-40 left-1/4 w-[500px] h-[500px] bg-zinc-900/30 rounded-full blur-3xl pointer-events-none"></div>

    <!-- Content Slot -->
    <div class="relative z-10 flex-1 flex flex-col justify-center items-center py-12 px-4 sm:px-6 lg:px-8">
        {{ $slot }}
    </div>

    @livewireScripts
</body>
</html>

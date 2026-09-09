<x-guest-layout title="استعادة كلمة المرور - منصة بادر الوطنية">
    <div class="w-full max-w-md">
        <!-- Logo & Header -->
        <div class="text-center mb-8">
            <a href="{{ url('/') }}" class="inline-flex items-center justify-center group mb-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-red-600 to-black flex items-center justify-center text-white shadow-xl shadow-red-600/20 group-hover:scale-105 transition-transform duration-200 border border-white/10">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                    </svg>
                </div>
            </a>
            <h1 class="text-2xl font-black text-white tracking-tight">استعادة كلمة المرور</h1>
            <p class="text-xs text-zinc-400 mt-1">أدخل بريدك الإلكتروني المسجل لإرسال رابط إعادة التعيين</p>
        </div>

        <div class="bg-[#0d0d11]/90 backdrop-blur-xl border border-zinc-800/80 rounded-3xl p-8 shadow-2xl relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-red-600 via-amber-500 to-zinc-800"></div>

            @if ($errors->any())
                <div class="mb-6 p-4 rounded-2xl bg-red-950/80 border border-red-800/80 text-red-300 text-xs font-bold space-y-1">
                    @foreach ($errors->all() as $error)
                        <p>• {{ $error }}</p>
                    @endforeach
                </div>
            @endif

            @session('status')
                <div class="mb-6 p-4 rounded-2xl bg-emerald-950/80 border border-emerald-800/80 text-emerald-300 text-xs font-bold">
                    {{ $value }}
                </div>
            @endsession

            <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-xs font-bold text-zinc-300 mb-2">البريد الإلكتروني المعتمد</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus 
                           placeholder="user@example.test"
                           class="w-full px-4 py-3 rounded-2xl bg-black/60 border border-zinc-800 text-sm text-white placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 font-medium">
                </div>

                <button type="submit" class="w-full py-3.5 px-4 rounded-2xl bg-red-600 hover:bg-red-700 text-white font-bold text-sm shadow-lg shadow-red-600/30 transition">
                    إرسال رابط استعادة كلمة المرور
                </button>
            </form>
        </div>

        <div class="text-center mt-6">
            <a href="{{ route('login') }}" class="text-xs font-bold text-zinc-400 hover:text-white transition">
                ← العودة لصفحة تسجيل الدخول
            </a>
        </div>
    </div>
</x-guest-layout>

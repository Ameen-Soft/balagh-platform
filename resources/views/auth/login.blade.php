<x-guest-layout title="تسجيل الدخول - منصة بادر الوطنية">
    <div class="w-full max-w-md">
        <!-- Logo & Header -->
        <div class="text-center mb-8">
            <a href="{{ url('/') }}" class="inline-flex items-center justify-center group mb-4">
                @if(setting_url('site_logo'))
                    <img src="{{ setting_url('site_logo') }}" alt="{{ setting('site_name', 'بــــادر') }}" class="w-16 h-16 object-contain rounded-2xl bg-white/5 p-2 border border-white/10 shadow-xl shadow-red-600/20 group-hover:scale-105 transition-transform duration-200">
                @else
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-red-600 to-black flex items-center justify-center text-white shadow-xl shadow-red-600/20 group-hover:scale-105 transition-transform duration-200 border border-white/10">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                        </svg>
                    </div>
                @endif
            </a>
            <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">
                {{ setting('site_name', 'منصة بــــادر') }}
            </h1>
        </div>

        <!-- Auth Card (Near-Black Obsidian) -->
        <div class="bg-[#0d0d11]/90 backdrop-blur-xl border border-zinc-800/80 rounded-3xl p-8 shadow-2xl relative overflow-hidden">
            <!-- Subtle accent bar inside card -->
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-red-600 via-amber-500 to-zinc-800"></div>

            <!-- Validation Errors -->
            @if ($errors->any())
                <div class="mb-6 p-4 rounded-2xl bg-red-950/80 border border-red-800/80 text-red-300 text-xs font-bold space-y-1">
                    <div class="flex items-center gap-2 text-red-200 mb-1">
                        <svg class="w-4 h-4 text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>تعذر تسجيل الدخول:</span>
                    </div>
                    @foreach ($errors->all() as $error)
                        <p class="leading-relaxed text-red-400">• {{ $error }}</p>
                    @endforeach
                </div>
            @endif

            @session('status')
                <div class="mb-6 p-4 rounded-2xl bg-emerald-950/80 border border-emerald-800/80 text-emerald-300 text-xs font-bold flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ $value }}</span>
                </div>
            @endsession

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <!-- Email Input -->
                <div>
                    <label for="email" class="block text-xs font-bold text-zinc-300 mb-2">
                        البريد الإلكتروني المعتمد
                    </label>
                    <div class="relative">
                        <input id="email" 
                               type="email" 
                               name="email" 
                               value="{{ old('email') }}" 
                               required 
                               autofocus 
                               autocomplete="username" 
                               placeholder="user@example.test"
                               class="w-full pl-4 pr-11 py-3 rounded-2xl bg-black/60 border border-zinc-800 text-sm text-white placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition font-medium">
                        <div class="absolute right-3.5 top-3.5 text-zinc-400 pointer-events-none">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Password Input -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label for="password" class="block text-xs font-bold text-zinc-300">
                            كلمة المرور
                        </label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-[11px] font-bold text-red-400 hover:text-red-300 transition">
                                نسيت كلمة المرور؟
                            </a>
                        @endif
                    </div>
                    <div class="relative">
                        <input id="password" 
                               type="password" 
                               name="password" 
                               required 
                               autocomplete="current-password" 
                               placeholder="••••••••"
                               class="w-full pl-4 pr-11 py-3 rounded-2xl bg-black/60 border border-zinc-800 text-sm text-white placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition font-medium">
                        <div class="absolute right-3.5 top-3.5 text-zinc-400 pointer-events-none">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between pt-1">
                    <label for="remember_me" class="flex items-center gap-2 cursor-pointer select-none">
                        <input id="remember_me" type="checkbox" name="remember" 
                               class="w-4 h-4 rounded-md bg-black border-zinc-700 text-red-600 focus:ring-red-500 focus:ring-offset-black">
                        <span class="text-xs font-semibold text-zinc-400">تذكر تسجيل دخولي</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button type="submit" 
                            class="w-full py-3.5 px-4 rounded-2xl bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white font-bold text-sm shadow-lg shadow-red-600/30 hover:shadow-red-600/40 hover:scale-[1.01] active:scale-[0.99] transition transform duration-150 flex items-center justify-center gap-2">
                        <span>تسجيل الدخول للمنظومة</span>
                        <svg class="w-4 h-4 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </div>
            </form>

            @php
                $googleClientId = config('services.google.web_client_id') ?: config('services.google.client_id');
            @endphp

            @if($googleClientId)
                <!-- Divider -->
                <div class="relative my-6">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-zinc-800"></div>
                    </div>
                    <div class="relative flex justify-center text-xs">
                        <span class="px-4 bg-[#0d0d11] text-zinc-400 font-semibold">أو المتابعة عبر الهوية الرقمية</span>
                    </div>
                </div>

                <!-- Google Auth Error Message Box -->
                <div id="google-auth-error" class="hidden mb-4 p-3.5 rounded-2xl bg-red-950/80 border border-red-800/80 text-red-300 text-xs font-bold leading-relaxed flex items-start gap-2">
                    <svg class="w-4 h-4 text-red-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span id="google-auth-error-text"></span>
                </div>

                <!-- Google Auth Loading Indicator -->
                <div id="google-auth-loading" class="hidden mb-4 p-3 rounded-2xl bg-zinc-900/90 border border-zinc-700 text-zinc-300 text-xs font-bold items-center justify-center gap-2">
                    <svg class="animate-spin h-4 w-4 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span>جارٍ التحقق من الحساب وتأمين الجلسة...</span>
                </div>

                <!-- Google Identity Services Button -->
                <div class="w-full flex justify-center">
                    <div id="g_id_onload"
                         data-client_id="{{ $googleClientId }}"
                         data-context="signin"
                         data-ux_mode="popup"
                         data-callback="handleGoogleSignIn"
                         data-auto_prompt="false">
                    </div>
                    <div class="g_id_signin w-full flex justify-center"
                         data-type="standard"
                         data-shape="rectangular"
                         data-theme="outline"
                         data-text="signin_with"
                         data-size="large"
                         data-logo_alignment="center"
                         data-width="380">
                    </div>
                </div>
            @endif
        </div>

        <!-- Return to Home -->
        <div class="text-center mt-6">
            <a href="{{ url('/') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-zinc-400 hover:text-white transition">
                <span>← العودة إلى الصفحة الرئيسية</span>
            </a>
        </div>
    </div>

    @push('scripts')
    @if($googleClientId)
        <script src="https://accounts.google.com/gsi/client" async defer></script>
        <script>
            function handleGoogleSignIn(response) {
                const errorBox = document.getElementById('google-auth-error');
                const errorText = document.getElementById('google-auth-error-text');
                const loadingBox = document.getElementById('google-auth-loading');

                if (errorBox) {
                    errorBox.classList.add('hidden');
                    if (errorText) errorText.textContent = '';
                }
                if (loadingBox) {
                    loadingBox.classList.remove('hidden');
                    loadingBox.classList.add('flex');
                }

                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const rememberMe = document.getElementById('remember_me')?.checked || false;

                fetch("{{ route('login.google') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                        "X-CSRF-TOKEN": csrfToken || ''
                    },
                    body: JSON.stringify({
                        credential: response.credential,
                        remember: rememberMe
                    })
                })
                .then(async (res) => {
                    const data = await res.json().catch(() => ({}));
                    if (res.ok && data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        if (loadingBox) {
                            loadingBox.classList.add('hidden');
                            loadingBox.classList.remove('flex');
                        }
                        let msg = data.message || 'فشل تسجيل الدخول بواسطة Google.';
                        if (data.errors) {
                            const firstKey = Object.keys(data.errors)[0];
                            if (firstKey && data.errors[firstKey] && data.errors[firstKey][0]) {
                                msg = data.errors[firstKey][0];
                            }
                        }
                        if (errorBox && errorText) {
                            errorText.textContent = msg;
                            errorBox.classList.remove('hidden');
                        } else {
                            alert(msg);
                        }
                    }
                })
                .catch((err) => {
                    if (loadingBox) {
                        loadingBox.classList.add('hidden');
                        loadingBox.classList.remove('flex');
                    }
                    if (errorBox && errorText) {
                        errorText.textContent = 'تعذر الاتصال بالخادم، يرجى التحقق من اتصالك بالإنترنت.';
                        errorBox.classList.remove('hidden');
                    }
                });
            }
        </script>
    @endif
    @endpush
</x-guest-layout>

<x-guest-layout title="تسجيل الدخول - منصة بادر الوطنية">
    <div class="flex-1 grid grid-cols-1 lg:grid-cols-12 min-h-[calc(100vh-0.25rem)]">
        
        <!-- Right Panel: Sovereign Identity & Citizen Guidance (5 Cols on Desktop) -->
        <div class="lg:col-span-5 bg-[#08080a] text-white p-6 sm:p-10 lg:p-14 flex flex-col justify-between relative overflow-hidden border-b lg:border-b-0 lg:border-l border-neutral-800">
            <!-- Architectural Grid Pattern -->
            <div class="absolute inset-0 bg-[linear-gradient(to_right,#18181b_1px,transparent_1px),linear-gradient(to_bottom,#18181b_1px,transparent_1px)] bg-[size:3.5rem_3.5rem] opacity-35 pointer-events-none"></div>

            <!-- Top Brand Bar -->
            <div class="relative z-10 flex items-center justify-between">
                <a href="{{ url('/') }}" class="inline-flex items-center gap-3">
                    @if(setting_url('site_logo'))
                        <img src="{{ setting_url('site_logo') }}" 
                             alt="{{ setting('site_name', 'بــــادر') }}" 
                             class="h-10 sm:h-12 w-auto object-contain shrink-0">
                    @endif
                    <span class="text-xl sm:text-2xl font-black tracking-tight text-white">
                        {{ setting('site_name', 'منصة بــــادر') }}
                    </span>
                </a>

                <a href="{{ url('/') }}" class="text-xs font-bold text-neutral-400 hover:text-white transition">
                    الرئيسية ←
                </a>
            </div>

            <!-- Middle Authoritative Statement (Desktop) -->
            <div class="relative z-10 my-8 lg:my-0 space-y-4">
                <span class="inline-block px-3 py-1 rounded bg-[#121217] border border-neutral-800 text-[11px] font-bold text-[#CE1126]">
                    بوابة النفاذ الموحد للكوادر الحكومية
                </span>
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black text-white leading-snug">
                    إدارة البلاغات والرقابة الميدانية المشتركة.
                </h1>
                <p class="text-xs sm:text-sm text-neutral-400 leading-relaxed max-w-md">
                    منظومة مركزية مخصصة لمشرفي الوزارات، مديري الإدارات الخدمية، والفرق الهندسية لمتابعة ومعالجة بلاغات المواطنين.
                </p>
            </div>

            <!-- Bottom Citizen Guidance Box (Hidden on mobile top to keep form high, shown at bottom on desktop) -->
            <div class="hidden lg:block relative z-10 p-5 rounded-xl bg-[#121217] border border-neutral-800 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-white">هل أنت مواطن وترغب برفع بلاغ؟</span>
                    <a href="{{ route('public.complaints.index') }}" class="text-xs font-bold text-[#CE1126] hover:underline">
                        سجل البلاغات ←
                    </a>
                </div>
                <p class="text-[11px] text-neutral-400 leading-relaxed">
                    لتقديم البلاغات الموثقة بالصور والـ GPS ومتابعة حالتها، يرجى استخدام تطبيق الهاتف الذكي المخصص للمواطنين.
                </p>
                @if(setting_url('app_apk_file'))
                    <div class="pt-1">
                        <a href="{{ setting_url('app_apk_file') }}" download class="inline-flex items-center justify-center w-full py-2.5 px-4 rounded-lg bg-[#08080a] hover:bg-neutral-900 border border-neutral-700 text-xs text-white font-bold transition">
                            تحميل تطبيق بادر للمواطنين (APK)
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Left Panel: Clean High-Contrast Login Form (7 Cols on Desktop) -->
        <div class="lg:col-span-7 bg-white flex flex-col justify-center items-center p-6 sm:p-12 lg:p-16">
            <div class="w-full max-w-md space-y-7">
                
                <!-- Form Header -->
                <div class="border-b border-neutral-200 pb-5">
                    <h2 class="text-2xl font-black text-[#08080a]">تسجيل الدخول للمنظومة</h2>
                    <p class="text-xs text-neutral-500 font-semibold mt-1.5">
                        أدخل بيانات الحساب الوظيفي المعتمد للمتابعة إلى لوحة التحكم
                    </p>
                </div>

                <!-- Validation & Auth Errors Box -->
                <div id="auth-error-box" class="google-auth-error {{ $errors->any() ? '' : 'hidden' }} p-4 rounded-lg bg-[#CE1126]/5 border border-[#CE1126]/30 text-xs font-bold space-y-1">
                    <div class="text-[#CE1126] mb-1">تعذر تسجيل الدخول:</div>
                    <div id="auth-error-content" class="space-y-1">
                        @foreach ($errors->all() as $error)
                            <p class="leading-relaxed text-[#08080a]">{{ $error }}</p>
                        @endforeach
                    </div>
                </div>

                @session('status')
                    <div class="p-4 rounded-lg bg-[#059669]/10 border border-[#059669]/30 text-[#059669] text-xs font-bold">
                        <span>{{ $value }}</span>
                    </div>
                @endsession

                <!-- Login Form -->
                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <!-- Email Input -->
                    <div>
                        <label for="email" class="block text-xs font-bold text-[#08080a] mb-2">
                            البريد الإلكتروني الوظيفي
                        </label>
                        <input id="email" 
                               type="email" 
                               name="email" 
                               value="{{ old('email') }}" 
                               required 
                               autofocus 
                               autocomplete="username" 
                               placeholder="name@ministry.gov.ye"
                               class="w-full px-4 py-3 rounded-lg bg-neutral-50 border border-neutral-300 text-sm text-[#08080a] placeholder-neutral-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#CE1126] focus:border-[#CE1126] transition font-medium">
                    </div>

                    <!-- Password Input -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label for="password" class="block text-xs font-bold text-[#08080a]">
                                كلمة المرور
                            </label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-xs font-bold text-[#CE1126] hover:underline">
                                    نسيت كلمة المرور؟
                                </a>
                            @endif
                        </div>
                        <input id="password" 
                               type="password" 
                               name="password" 
                               required 
                               autocomplete="current-password" 
                               placeholder="••••••••"
                               class="w-full px-4 py-3 rounded-lg bg-neutral-50 border border-neutral-300 text-sm text-[#08080a] placeholder-neutral-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#CE1126] focus:border-[#CE1126] transition font-medium">
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center justify-between pt-1">
                        <label for="remember_me" class="inline-flex items-center gap-2.5 cursor-pointer select-none">
                            <input id="remember_me" type="checkbox" name="remember" 
                                   class="w-4 h-4 rounded border-neutral-300 text-[#CE1126] focus:ring-[#CE1126]">
                            <span class="text-xs font-bold text-neutral-700">تذكر جلسة الدخول على هذا الجهاز</span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-2">
                        <button type="submit" 
                                class="w-full py-3.5 px-6 rounded-lg bg-[#CE1126] hover:bg-[#B70E20] text-white font-bold text-sm transition flex items-center justify-center gap-2">
                            <span>دخول إلى لوحة التحكم</span>
                            <span>←</span>
                        </button>
                    </div>
                </form>

                @php
                    $googleClientId = config('services.google.web_client_id') ?: config('services.google.client_id');
                @endphp

                @if($googleClientId)
                    <div class="relative my-6">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-neutral-200"></div>
                        </div>
                        <div class="relative flex justify-center text-xs">
                            <span class="px-3 bg-white text-neutral-500 font-bold">أو الدخول عبر الحساب الرقمي المربوط</span>
                        </div>
                    </div>

                    <div id="google-auth-loading" class="hidden mb-4 p-3 rounded-lg bg-neutral-100 border border-neutral-300 text-[#08080a] text-xs font-bold items-center justify-center gap-2">
                        <span>جارٍ التحقق من الصلاحيات وتأمين الجلسة...</span>
                    </div>

                    <div class="w-full flex justify-center overflow-hidden">
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
                             data-width="360">
                        </div>
                    </div>
                @endif

                <!-- Mobile Citizen Box (Shown only on mobile below the form) -->
                <div class="lg:hidden pt-6 border-t border-neutral-200 text-center space-y-3">
                    <p class="text-xs font-bold text-[#08080a]">مواطن وترغب بتقديم أو متابعة بلاغ؟</p>
                    <div class="flex flex-col sm:flex-row gap-2.5">
                        <a href="{{ route('public.complaints.index') }}" class="flex-1 py-2.5 px-4 rounded-lg bg-neutral-100 text-[#08080a] text-xs font-bold border border-neutral-200">
                            تصفح سجل البلاغات
                        </a>
                        @if(setting_url('app_apk_file'))
                            <a href="{{ setting_url('app_apk_file') }}" download class="flex-1 py-2.5 px-4 rounded-lg bg-[#08080a] text-white text-xs font-bold">
                                تحميل تطبيق بادر (APK)
                            </a>
                        @endif
                    </div>
                </div>

            </div>
        </div>

    </div>

    @push('scripts')
    @if($googleClientId)
        <script src="https://accounts.google.com/gsi/client" async defer></script>
        <script>
            function handleGoogleSignIn(response) {
                const errorBox = document.getElementById('auth-error-box');
                const errorContent = document.getElementById('auth-error-content');
                const loadingBox = document.getElementById('google-auth-loading');

                if (errorBox) {
                    errorBox.classList.add('hidden');
                    if (errorContent) errorContent.innerHTML = '';
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
                        if (errorBox && errorContent) {
                            errorContent.innerHTML = `<p class="leading-relaxed text-[#08080a]">• ${msg}</p>`;
                            errorBox.classList.remove('hidden');
                            errorBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                        } else {
                            alert(msg);
                        }
                    }
                })
                .catch(() => {
                    if (loadingBox) {
                        loadingBox.classList.add('hidden');
                        loadingBox.classList.remove('flex');
                    }
                    if (errorBox && errorContent) {
                        errorContent.innerHTML = '<p class="leading-relaxed text-[#08080a]">• تعذر الاتصال بالخادم، يرجى التحقق من اتصالك بالإنترنت.</p>';
                        errorBox.classList.remove('hidden');
                        errorBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }
                });
            }
        </script>
    @endif
    @endpush
</x-guest-layout>
<x-public-layout title="منصة بادر - البوابة الوطنية الموحدة للبلاغات والشكاوى التنموية">
    <!-- Hero Section (Full Viewport Fit & Near-Black Obsidian Theme) -->
    <section class="relative overflow-hidden bg-gradient-to-b from-black via-[#08080a] to-black text-white min-h-screen flex flex-col justify-center pt-28 pb-8 sm:pt-32 sm:pb-12 border-b border-zinc-800/80">
        <!-- Subtle Glow & Pattern -->
        <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#ce1126_1px,transparent_1px)] [background-size:20px_20px] pointer-events-none"></div>
        <div class="absolute -top-40 right-1/4 w-96 h-96 bg-red-600/15 rounded-full blur-3xl pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center w-full my-auto">
            <!-- Main Heading -->
            <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black tracking-tight leading-tight max-w-4xl mx-auto text-white">
                صوت المواطن أمانة،
                <span class="block text-transparent bg-clip-text bg-gradient-to-r from-red-500 via-white to-red-400 pb-5 mt-1 sm:mt-2">
                    والرقابة حق وشراكة
                </span>
            </h1>

            <!-- Subtitle -->
            <p class="mt-4 sm:mt-5 text-sm sm:text-base lg:text-lg text-zinc-300 max-w-2xl mx-auto leading-relaxed font-normal">
                منصة رقمية مركزية لربط المواطن بالوزارات والجهات الخدمية، معالجة الشكاوى بذكاء ميداني، ومتابعة المشاريع التنموية بشفافية وطنية متكاملة.
            </p>

            <!-- Action Buttons -->
            <div class="mt-6 sm:mt-8 flex flex-col sm:flex-row items-center justify-center gap-3 sm:gap-4 max-w-md sm:max-w-none mx-auto">
                <a href="{{ route('public.complaints.index') }}" 
                   class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 sm:px-8 py-3 sm:py-3.5 rounded-2xl text-sm sm:text-base font-bold bg-red-600 hover:bg-red-700 text-white shadow-lg shadow-red-600/20 hover:scale-105 transition transform duration-150">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>سجل البلاغات</span>
                </a>

                <a href="{{ route('public.projects.index') }}" 
                   class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 sm:px-8 py-3 sm:py-3.5 rounded-2xl text-sm sm:text-base font-bold bg-zinc-900/90 hover:bg-zinc-800 text-white border border-zinc-700/80 hover:scale-105 transition transform duration-150">
                    <svg class="w-5 h-5 text-zinc-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <span>المشاريع الوطنية</span>
                </a>
            </div>

            <!-- KPI Stats Ribbon (Fully Visible & Sized to Fit Viewport) -->
            <div class="mt-8 sm:mt-12 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 max-w-5xl mx-auto">
                <div class="bg-[#0d0d11]/85 backdrop-blur-md border border-zinc-800/80 hover:border-zinc-700 rounded-2xl p-4 sm:p-5 shadow-sm text-right transition">
                    <p class="text-xs font-bold text-zinc-400 mb-1">إجمالي البلاغات المسجلة</p>
                    <p class="text-2xl sm:text-3xl font-black text-white">{{ number_format($stats['total_complaints']) }}</p>
                    <span class="text-[11px] text-emerald-400 font-semibold mt-1 inline-block">موثقة بالكامل</span>
                </div>

                <div class="bg-[#0d0d11]/85 backdrop-blur-md border border-zinc-800/80 hover:border-zinc-700 rounded-2xl p-4 sm:p-5 shadow-sm text-right transition">
                    <p class="text-xs font-bold text-zinc-400 mb-1">نسبة الإنجاز والمعالجة</p>
                    <p class="text-2xl sm:text-3xl font-black text-red-500">{{ $stats['resolution_rate'] }}%</p>
                    <span class="text-[11px] text-zinc-400 font-semibold mt-1 inline-block">{{ number_format($stats['resolved_complaints']) }} بلاغ منجز</span>
                </div>

                <div class="bg-[#0d0d11]/85 backdrop-blur-md border border-zinc-800/80 hover:border-zinc-700 rounded-2xl p-4 sm:p-5 shadow-sm text-right transition">
                    <p class="text-xs font-bold text-zinc-400 mb-1">المواطنين المشاركين</p>
                    <p class="text-2xl sm:text-3xl font-black text-white">{{ number_format($stats['total_citizens']) }}</p>
                    <span class="text-[11px] text-zinc-400 font-semibold mt-1 inline-block">رقابة مجتمعية نشطة</span>
                </div>

                <div class="bg-[#0d0d11]/85 backdrop-blur-md border border-zinc-800/80 hover:border-zinc-700 rounded-2xl p-4 sm:p-5 shadow-sm text-right transition">
                    <p class="text-xs font-bold text-zinc-400 mb-1">مشاريع تنموية تحت المتابعة</p>
                    <p class="text-2xl sm:text-3xl font-black text-white">{{ number_format($stats['total_projects']) }}</p>
                    <span class="text-[11px] text-emerald-400 font-semibold mt-1 inline-block">{{ number_format($stats['completed_projects']) }} مكتملة</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Platform Pillars -->
    <section id="about" class="py-20 bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <h2 class="text-xs font-bold text-red-600 tracking-wider uppercase mb-2">ركائز المنظومة الوطنية</h2>
                <h3 class="text-3xl font-black text-slate-900">كيف تعمل منصة بــــادر؟</h3>
                <p class="text-sm text-slate-600 mt-3 leading-relaxed">
                    منظومة متكاملة تربط المواطن في الميدان بالكوادر الحكومية المشرفة والمهندسين المنفذين بآلية ذكية وسريعة.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Pillar 1 -->
                <div class="p-8 rounded-3xl bg-slate-50 border border-slate-200/80 hover:shadow-lg transition">
                    <div class="w-14 h-14 rounded-2xl bg-red-100 text-red-600 flex items-center justify-center mb-6 font-black">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <h4 class="text-lg font-bold text-slate-900 mb-2">رصد ميداني بالـ GPS والصور</h4>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        تسجيل البلاغ مباشرة من موقع الحدث مع دعم العمل بدون إنترنت والمزامنة الفورية عند توفر الشبكة.
                    </p>
                </div>

                <!-- Pillar 2 -->
                <div class="p-8 rounded-3xl bg-slate-50 border border-slate-200/80 hover:shadow-lg transition">
                    <div class="w-14 h-14 rounded-2xl bg-black text-white flex items-center justify-center mb-6 font-black border border-zinc-800">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <h4 class="text-lg font-bold text-slate-900 mb-2">توجيه ذكي وتكليف فوري</h4>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        توجيه الشكوى آلياً إلى الوزارة المختصة، وتكليف فرق الصيانة الميدانية بمهام واضحة ومحددة بمدد زمنية (SLA).
                    </p>
                </div>

                <!-- Pillar 3 -->
                <div class="p-8 rounded-3xl bg-slate-50 border border-slate-200/80 hover:shadow-lg transition">
                    <div class="w-14 h-14 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center mb-6 font-black">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h4 class="text-lg font-bold text-slate-900 mb-2">شفافية وإثبات الإنجاز</h4>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        توثيق مرحلة المعالجة بالأدلة المصورة (قبل وبعد)، وتحديث حالة البلاغ أمام المواطن والجهات العليا بلحظتها.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Public Transparency Feed (Recent Resolved) -->
    <section class="py-20 bg-slate-50 border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row items-start sm:items-end justify-between mb-12 gap-4">
                <div>
                    <h2 class="text-xs font-bold text-red-600 tracking-wider uppercase mb-1">الشفافية العامة</h2>
                    <h3 class="text-2xl sm:text-3xl font-black text-slate-900">أحدث البلاغات التي تم حلها بنجاح</h3>
                    <p class="text-sm text-slate-500 mt-1">عرض عام يوثق كفاءة استجابة الجهات الحكومية لخدمة المواطن</p>
                </div>
                <a href="{{ route('public.complaints.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-red-600 hover:text-red-700">
                    <span>عرض كافة البلاغات المفتوحة</span>
                    <svg class="w-4 h-4 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($recentResolved as $item)
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs hover:shadow-md transition p-6 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-xs font-mono font-bold text-slate-400">#{{ $item->complaint_number }}</span>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    تم الإنجاز
                                </span>
                            </div>
                            <h4 class="text-base font-bold text-slate-900 mb-2 line-clamp-1">{{ $item->title }}</h4>
                            <p class="text-xs text-slate-500 line-clamp-2 leading-relaxed mb-4">{{ $item->description }}</p>
                        </div>

                        <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs">
                            <span class="font-semibold text-slate-700">{{ $item->currentDepartment?->name ?? 'جهة حكومية' }}</span>
                            <a href="{{ route('public.complaints.show', $item->id) }}" class="font-bold text-red-600 hover:text-red-700">
                                تفاصيل البلاغ ←
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-3 text-center py-12 text-slate-400">
                        لا توجد بلاغات معالجة منشورة حالياً.
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- Featured Projects -->
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row items-start sm:items-end justify-between mb-12 gap-4">
                <div>
                    <h2 class="text-xs font-bold text-red-600 tracking-wider uppercase mb-1">المشاريع التنموية</h2>
                    <h3 class="text-2xl sm:text-3xl font-black text-slate-900">مشاريع تحظى برقابة ومتابعة مجتمعية</h3>
                    <p class="text-sm text-slate-500 mt-1">متابعة مسار الإنجاز ونسب التنفيذ للمشاريع التنموية</p>
                </div>
                <a href="{{ route('public.projects.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-red-600 hover:text-red-700">
                    <span>جميع المشاريع الوطنية</span>
                    <svg class="w-4 h-4 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @forelse($featuredProjects as $proj)
                    <div class="bg-slate-50 rounded-3xl border border-slate-200 overflow-hidden hover:shadow-lg transition flex flex-col">
                        <div class="p-6 flex-1">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-xs font-bold px-2.5 py-1 rounded-lg {{ $proj->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $proj->status === 'completed' ? 'مكتمل' : 'قيد التنفيذ' }}
                                </span>
                                <span class="text-xs font-mono font-bold text-slate-400">#PRJ-{{ $proj->id }}</span>
                            </div>
                            <h4 class="text-lg font-bold text-slate-900 mb-2">{{ $proj->name }}</h4>
                            <p class="text-xs text-slate-600 line-clamp-3 leading-relaxed mb-6">{{ $proj->description }}</p>

                            <!-- Progress Bar -->
                            <div class="space-y-1.5">
                                <div class="flex justify-between text-xs font-bold">
                                    <span class="text-slate-600">نسبة التنفيذ</span>
                                    <span class="text-red-600">{{ $proj->progress_percentage ?? 0 }}%</span>
                                </div>
                                <div class="w-full h-2 bg-slate-200 rounded-full overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-red-600 to-amber-500 rounded-full" style="width: {{ $proj->progress_percentage ?? 0 }}%"></div>
                                </div>
                            </div>
                        </div>

                        <div class="p-4 bg-white border-t border-slate-200 flex items-center justify-between text-xs">
                            <span class="text-slate-500 font-medium">المستفيدين: {{ number_format($proj->beneficiaries_count ?? 0) }}</span>
                            <a href="{{ route('public.projects.show', $proj->id) }}" class="font-bold text-red-600 hover:text-red-700">
                                استعراض المشروع ←
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-3 text-center py-12 text-slate-400">
                        لا توجد مشاريع مضافة حالياً.
                    </div>
                @endforelse
            </div>
        </div>
    </section>
</x-public-layout>

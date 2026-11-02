<x-public-layout title="منصة بادر - البوابة الوطنية الموحدة للبلاغات والشكاوى التنموية">
    <!-- Institutional Split Hero Section (Refined Calibrated Light Tone & Full Viewport Height) -->
    <section class="relative bg-[#fafafa] text-neutral-900 border-b border-neutral-300/90 overflow-hidden min-h-[calc(100dvh-4.25rem)] lg:min-h-[calc(100vh-5rem)] flex items-center">
        <!-- Structural Grid Lines (Crisp High-Clarity Blueprint Grid) -->
        <div class="absolute inset-0 bg-[linear-gradient(to_right,#8e9cae_1px,transparent_1px),linear-gradient(to_bottom,#8e9cae_1px,transparent_1.25px)] bg-[size:2.5rem_2.5rem] sm:bg-[size:3.5rem_3.5rem] opacity-15 pointer-events-none"></div>

        <!-- Mobile Hero Presentation (Exact 100% Viewport Height, Centered & Clean matching reference image) -->
        <div class="lg:hidden flex flex-col justify-between items-center text-center min-h-[calc(100dvh-4.25rem)] py-8 px-5 relative z-10 w-full">
            <!-- Top Spacer for Optimal Optical Centering -->
            <div class="w-full h-2"></div>

            <div class="flex flex-col justify-center items-center max-w-sm mx-auto my-auto w-full">
                <!-- Eyebrow Badge (Subtle & Elegant) -->
                <div class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-white border border-neutral-300/90 text-[11px] font-bold text-[#CE1126] shadow-sm mb-5">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#CE1126] animate-pulse"></span>
                    <span>منصة بادر الوطنية</span>
                </div>

                <!-- Main Mobile Title (Centered & Focused) -->
                <h1 class="text-3xl font-black text-neutral-900 tracking-tight leading-[1.3] max-w-xs mx-auto mb-3.5">
                    صوت المواطن أمانة
                    <span class="block text-neutral-700 font-extrabold mt-1 text-2xl">والرقابة حق وشراكة</span>
                </h1>

                <!-- Summarized Text (Concise & Easy to Read) -->
                <p class="text-xs sm:text-sm text-neutral-600 leading-relaxed max-w-xs mx-auto mb-8 font-medium">
                    منصة رقمية وطنية لربط المواطن بالجهات الخدمية ومعالجة الشكاوى ومتابعة المشاريع بشفافية متكاملة.
                </p>

                <!-- Action Button (Pill button like the reference image) -->
                <a href="{{ route('public.complaints.index') }}" 
                   class="inline-flex items-center justify-center gap-2 px-8 py-3.5 rounded-lg text-xs sm:text-sm font-bold bg-[#CE1126] hover:bg-[#B70E20] text-white shadow-xl shadow-red-600/25 active:scale-95 transition">
                    <span>تصفح سجل البلاغات</span>
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </a>
            </div>

            <!-- Scroll Indicator at Bottom Edge (matching the reference image) -->
            <div class="pt-4 pb-2 flex flex-col items-center gap-1.5 opacity-60">
                <span class="text-[9px] tracking-widest text-neutral-600 font-mono font-bold uppercase">SCROLL</span>
                <div class="w-px h-8 bg-neutral-500"></div>
            </div>
        </div>

        <!-- Desktop Hero Presentation (Institutional Split Layout in Calibrated Light Tone) -->
        <div class="hidden lg:block max-w-7xl mx-auto px-6 lg:px-8 py-14 lg:py-20 relative z-10 w-full">
            <div class="grid grid-cols-12 gap-10 xl:gap-14 items-center">
                
                <!-- Right Column: Statement & Actions (7 Cols) -->
                <div class="col-span-7 space-y-5 text-right">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/90 border border-neutral-300 text-xs font-bold text-[#CE1126] shadow-sm">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#CE1126]"></span>
                        <span>نظام الرقابة الميدانية والاستجابة الحكومية</span>
                    </div>

                    <h1 class="text-4xl lg:text-[44px] font-black tracking-tight text-neutral-900 leading-[1.3]">
                        صوت المواطن أمانة،
                        <span class="block text-neutral-600 mt-2">
                            والرقابة حق وشراكة.
                        </span>
                    </h1>

                    <p class="text-sm sm:text-base text-neutral-600 max-w-xl leading-relaxed">
                        منصة رقمية مركزية لربط المواطن بالوزارات والجهات الخدمية، معالجة الشكاوى بذكاء ميداني، ومتابعة المشاريع التنموية بشفافية وطنية متكاملة.
                    </p>

                    <!-- Action Buttons -->
                    <div class="pt-2 flex items-center gap-3.5">
                        <a href="{{ route('public.complaints.index') }}" 
                           class="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl text-sm font-bold bg-[#CE1126] hover:bg-[#B70E20] text-white shadow-md shadow-red-600/20 active:scale-95 transition">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span>تصفح سجل البلاغات العام</span>
                        </a>

                        <a href="{{ route('public.projects.index') }}" 
                           class="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl text-sm font-bold bg-white hover:bg-neutral-50 text-neutral-800 border border-neutral-300 shadow-sm transition">
                            <svg class="w-4 h-4 text-neutral-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            <span>مراقبة المشاريع التنموية</span>
                        </a>
                    </div>

                    <!-- Technical Standards Footnote -->
                    <div class="grid pt-5 border-t border-neutral-300/80 grid-cols-3 gap-6 max-w-xl text-xs">
                        <div class="border-r-2 border-[#CE1126] pr-3">
                            <span class="font-bold text-neutral-900 block">توثيق مكاني GPS</span>
                            <span class="text-neutral-500 mt-0.5 block">إحداثيات دقيقة ومؤمنة</span>
                        </div>
                        <div class="border-r-2 border-neutral-300 pr-3">
                            <span class="font-bold text-neutral-900 block">ربط وزاري مباشر</span>
                            <span class="text-neutral-500 mt-0.5 block">توجيه آلي حسب الاختصاص</span>
                        </div>
                        <div class="border-r-2 border-neutral-300 pr-3">
                            <span class="font-bold text-neutral-900 block">حماية الخصوصية</span>
                            <span class="text-neutral-500 mt-0.5 block">سرية تامة لبيانات المبلغين</span>
                        </div>
                    </div>
                </div>

                <!-- Left Column: National Operations Metrics Matrix (5 Cols) -->
                <div class="col-span-5">
                    <div class="bg-white border border-neutral-300/80 rounded-2xl overflow-hidden shadow-xl shadow-neutral-900/5">
                        <!-- Matrix Header -->
                        <div class="px-5 py-3.5 bg-neutral-50/90 border-b border-neutral-200 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-[#CE1126]"></span>
                                <span class="text-xs font-bold text-neutral-900">مؤشرات الأداء والإنجاز الوطني</span>
                            </div>
                            <span class="text-[11px] text-[#CE1126] font-extrabold bg-red-50 border border-red-100 px-2.5 py-0.5 rounded-full">إحصائيات المنصة</span>
                        </div>

                        <!-- 2x2 Metrics Grid -->
                        <div class="grid grid-cols-2 divide-x divide-y divide-x-reverse divide-neutral-200">
                            <!-- Stat 1 -->
                            <div class="p-5 sm:p-6 bg-white hover:bg-neutral-50/60 transition">
                                <span class="text-xs font-bold text-neutral-500 block mb-1">إجمالي البلاغات</span>
                                <span class="text-2xl sm:text-3xl font-black text-neutral-900 font-mono tracking-tight block">
                                    {{ number_format($stats['total_complaints']) }}
                                </span>
                                <span class="inline-block mt-1 text-[11px] text-[#059669] font-bold">
                                    موثقة ومعتمدة
                                </span>
                            </div>

                            <!-- Stat 2 -->
                            <div class="p-5 sm:p-6 bg-white hover:bg-neutral-50/60 transition">
                                <span class="text-xs font-bold text-neutral-500 block mb-1">معدل المعالجة</span>
                                <span class="text-2xl sm:text-3xl font-black text-[#CE1126] font-mono tracking-tight block">
                                    {{ $stats['resolution_rate'] }}%
                                </span>
                                <span class="inline-block mt-1 text-[11px] text-neutral-500 font-semibold">
                                    {{ number_format($stats['resolved_complaints']) }} بلاغ منجز
                                </span>
                            </div>

                            <!-- Stat 3 -->
                            <div class="p-5 sm:p-6 bg-white hover:bg-neutral-50/60 transition">
                                <span class="text-xs font-bold text-neutral-500 block mb-1">المواطنون المسجلون</span>
                                <span class="text-2xl sm:text-3xl font-black text-neutral-900 font-mono tracking-tight block">
                                    {{ number_format($stats['total_citizens']) }}
                                </span>
                                <span class="inline-block mt-1 text-[11px] text-neutral-500 font-semibold">
                                    مشاركة مجتمعية
                                </span>
                            </div>

                            <!-- Stat 4 -->
                            <div class="p-5 sm:p-6 bg-white hover:bg-neutral-50/60 transition">
                                <span class="text-xs font-bold text-neutral-500 block mb-1">المشاريع التنموية</span>
                                <span class="text-2xl sm:text-3xl font-black text-neutral-900 font-mono tracking-tight block">
                                    {{ number_format($stats['total_projects']) }}
                                </span>
                                <span class="inline-block mt-1 text-[11px] text-[#059669] font-bold">
                                    {{ number_format($stats['completed_projects']) }} مشروع مكتمل
                                </span>
                            </div>
                        </div>

                        <!-- Matrix Footer Progress -->
                        <div class="px-5 py-3.5 bg-neutral-50/90 border-t border-neutral-200">
                            <div class="flex items-center justify-between text-[11px] font-bold text-neutral-600 mb-2">
                                <span>كفاءة الاستجابة الميدانية العامة</span>
                                <span class="font-mono text-neutral-900 font-black">{{ $stats['resolution_rate'] }}%</span>
                            </div>
                            <div class="w-full h-2 bg-neutral-200 rounded-full overflow-hidden">
                                <div class="h-full bg-[#CE1126] rounded-full" style="width: {{ min(100, max(0, $stats['resolution_rate'])) }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Institutional Workflow Pillars -->
    <section id="about" class="py-12 sm:py-16 bg-white border-b border-neutral-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 sm:mb-12 pb-5 border-b border-neutral-200 gap-3">
                <div>
                    <span class="text-xs font-bold text-[#CE1126] uppercase tracking-wider block mb-1">آلية العمل المؤسسي</span>
                    <h2 class="text-xl sm:text-3xl font-black text-[#08080a]">دورة معالجة البلاغات والرقابة الميدانية</h2>
                </div>
                <p class="text-xs sm:text-sm text-neutral-600 max-w-md leading-relaxed">
                    منظومة حوكمة إلكترونية تضبط مسار البلاغ منذ لحظة رصده في الميدان وحتى إغلاقه بمحاضر الإنجاز المصورة.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 sm:gap-6">
                <!-- Step 01 -->
                <div class="p-6 rounded-xl bg-neutral-50 border border-neutral-200 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-5">
                            <span class="text-xs font-mono font-black px-2.5 py-1 rounded bg-[#08080a] text-white">المرحلة 01</span>
                            <div class="w-10 h-10 rounded-lg bg-white border border-neutral-200 flex items-center justify-center text-[#CE1126]">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </div>
                        </div>
                        <h3 class="text-base font-black text-[#08080a] mb-2">التوثيق الميداني والرفع الجغرافي</h3>
                        <p class="text-xs text-neutral-600 leading-relaxed">
                            التقاط المشكلة عبر الكاميرا المباشرة وتثبيت إحداثيات الموقع (GPS) لمنع البلاغات الوهمية وضمان دقة الوصول.
                        </p>
                    </div>
                    <div class="mt-6 pt-4 border-t border-neutral-200 flex items-center justify-between text-[11px] font-bold text-neutral-500">
                        <span>المصدر: تطبيق المواطن</span>
                        <span class="text-[#08080a]">تشفير الإحداثيات</span>
                    </div>
                </div>

                <!-- Step 02 -->
                <div class="p-6 rounded-xl bg-neutral-50 border border-neutral-200 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-5">
                            <span class="text-xs font-mono font-black px-2.5 py-1 rounded bg-[#08080a] text-white">المرحلة 02</span>
                            <div class="w-10 h-10 rounded-lg bg-white border border-neutral-200 flex items-center justify-center text-[#08080a]">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                            </div>
                        </div>
                        <h3 class="text-base font-black text-[#08080a] mb-2">الفرز الآلي وتكليف الفرق</h3>
                        <p class="text-xs text-neutral-600 leading-relaxed">
                            توجيه البلاغ فوراً إلى الوزارة والإدارة المختصة، وإسناد المهمة للمهندس أو الفريق الميداني وفق معايير الأولوية.
                        </p>
                    </div>
                    <div class="mt-6 pt-4 border-t border-neutral-200 flex items-center justify-between text-[11px] font-bold text-neutral-500">
                        <span>المسؤول: غرفة العمليات</span>
                        <span class="text-[#08080a]">متابعة SLA</span>
                    </div>
                </div>

                <!-- Step 03 -->
                <div class="p-6 rounded-xl bg-neutral-50 border border-neutral-200 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-5">
                            <span class="text-xs font-mono font-black px-2.5 py-1 rounded bg-[#CE1126] text-white">المرحلة 03</span>
                            <div class="w-10 h-10 rounded-lg bg-white border border-neutral-200 flex items-center justify-center text-[#059669]">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                        </div>
                        <h3 class="text-base font-black text-[#08080a] mb-2">المعالجة وإثبات الإنجاز المصور</h3>
                        <p class="text-xs text-neutral-600 leading-relaxed">
                            تنفيذ المعالجة الميدانية ورفع صور ما بعد الإصلاح، ليتم اعتمادها ونشرها في سجل الشفافية العام أمام المواطنين.
                        </p>
                    </div>
                    <div class="mt-6 pt-4 border-t border-neutral-200 flex items-center justify-between text-[11px] font-bold text-neutral-500">
                        <span>النتيجة: إغلاق موثق</span>
                        <span class="text-[#059669]">شفافية كاملة</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Public Transparency Feed (Recent Resolved Complaints) -->
    <section class="py-12 sm:py-16 bg-neutral-50 border-b border-neutral-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row items-start sm:items-end justify-between mb-8 sm:mb-10 gap-3">
                <div>
                    <span class="text-xs font-bold text-[#CE1126] uppercase tracking-wider block mb-1">سجل الشفافية الحكومية</span>
                    <h2 class="text-xl sm:text-3xl font-black text-[#08080a]">أحدث البلاغات المعالجة</h2>
                </div>
                <a href="{{ route('public.complaints.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-white border border-neutral-200 text-xs font-bold text-[#08080a] hover:bg-neutral-100 transition">
                    <span>استعراض السجل الكامل</span>
                    <span>←</span>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6">
                @forelse($recentResolved as $item)
                    <article class="bg-white rounded-xl border border-neutral-200 hover:border-neutral-300 transition flex flex-col justify-between overflow-hidden">
                        <div class="p-5 sm:p-6">
                            <!-- Card Top Meta -->
                            <div class="flex items-center justify-between gap-2 pb-3 mb-4 border-b border-neutral-100">
                                <span class="text-xs font-mono font-bold text-[#08080a] bg-neutral-100 px-2.5 py-1 rounded">
                                    #{{ $item->complaint_number }}
                                </span>
                                <span class="px-2.5 py-1 rounded text-xs font-bold bg-[#059669]/10 text-[#059669]">
                                    تم الإنجاز
                                </span>
                            </div>

                            <h3 class="text-base font-black text-[#08080a] mb-2 line-clamp-1">
                                {{ $item->title }}
                            </h3>
                            <p class="text-xs text-neutral-600 line-clamp-2 leading-relaxed">
                                {{ $item->description }}
                            </p>
                        </div>

                        <div class="px-5 sm:px-6 py-3.5 bg-neutral-50 border-t border-neutral-200/80 flex items-center justify-between text-xs">
                            <span class="font-bold text-neutral-700 truncate max-w-[65%]">
                                {{ $item->currentDepartment?->name ?? 'الجهة الحكومية المختصة' }}
                            </span>
                            <a href="{{ route('public.complaints.show', $item->id) }}" class="font-bold text-[#CE1126] hover:text-[#B70E20] shrink-0">
                                عرض الملف ←
                            </a>
                        </div>
                    </article>
                @empty
                    <div class="col-span-full bg-white rounded-xl border border-neutral-200 p-10 text-center">
                        <p class="text-sm font-bold text-neutral-500">لا توجد بلاغات معالجة منشورة في السجل حالياً.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- Featured National Projects -->
    <section class="py-12 sm:py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row items-start sm:items-end justify-between mb-8 sm:mb-10 gap-3">
                <div>
                    <span class="text-xs font-bold text-[#CE1126] uppercase tracking-wider block mb-1">الرقابة التنموية</span>
                    <h2 class="text-xl sm:text-3xl font-black text-[#08080a]">مشاريع خدمية تحت المتابعة المجتمعية</h2>
                </div>
                <a href="{{ route('public.projects.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-neutral-100 border border-neutral-200 text-xs font-bold text-[#08080a] hover:bg-neutral-200 transition">
                    <span>دليل المشاريع التنموية</span>
                    <span>←</span>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 sm:gap-6">
                @forelse($featuredProjects as $proj)
                    <article class="bg-white rounded-xl border border-neutral-200 hover:border-neutral-300 transition flex flex-col justify-between overflow-hidden">
                        <div class="p-5 sm:p-6 flex-1 flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between gap-2 pb-3 mb-4 border-b border-neutral-100">
                                    <span class="text-xs font-mono font-bold text-[#08080a] bg-neutral-100 px-2.5 py-1 rounded">
                                        #PRJ-{{ $proj->id }}
                                    </span>
                                    <span class="px-2.5 py-1 rounded text-xs font-bold {{ $proj->status === 'completed' ? 'bg-[#059669]/10 text-[#059669]' : 'bg-[#D97706]/10 text-[#D97706]' }}">
                                        {{ $proj->status === 'completed' ? 'مكتمل' : 'قيد التنفيذ' }}
                                    </span>
                                </div>

                                <h3 class="text-base font-black text-[#08080a] mb-2 line-clamp-1">{{ $proj->name }}</h3>
                                <p class="text-xs text-neutral-600 line-clamp-3 leading-relaxed mb-5">{{ $proj->description }}</p>
                            </div>

                            <!-- Progress Indicator -->
                            <div class="p-3.5 rounded-lg bg-neutral-50 border border-neutral-200 space-y-2">
                                <div class="flex justify-between items-center text-xs font-bold">
                                    <span class="text-neutral-600">نسبة الإنجاز المعتمدة</span>
                                    <span class="font-mono text-[#08080a]">{{ $proj->progress_percentage ?? 0 }}%</span>
                                </div>
                                <div class="w-full h-2 bg-neutral-200 rounded-full overflow-hidden">
                                    <div class="h-full {{ ($proj->progress_percentage ?? 0) >= 100 ? 'bg-[#059669]' : 'bg-[#CE1126]' }} rounded-full" style="width: {{ min(100, max(0, $proj->progress_percentage ?? 0)) }}%"></div>
                                </div>
                            </div>
                        </div>

                        <div class="px-5 sm:px-6 py-3.5 bg-neutral-50 border-t border-neutral-200/80 flex items-center justify-between text-xs">
                            <span class="text-neutral-600 font-semibold">
                                المستفيدون: <strong class="text-[#08080a] font-mono">{{ number_format($proj->beneficiaries_count ?? 0) }}</strong>
                            </span>
                            <a href="{{ route('public.projects.show', $proj->id) }}" class="font-bold text-[#CE1126] hover:text-[#B70E20]">
                                تفاصيل المشروع ←
                            </a>
                        </div>
                    </article>
                @empty
                    <div class="col-span-full bg-neutral-50 rounded-xl border border-neutral-200 p-10 text-center">
                        <p class="text-sm font-bold text-neutral-500">لا توجد مشاريع تنموية مضافة حالياً.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>
</x-public-layout>
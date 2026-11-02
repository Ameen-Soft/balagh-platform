<x-public-layout title="{{ $project->name }} - المشاريع التنموية - منصة بادر">
    <!-- Institutional Project Header -->
    <div class="bg-[#09090b] text-white py-10 border-b border-zinc-800">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="flex items-center gap-2 text-xs text-zinc-400 mb-4 font-semibold">
                <a href="{{ url('/') }}" class="hover:text-white transition">الرئيسية</a>
                <span>/</span>
                <a href="{{ route('public.projects.index') }}" class="hover:text-white transition">المشاريع التنموية</a>
                <span>/</span>
                <span class="text-zinc-200 font-mono">#PRJ-{{ $project->id }}</span>
            </nav>

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2.5 mb-2">
                        <span class="text-xs font-mono font-bold text-white bg-zinc-900 px-2.5 py-1 rounded border border-zinc-700">
                            #PRJ-{{ $project->id }}
                        </span>
                        <span class="text-xs font-bold text-zinc-400">مشروع تنموي معتمد</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-black text-white leading-snug">{{ $project->name }}</h1>
                </div>

                <div class="shrink-0">
                    <span class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-bold {{ $project->status === 'completed' ? 'bg-emerald-950/90 text-emerald-400 border border-emerald-800' : 'bg-amber-950/90 text-amber-400 border border-amber-800' }}">
                        <span class="w-2 h-2 rounded-full {{ $project->status === 'completed' ? 'bg-emerald-400' : 'bg-amber-400' }}"></span>
                        {{ $project->status === 'completed' ? 'مشروع مكتمل' : 'قيد التنفيذ والمتابعة' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <!-- Main Project Details (8 Cols) -->
            <div class="lg:col-span-8 space-y-6">
                <!-- Executive Progress Indicator Card -->
                <div class="bg-white rounded-xl border border-slate-200 p-6">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <h2 class="text-sm font-black text-slate-900">مؤشر التقدم ونسبة الإنجاز الميداني</h2>
                            <p class="text-xs text-slate-500 mt-0.5">نسبة التنفيذ المعتمدة من الفرق الهندسية المشرفة</p>
                        </div>
                        <span class="text-2xl font-black font-mono text-[#CE1126]">{{ $project->progress_percentage ?? 0 }}%</span>
                    </div>
                    <div class="w-full h-3 bg-slate-100 rounded-full overflow-hidden border border-slate-200">
                        <div class="h-full {{ ($project->progress_percentage ?? 0) >= 100 ? 'bg-[#059669]' : 'bg-[#CE1126]' }} rounded-full transition-all duration-500" style="width: {{ min(100, max(0, $project->progress_percentage ?? 0)) }}%"></div>
                    </div>
                </div>

                <!-- Project Description -->
                <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                    <div class="px-6 py-4 bg-slate-50 border-b border-slate-200">
                        <h2 class="text-sm font-black text-slate-900">وصف المشروع وأهدافه التنموية</h2>
                    </div>
                    <div class="p-6">
                        <p class="text-sm text-slate-700 leading-relaxed whitespace-pre-line">{{ $project->description }}</p>
                    </div>
                </div>

                <!-- Community Contributions / Feedback -->
                <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                    <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                        <h2 class="text-sm font-black text-slate-900">سجل الرقابة والمشاركات المجتمعية</h2>
                        <span class="text-xs font-mono font-bold text-slate-600">{{ $project->contributions->count() }} مشاركة</span>
                    </div>
                    <div class="p-6 space-y-3">
                        @forelse($project->contributions as $contrib)
                            <div class="p-4 rounded-lg bg-[#F8FAFC] border border-slate-200/80">
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="text-xs font-black text-slate-900">مساهمة رقابية من مواطن</span>
                                    <span class="text-[11px] font-mono text-slate-500">{{ $contrib->created_at->translatedFormat('d F Y') }}</span>
                                </div>
                                <p class="text-xs text-slate-600 leading-relaxed">{{ $contrib->comment }}</p>
                            </div>
                        @empty
                            <p class="text-xs font-semibold text-slate-500 py-2">لا توجد ملاحظات أو مشاركات مجتمعية مسجلة على هذا المشروع حتى الآن.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Sidebar Project Specifications (4 Cols) -->
            <div class="lg:col-span-4 space-y-5">
                <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                    <div class="px-5 py-4 bg-slate-900 text-white">
                        <h3 class="text-xs font-bold uppercase tracking-wider">البيانات الأساسية للمشروع</h3>
                    </div>

                    <div class="divide-y divide-slate-200 text-xs">
                        <div class="p-4 flex flex-col gap-1">
                            <span class="text-slate-500 font-semibold">الميزانية التقديرية المرصودة</span>
                            <span class="font-black text-slate-900 text-sm font-mono">
                                {{ $project->budget ? number_format($project->budget) . ' ريال يمني' : 'غير محددة' }}
                            </span>
                        </div>

                        <div class="p-4 flex flex-col gap-1">
                            <span class="text-slate-500 font-semibold">عدد المستفيدين المقدر</span>
                            <span class="font-black text-slate-900 text-sm font-mono">{{ number_format($project->beneficiaries_count ?? 0) }} مواطن</span>
                        </div>

                        <div class="p-4 flex flex-col gap-1">
                            <span class="text-slate-500 font-semibold">تاريخ الإدراج والإطلاق</span>
                            <span class="font-bold text-slate-900 font-mono">{{ $project->created_at->translatedFormat('d F Y') }}</span>
                        </div>

                        <div class="p-4 flex flex-col gap-1">
                            <span class="text-slate-500 font-semibold">النطاق الجغرافي</span>
                            <span class="font-bold text-slate-900">{{ $project->governorate ?? 'الجمهورية اليمنية' }}</span>
                        </div>
                    </div>
                </div>

                <a href="{{ route('public.projects.index') }}" 
                   class="flex items-center justify-center gap-2 w-full py-3 px-4 rounded-lg bg-white border border-slate-300 text-slate-900 text-xs font-bold hover:bg-slate-100 transition">
                    <span>← العودة لدليل المشاريع</span>
                </a>
            </div>

        </div>
    </div>
</x-public-layout>
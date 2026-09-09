<x-public-layout title="{{ $project->name }} - المشاريع التنموية - منصة بادر">
    <div class="bg-gradient-to-b from-black via-[#08080a] to-[#0d0d11] text-white pt-28 pb-12 border-b border-zinc-800/80">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-mono font-bold text-red-400 bg-red-950/80 px-2.5 py-1 rounded-md border border-red-800">
                            #PRJ-{{ $project->id }}
                        </span>
                        <span class="text-xs font-bold text-slate-400">مشروع تنموي معتمد</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-black mt-2 text-white">{{ $project->name }}</h1>
                </div>

                <div>
                    <span class="px-3.5 py-1.5 rounded-xl text-sm font-bold {{ $project->status === 'completed' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-amber-500/20 text-amber-400 border border-amber-500/30' }}">
                        {{ $project->status === 'completed' ? 'مشروع مكتمل' : 'قيد التنفيذ والمتابعة' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Project Main Info -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Description Card -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
                    <h2 class="text-sm font-bold text-slate-900 mb-3">وصف المشروع وأهدافه</h2>
                    <p class="text-sm text-slate-700 leading-relaxed whitespace-pre-line">{{ $project->description }}</p>
                </div>

                <!-- Progress Milestone Card -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
                    <h2 class="text-sm font-bold text-slate-900 mb-4">مؤشر التقدم ونسبة الإنجاز</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center text-sm font-bold">
                            <span class="text-slate-600">نسبة الإنجاز الميداني المعتمدة</span>
                            <span class="text-2xl font-black text-red-600">{{ $project->progress_percentage ?? 0 }}%</span>
                        </div>
                        <div class="w-full h-3.5 bg-slate-100 rounded-full overflow-hidden p-0.5 border border-slate-200">
                            <div class="h-full bg-gradient-to-r from-red-600 to-amber-500 rounded-full transition-all duration-500" style="width: {{ $project->progress_percentage ?? 0 }}%"></div>
                        </div>
                    </div>
                </div>

                <!-- Community Contributions -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
                    <h2 class="text-sm font-bold text-slate-900 mb-4">الملاحظات والمشاركات المجتمعية</h2>
                    <div class="space-y-4">
                        @forelse($project->contributions as $contrib)
                            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs font-bold text-slate-800">مواطن مشارك</span>
                                    <span class="text-[11px] text-slate-400">{{ $contrib->created_at->translatedFormat('d F Y') }}</span>
                                </div>
                                <p class="text-xs text-slate-600 leading-relaxed">{{ $contrib->comment }}</p>
                            </div>
                        @empty
                            <p class="text-xs text-slate-400">لا توجد مشاركات مجتمعية مسجلة حتى الآن.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Sidebar Info -->
            <div class="space-y-6">
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">بيانات المشروع</h3>

                    <div>
                        <span class="text-[11px] text-slate-400 font-bold block">الميزانية المرصودة</span>
                        <span class="text-sm font-bold text-slate-800">
                            {{ $project->budget ? number_format($project->budget) . ' ريال يمني' : 'غير محددة' }}
                        </span>
                    </div>

                    <div>
                        <span class="text-[11px] text-slate-400 font-bold block">عدد المستفيدين المقدر</span>
                        <span class="text-sm font-bold text-slate-800">{{ number_format($project->beneficiaries_count ?? 0) }} مواطن</span>
                    </div>

                    <div>
                        <span class="text-[11px] text-slate-400 font-bold block">تاريخ الإطلاق</span>
                        <span class="text-sm font-bold text-slate-800">{{ $project->created_at->translatedFormat('d F Y') }}</span>
                    </div>

                    <div>
                        <span class="text-[11px] text-slate-400 font-bold block">المحافظة / الموقع</span>
                        <span class="text-sm font-bold text-slate-800">{{ $project->governorate ?? 'الجمهورية اليمنية' }}</span>
                    </div>
                </div>

                <a href="{{ route('public.projects.index') }}" 
                   class="block text-center w-full py-3 rounded-xl bg-black text-white text-xs font-bold hover:bg-zinc-800 transition">
                    ← العودة لقائمة المشاريع
                </a>
            </div>
        </div>
    </div>
</x-public-layout>

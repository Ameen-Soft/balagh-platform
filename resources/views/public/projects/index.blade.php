<x-public-layout title="المشاريع التنموية والرقابة المجتمعية - منصة بادر">
    <!-- Institutional Page Header -->
    <div class="bg-[#09090b] text-white py-12 border-b border-zinc-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="flex items-center gap-2 text-xs text-zinc-400 mb-4 font-semibold">
                <a href="{{ url('/') }}" class="hover:text-white transition">الرئيسية</a>
                <span>/</span>
                <span class="text-red-400">دليل المشاريع التنموية</span>
            </nav>
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div class="max-w-2xl">
                    <h1 class="text-2xl sm:text-4xl font-black text-white">دليل المشاريع التنموية الوطنية</h1>
                    <p class="text-zinc-400 text-xs sm:text-sm mt-2 leading-relaxed">
                        منصة متابعة مفتوحة لمشاريع البنية التحتية والخدمات العامة، تتيح للمواطنين الاطلاع على الميزانيات المرصودة ونسب الإنجاز الفعلي على أرض الواقع.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <!-- Segmented Status Filter Bar -->
        <div class="flex flex-wrap items-center justify-between gap-4 mb-8 pb-4 border-b border-slate-200">
            <div class="inline-flex p-1 rounded-lg bg-slate-200/70 border border-slate-200">
                <a href="{{ route('public.projects.index') }}" 
                   class="px-4 py-2 rounded-md text-xs font-bold transition {{ !request('status') ? 'bg-[#08080a] text-white shadow-2xs' : 'text-slate-700 hover:text-slate-950' }}">
                    كافة المشاريع
                </a>
                <a href="{{ route('public.projects.index', ['status' => 'in_progress']) }}" 
                   class="px-4 py-2 rounded-md text-xs font-bold transition {{ request('status') === 'in_progress' ? 'bg-[#08080a] text-white shadow-2xs' : 'text-slate-700 hover:text-slate-950' }}">
                    قيد التنفيذ
                </a>
                <a href="{{ route('public.projects.index', ['status' => 'completed']) }}" 
                   class="px-4 py-2 rounded-md text-xs font-bold transition {{ request('status') === 'completed' ? 'bg-[#08080a] text-white shadow-2xs' : 'text-slate-700 hover:text-slate-950' }}">
                    المشاريع المكتملة
                </a>
            </div>

            <span class="text-xs font-bold text-slate-500">
                إجمالي النتائج المعروضة: <strong class="text-[#08080a] font-mono">{{ $projects->total() }}</strong>
            </span>
        </div>

        <!-- Projects Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($projects as $project)
                <article class="bg-white rounded-xl border border-slate-200 hover:border-slate-300 transition flex flex-col justify-between overflow-hidden">
                    <div class="p-6">
                        <!-- Top Meta -->
                        <div class="flex items-center justify-between gap-2 pb-3.5 mb-4 border-b border-slate-100">
                            <span class="text-xs font-mono font-bold text-slate-800 bg-slate-100 px-2.5 py-1 rounded border border-slate-200/60">
                                #PRJ-{{ $project->id }}
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded text-xs font-bold {{ $project->status === 'completed' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-amber-50 text-amber-800 border border-amber-200' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $project->status === 'completed' ? 'bg-emerald-600' : 'bg-amber-600' }}"></span>
                                {{ $project->status === 'completed' ? 'مكتمل' : 'قيد التنفيذ' }}
                            </span>
                        </div>

                        <!-- Title & Description -->
                        <h2 class="text-base font-black text-slate-900 mb-2 line-clamp-1">{{ $project->name }}</h2>
                        <p class="text-xs text-slate-600 line-clamp-3 leading-relaxed mb-5">{{ $project->description }}</p>

                        <!-- Progress Box -->
                        <div class="p-3.5 rounded-lg bg-[#F8FAFC] border border-slate-200/80 space-y-2 mb-4">
                            <div class="flex justify-between items-center text-xs font-bold">
                                <span class="text-slate-600">نسبة التنفيذ الميداني</span>
                                <span class="font-mono text-slate-950">{{ $project->progress_percentage ?? 0 }}%</span>
                            </div>
                            <div class="w-full h-2 bg-slate-200 rounded-full overflow-hidden">
                                <div class="h-full {{ ($project->progress_percentage ?? 0) >= 100 ? 'bg-[#059669]' : 'bg-[#CE1126]' }} rounded-full" style="width: {{ min(100, max(0, $project->progress_percentage ?? 0)) }}%"></div>
                            </div>
                        </div>

                        <!-- Key Metrics Table -->
                        <div class="text-xs divide-y divide-slate-100 border-t border-slate-100">
                            <div class="py-2 flex items-center justify-between">
                                <span class="text-slate-500 font-semibold">المستفيدون المقدرون</span>
                                <span class="text-slate-900 font-bold font-mono">{{ number_format($project->beneficiaries_count ?? 0) }} مواطن</span>
                            </div>
                            @if($project->budget)
                                <div class="py-2 flex items-center justify-between">
                                    <span class="text-slate-500 font-semibold">الميزانية المرصودة</span>
                                    <span class="text-slate-900 font-bold font-mono">{{ number_format($project->budget) }} ر.ي</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="px-6 py-3.5 bg-slate-50 border-t border-slate-200/80 flex items-center justify-between text-xs">
                        <span class="text-slate-500 font-mono font-semibold">{{ $project->created_at->format('Y-m-d') }}</span>
                        <a href="{{ route('public.projects.show', $project->id) }}" class="font-bold text-[#CE1126] hover:text-[#B70E20]">
                            ملف المشروع ←
                        </a>
                    </div>
                </article>
            @empty
                <div class="col-span-3 text-center py-16 bg-white rounded-xl border border-slate-200">
                    <p class="text-slate-600 font-bold text-sm">لا توجد مشاريع تنموية مضافة في هذا التصنيف حالياً.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $projects->withQueryString()->links() }}
        </div>
    </div>
</x-public-layout>
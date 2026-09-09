<x-public-layout title="المشاريع التنموية والرقابة المجتمعية - منصة بادر">
    <div class="bg-gradient-to-b from-black via-[#08080a] to-[#0d0d11] text-white pt-28 pb-12 border-b border-zinc-800/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl">
                <span class="text-xs font-bold text-red-500 uppercase tracking-wide">الشفافية التنموية</span>
                <h1 class="text-3xl sm:text-4xl font-black mt-2 text-white">دليل المشاريع التنموية الوطنية</h1>
                <p class="text-slate-400 text-sm mt-2 leading-relaxed">
                    متابعة شفافة ومفتوحة لمشاريع البنية التحتية والخدمات العامة، لتمكين المواطن من مراقبة الإنجاز ونسب التنفيذ على أرض الواقع.
                </p>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <!-- Status Filter Tabs -->
        <div class="flex items-center gap-2 mb-8 overflow-x-auto pb-2">
            <a href="{{ route('public.projects.index') }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold transition {{ !request('status') ? 'bg-red-600 text-white shadow-sm' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200' }}">
                جميع المشاريع
            </a>
            <a href="{{ route('public.projects.index', ['status' => 'in_progress']) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold transition {{ request('status') === 'in_progress' ? 'bg-red-600 text-white shadow-sm' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200' }}">
                قيد التنفيذ
            </a>
            <a href="{{ route('public.projects.index', ['status' => 'completed']) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold transition {{ request('status') === 'completed' ? 'bg-red-600 text-white shadow-sm' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200' }}">
                المشاريع المكتملة
            </a>
        </div>

        <!-- Projects Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse($projects as $project)
                <div class="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-xs hover:shadow-lg transition flex flex-col justify-between">
                    <div class="p-6">
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="text-xs font-mono font-bold text-slate-400">#PRJ-{{ $project->id }}</span>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $project->status === 'completed' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                                {{ $project->status === 'completed' ? 'مكتمل' : 'قيد التنفيذ' }}
                            </span>
                        </div>

                        <h3 class="text-lg font-bold text-slate-900 mb-2">{{ $project->name }}</h3>
                        <p class="text-xs text-slate-600 line-clamp-3 leading-relaxed mb-6">{{ $project->description }}</p>

                        <!-- Progress Bar -->
                        <div class="space-y-1.5 mb-4">
                            <div class="flex justify-between text-xs font-bold">
                                <span class="text-slate-500">نسبة التنفيذ</span>
                                <span class="text-red-600">{{ $project->progress_percentage ?? 0 }}%</span>
                            </div>
                            <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-red-600 to-amber-500 rounded-full" style="width: {{ $project->progress_percentage ?? 0 }}%"></div>
                            </div>
                        </div>

                        <div class="text-xs text-slate-500 font-semibold space-y-1 pt-2 border-t border-slate-100">
                            <p>المستفيدين المقدرين: <span class="text-slate-800 font-bold">{{ number_format($project->beneficiaries_count ?? 0) }}</span> مواطن</p>
                            @if($project->budget)
                                <p>الميزانية المقدرة: <span class="text-slate-800 font-bold">{{ number_format($project->budget) }}</span> ريال يمني</p>
                            @endif
                        </div>
                    </div>

                    <div class="p-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between text-xs">
                        <span class="text-slate-400">{{ $project->created_at->format('Y-m-d') }}</span>
                        <a href="{{ route('public.projects.show', $project->id) }}" class="font-bold text-red-600 hover:text-red-700">
                            استعراض التفاصيل ←
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-3 text-center py-16 bg-white rounded-3xl border border-slate-200">
                    <p class="text-slate-500 font-bold">لا توجد مشاريع تنموية مضافة حالياً.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $projects->withQueryString()->links() }}
        </div>
    </div>
</x-public-layout>

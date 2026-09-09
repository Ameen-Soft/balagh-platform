<x-ministry-layout title="لوحة العمليات التشغيلية" headerTitle="مؤشرات الأداء والمعالجة التشغيلية">
    <!-- Department Scope Header (Near-Black Obsidian) -->
    <div class="bg-gradient-to-r from-black via-[#09090b] to-zinc-900 border border-zinc-800/80 text-white rounded-3xl p-6 sm:p-8 mb-8 shadow-md relative overflow-hidden">
        <div class="absolute left-0 top-0 bottom-0 w-1/3 bg-gradient-to-l from-transparent to-red-600/10 pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <span class="text-xs font-bold text-amber-400 bg-amber-950/80 px-3 py-1 rounded-full border border-amber-800 inline-block mb-2">
                    الجهة: {{ $deptOverview['department']->name ?? 'الإدارة المختصة' }}
                </span>
                <h1 class="text-2xl sm:text-3xl font-black text-white">
                    {{ $deptOverview['department']->ministry?->name ?? 'الوزارة التابعة' }}
                </h1>
                <p class="text-xs text-zinc-400 mt-1">بوابة المتابعة والإشراف والتكليف الميداني لمعالجة شكاوى المواطنين</p>
            </div>

            <div class="flex items-center gap-4 text-left">
                <div class="bg-[#0d0d11] border border-zinc-800 rounded-2xl p-4 text-center min-w-[120px]">
                    <span class="text-[11px] text-zinc-400 block font-bold">نسبة إنجاز الجهة</span>
                    <span class="text-2xl font-black text-amber-400">{{ $deptOverview['resolution_rate'] }}%</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Operational KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-500 mb-1">إجمالي البلاغات الواردة</p>
                <h3 class="text-2xl font-black text-slate-900">{{ number_format($deptOverview['total_complaints']) }}</h3>
                <span class="text-[11px] text-slate-400 font-semibold">{{ $deptOverview['submitted'] }} بانتظار المراجعة</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-black text-white flex items-center justify-center border border-zinc-800">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
        </div>

        <!-- In Progress -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-500 mb-1">قيد المعالجة الميدانية</p>
                <h3 class="text-2xl font-black text-amber-600">{{ $deptOverview['in_progress'] }}</h3>
                <span class="text-[11px] text-slate-400 font-semibold">{{ $deptOverview['active_assignments'] }} مهمة ميدانية نشطة</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 border border-amber-200 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <!-- Resolved -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-500 mb-1">تم إنجازها بنجاح</p>
                <h3 class="text-2xl font-black text-emerald-600">{{ $deptOverview['resolved'] + $deptOverview['closed'] }}</h3>
                <span class="text-[11px] text-emerald-600 font-semibold">موثقة بالأدلة والمحاضر</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-200 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
        </div>

        <!-- Urgent Alerts -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-500 mb-1">بلاغات عاجلة وخطرة</p>
                <h3 class="text-2xl font-black text-red-600">{{ $deptOverview['urgent'] }}</h3>
                <span class="text-[11px] text-red-600 font-semibold">تتطلب تدخلاً فورياً (SLA)</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-red-50 text-red-600 border border-red-200 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
        </div>
    </div>

    <!-- Urgent Complaints Warning Section -->
    @if($urgentComplaints->isNotEmpty())
        <div class="bg-red-50 rounded-2xl border border-red-200 p-6 mb-8">
            <div class="flex items-center gap-2 text-red-800 font-black text-sm mb-4">
                <span class="w-3 h-3 rounded-full bg-red-600 animate-ping"></span>
                <span>تنبيه عاجل: بلاغات ذات أولوية قصوى تتطلب اتخاذ إجراء فوري</span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($urgentComplaints as $uc)
                    <div class="bg-white rounded-xl p-4 border border-red-200/80 flex items-center justify-between">
                        <div>
                            <span class="font-mono text-[10px] font-bold text-red-600 block">#{{ $uc->complaint_number }}</span>
                            <h4 class="text-xs font-bold text-slate-900 line-clamp-1">{{ $uc->title }}</h4>
                            <p class="text-[10px] text-slate-400 mt-0.5">مقدم البلاغ: {{ $uc->citizen?->name }}</p>
                        </div>
                        <a href="{{ route('ministry.complaints.show', $uc->id) }}" class="py-1.5 px-3 rounded-lg bg-red-600 text-white text-xs font-bold hover:bg-red-700 transition">
                            معالجة البلاغ
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Recent Department Complaints & Assignments (Ultra-Responsive) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">
        <!-- Complaints Queue Section -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden flex flex-col justify-between">
            <div>
                <div class="p-4 sm:p-6 border-b border-slate-100 flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <h3 class="text-sm sm:text-base font-bold text-slate-900 flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-red-600"></span>
                            سجل البلاغات والشكاوى الواردة للإدارة
                        </h3>
                        <p class="text-[11px] sm:text-xs text-slate-500 mt-0.5">أحدث البلاغات التي تتطلب المعالجة أو الإسناد الميداني</p>
                    </div>
                    <a href="{{ route('ministry.complaints.index') }}" class="text-xs font-bold text-red-600 hover:text-red-700 transition flex items-center gap-1">
                        <span>عرض كافة البلاغات</span>
                        <span>←</span>
                    </a>
                </div>

                <!-- Desktop Table View (Visible on tablet & desktop) -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-bold border-b border-slate-200/80">
                            <tr>
                                <th class="py-3.5 px-5">رقم البلاغ</th>
                                <th class="py-3.5 px-5">الموضوع والتفاصيل</th>
                                <th class="py-3.5 px-5">المواطن</th>
                                <th class="py-3.5 px-5">الأولوية</th>
                                <th class="py-3.5 px-5">الحالة</th>
                                <th class="py-3.5 px-5 text-center">الإجراء</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @forelse($recentComplaints as $c)
                                <tr class="hover:bg-slate-50/80 transition group">
                                    <td class="py-4 px-5 font-mono font-bold text-slate-900 whitespace-nowrap">
                                        <span class="px-2 py-1 rounded bg-slate-100 border border-slate-200/60">#{{ $c->complaint_number }}</span>
                                    </td>
                                    <td class="py-4 px-5 max-w-xs">
                                        <p class="font-bold text-slate-800 truncate group-hover:text-red-600 transition">{{ $c->title }}</p>
                                        <p class="text-[10px] text-slate-400 truncate">{{ $c->category?->name ?? 'بلاغ عام' }}</p>
                                    </td>
                                    <td class="py-4 px-5 text-slate-600 whitespace-nowrap">
                                        <div class="flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                            <span>{{ $c->citizen?->name }}</span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-5 whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold 
                                            {{ $c->priority === 'urgent' ? 'bg-red-100 text-red-800 border border-red-200' : ($c->priority === 'high' ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-slate-100 text-slate-700') }}">
                                            @if($c->priority === 'urgent')
                                                <span class="w-1.5 h-1.5 rounded-full bg-red-600 animate-ping"></span>
                                            @endif
                                            {{ $c->priority_arabic }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-5 whitespace-nowrap">
                                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold 
                                            {{ in_array($c->status, ['resolved', 'closed']) ? 'bg-emerald-100 text-emerald-800' : ($c->status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-amber-50 text-amber-800 border border-amber-200') }}">
                                            {{ $c->status_arabic }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-5 text-center whitespace-nowrap">
                                        <a href="{{ route('ministry.complaints.show', $c->id) }}" class="inline-flex items-center gap-1 py-1 px-3 rounded-lg bg-black text-white font-bold text-[11px] hover:bg-red-600 transition shadow-2xs">
                                            <span>معالجة</span>
                                            <span>←</span>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-12 text-slate-400">لا توجد بلاغات واردة للإدارة حالياً.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card Feed View (Visible on mobile screens) -->
                <div class="block md:hidden divide-y divide-slate-100 p-3 sm:p-4 space-y-3">
                    @forelse($recentComplaints as $c)
                        <div class="p-3.5 rounded-xl bg-slate-50/90 border border-slate-200/80 space-y-2.5">
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-mono text-xs font-bold text-slate-900 bg-white px-2 py-0.5 rounded border border-slate-200">
                                    #{{ $c->complaint_number }}
                                </span>
                                <div class="flex items-center gap-1.5">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold 
                                        {{ $c->priority === 'urgent' ? 'bg-red-100 text-red-800' : ($c->priority === 'high' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700') }}">
                                        {{ $c->priority_arabic }}
                                    </span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold 
                                        {{ in_array($c->status, ['resolved', 'closed']) ? 'bg-emerald-100 text-emerald-800' : ($c->status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800') }}">
                                        {{ $c->status_arabic }}
                                    </span>
                                </div>
                            </div>

                            <div>
                                <h4 class="text-xs font-bold text-slate-900 leading-snug line-clamp-2">{{ $c->title }}</h4>
                                <div class="flex items-center justify-between text-[11px] text-slate-500 mt-1">
                                    <span>المواطن: {{ $c->citizen?->name }}</span>
                                    <span>{{ $c->created_at->format('Y-m-d') }}</span>
                                </div>
                            </div>

                            <a href="{{ route('ministry.complaints.show', $c->id) }}" 
                               class="block text-center w-full py-2 rounded-xl bg-black text-white font-bold text-xs hover:bg-red-600 transition shadow-xs">
                                اتخاذ إجراء تشغيلي ومعالجة ←
                            </a>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 text-center py-8">لا توجد بلاغات واردة للإدارة حالياً.</p>
                    @endforelse
                </div>
            </div>

            <div class="p-3 sm:p-4 border-t border-slate-100 bg-slate-50/50 flex items-center justify-between text-xs text-slate-500">
                <span>عرض أحدث {{ $recentComplaints->count() }} بلاغات</span>
                <a href="{{ route('ministry.complaints.index') }}" class="font-bold text-slate-700 hover:text-red-600 transition">
                    الانتقال لسجل الفلترة الشامل
                </a>
            </div>
        </div>

        <!-- Active Field Tasks Column (Responsive Cards) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 sm:p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-3">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                            المهام الميدانية الجارية
                        </h3>
                        <p class="text-[11px] text-slate-400 mt-0.5">متابعة الفِرق في الميدان</p>
                    </div>
                    <a href="{{ route('ministry.assignments.index') }}" class="text-[11px] font-bold text-red-600 hover:text-red-700 transition">عرض الكل</a>
                </div>

                <div class="space-y-3">
                    @forelse($recentAssignments as $ra)
                        <div class="p-3.5 rounded-xl bg-slate-50/90 border border-slate-200/80 hover:border-slate-300 transition">
                            <div class="flex items-center justify-between mb-1.5">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-lg bg-amber-100 text-amber-800 flex items-center justify-center font-bold text-xs shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    </div>
                                    <span class="text-xs font-bold text-slate-800 truncate max-w-[130px] sm:max-w-none">{{ $ra->worker?->name }}</span>
                                </div>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full 
                                    {{ $ra->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : ($ra->status === 'in_progress' ? 'bg-amber-100 text-amber-800' : 'bg-slate-200 text-slate-700') }}">
                                    {{ $ra->status }}
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-600 line-clamp-1">بلاغ: {{ $ra->complaint?->title }}</p>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            </div>
                            <p class="text-xs text-slate-400">لا توجد مهام ميدانية جارية الآن.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-100">
                <a href="{{ route('ministry.complaints.index') }}" class="block text-center py-2.5 rounded-xl bg-black text-white text-xs font-bold hover:bg-zinc-800 transition shadow-sm">
                    + إسناد مهمة ميدانية جديدة
                </a>
            </div>
        </div>
    </div>
</x-ministry-layout>

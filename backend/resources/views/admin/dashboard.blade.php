<x-admin-layout title="لوحة الإدارة العامة والرقابة الوطنية" headerTitle="لوحة المؤشرات والرقابة الشاملة">
    <!-- Quick Statistics KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Card 1: Total Complaints -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-500 mb-1">إجمالي البلاغات الوطنية</p>
                <h3 class="text-2xl font-black text-slate-900">{{ number_format($overview['total_complaints']) }}</h3>
                <span class="text-[11px] text-slate-400 font-semibold">مسجلة في كافة المحافظات</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-black text-white flex items-center justify-center border border-zinc-800">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
        </div>

        <!-- Card 2: Resolution Rate -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-500 mb-1">نسبة الإنجاز والمعالجة</p>
                <h3 class="text-2xl font-black text-red-600">{{ $overview['resolution_rate'] }}%</h3>
                <span class="text-[11px] text-emerald-600 font-semibold">{{ number_format($overview['resolved_complaints']) }} بلاغ معالج بنجاح</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-red-50 text-red-600 border border-red-200 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <!-- Card 3: Ministries & Departments -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-500 mb-1">الجهات الحكومية المربوطة</p>
                <h3 class="text-2xl font-black text-slate-900">{{ $overview['total_ministries'] }} وزارات</h3>
                <span class="text-[11px] text-slate-400 font-semibold">{{ $overview['total_departments'] }} إدارة خدمية مفعلة</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 border border-amber-200 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
        </div>

        <!-- Card 4: Field Workers & Citizens -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-500 mb-1">الميدان والمواطنين</p>
                <h3 class="text-2xl font-black text-slate-900">{{ number_format($overview['total_citizens']) }}</h3>
                <span class="text-[11px] text-slate-400 font-semibold">{{ $overview['total_field_workers'] }} مهندس وفني ميداني</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-zinc-100 text-zinc-800 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
        </div>
    </div>

    <!-- Second Row: Status Distribution & Categories -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Status Breakdown Card -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs">
            <h4 class="text-sm font-bold text-slate-900 mb-4">حالات البلاغات الحالية</h4>
            <div class="space-y-3">
                <div class="flex items-center justify-between text-xs">
                    <span class="font-semibold text-slate-600 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-zinc-600"></span> بلاغات جديدة ومراجعة
                    </span>
                    <span class="font-bold text-slate-900">{{ $statusCounts['submitted'] + $statusCounts['under_review'] }}</span>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="font-semibold text-slate-600 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> قيد العمل الميداني
                    </span>
                    <span class="font-bold text-slate-900">{{ $statusCounts['in_progress'] }}</span>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="font-semibold text-slate-600 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> تم الحل والإغلاق
                    </span>
                    <span class="font-bold text-slate-900">{{ $statusCounts['resolved'] + $statusCounts['closed'] }}</span>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="font-semibold text-slate-600 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span> بلاغات مرفوضة
                    </span>
                    <span class="font-bold text-slate-900">{{ $statusCounts['rejected'] }}</span>
                </div>
            </div>
        </div>

        <!-- Top Categories Breakdown -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs lg:col-span-2">
            <h4 class="text-sm font-bold text-slate-900 mb-4">أعلى التصنيفات الخدمية استقبالاً للبلاغات</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @forelse($categoryCounts as $cat)
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-800">{{ $cat['name'] }}</span>
                        <span class="text-xs font-black px-2.5 py-1 rounded-md bg-white border border-slate-200 text-red-600">
                            {{ $cat['count'] }} بلاغ
                        </span>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 col-span-2">لا توجد بيانات تصنيفات بعد.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Recent Nationwide Complaints Monitor -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-4 sm:p-6 border-b border-slate-100 flex flex-wrap items-center justify-between gap-2">
            <div>
                <h4 class="text-sm sm:text-base font-bold text-slate-900">سجل الرقابة اللحظي على البلاغات</h4>
                <p class="text-[11px] sm:text-xs text-slate-500 mt-0.5">متابعة مركزية لجميع البلاغات الواردة لمختلف الوزارات والجهات</p>
            </div>
            <a href="{{ route('admin.complaints.index') }}" class="text-xs font-bold text-red-600 hover:text-red-700">
                عرض كافة البلاغات ←
            </a>
        </div>

        <!-- Desktop Table View -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-bold border-b border-slate-200/80">
                    <tr>
                        <th class="py-3.5 px-6">رقم البلاغ</th>
                        <th class="py-3.5 px-6">موضوع البلاغ</th>
                        <th class="py-3.5 px-6">الجهة المعنية</th>
                        <th class="py-3.5 px-6">الأولوية</th>
                        <th class="py-3.5 px-6">الحالة</th>
                        <th class="py-3.5 px-6">التاريخ</th>
                        <th class="py-3.5 px-6 text-center">إجراء</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($recentComplaints as $complaint)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-4 px-6 font-mono font-bold text-slate-900 whitespace-nowrap">#{{ $complaint->complaint_number }}</td>
                            <td class="py-4 px-6 text-slate-900 font-bold max-w-xs truncate">{{ $complaint->title }}</td>
                            <td class="py-4 px-6 text-slate-600 whitespace-nowrap">
                                {{ $complaint->currentDepartment?->ministry?->name ?? 'غير محدد' }}
                            </td>
                            <td class="py-4 px-6 whitespace-nowrap">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold 
                                    {{ $complaint->priority === 'urgent' ? 'bg-red-100 text-red-800 border border-red-200' : ($complaint->priority === 'high' ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-slate-100 text-slate-700') }}">
                                    {{ $complaint->priority_arabic }}
                                </span>
                            </td>
                            <td class="py-4 px-6 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold
                                    {{ in_array($complaint->status, ['resolved', 'closed']) ? 'bg-emerald-100 text-emerald-800' : 'bg-zinc-100 text-zinc-800 border border-zinc-200' }}">
                                    {{ $complaint->status_arabic }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-slate-400 whitespace-nowrap">{{ $complaint->created_at->format('Y-m-d') }}</td>
                            <td class="py-4 px-6 text-center whitespace-nowrap">
                                <a href="{{ route('admin.complaints.show', $complaint->id) }}" class="inline-flex items-center gap-1 py-1.5 px-3 rounded-xl bg-black text-white font-bold text-xs hover:bg-red-600 transition shadow-xs">
                                    <span>معاينة الرقابة</span>
                                    <span>←</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-slate-400">لا توجد بلاغات مسجلة حالياً.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Card View -->
        <div class="block md:hidden divide-y divide-slate-100 p-3.5 space-y-3">
            @forelse($recentComplaints as $complaint)
                <div class="p-4 rounded-2xl bg-slate-50/90 border border-slate-200/80 space-y-2.5">
                    <div class="flex items-center justify-between gap-2">
                        <span class="font-mono text-xs font-bold text-slate-900 bg-white px-2.5 py-1 rounded-lg border border-slate-200">
                            #{{ $complaint->complaint_number }}
                        </span>
                        <div class="flex items-center gap-1.5">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold 
                                {{ $complaint->priority === 'urgent' ? 'bg-red-100 text-red-800' : ($complaint->priority === 'high' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700') }}">
                                {{ $complaint->priority_arabic }}
                            </span>
                            <span class="px-2.5 py-0.5 rounded text-[10px] font-bold 
                                {{ in_array($complaint->status, ['resolved', 'closed']) ? 'bg-emerald-100 text-emerald-800' : 'bg-zinc-100 text-zinc-800 border border-zinc-200' }}">
                                {{ $complaint->status_arabic }}
                            </span>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-xs font-bold text-slate-900 leading-snug">{{ $complaint->title }}</h4>
                        <div class="flex items-center justify-between text-[11px] text-slate-500 mt-1.5 pt-1.5 border-t border-slate-200/60">
                            <span>{{ $complaint->currentDepartment?->ministry?->name ?? 'الوزارة' }}</span>
                            <span>{{ $complaint->created_at->format('Y-m-d') }}</span>
                        </div>
                    </div>

                    <a href="{{ route('admin.complaints.show', $complaint->id) }}" 
                       class="block text-center w-full py-2 rounded-xl bg-black text-white font-bold text-xs hover:bg-red-600 transition shadow-xs">
                        معاينة الرقابة ←
                    </a>
                </div>
            @empty
                <p class="text-xs text-slate-400 text-center py-6">لا توجد بلاغات مسجلة حالياً.</p>
            @endforelse
        </div>
    </div>
</x-admin-layout>

<x-ministry-layout title="التكليفات والمهام الميدانية" headerTitle="متابعة المهام الميدانية وفرق الصيانة">
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-sm font-bold text-slate-900">سجل التكليفات الميدانية للجهة</h3>
                <p class="text-xs text-slate-500 mt-0.5">متابعة إنجاز الفرق الميدانية والأدلة المصورة المرفوعة من الميدان</p>
            </div>

            <!-- Status Tabs -->
            <div class="flex items-center gap-2 overflow-x-auto pb-1">
                <a href="{{ route('ministry.assignments.index') }}" 
                   class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ !request('status') ? 'bg-red-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    الكل
                </a>
                <a href="{{ route('ministry.assignments.index', ['status' => 'pending']) }}" 
                   class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ request('status') === 'pending' ? 'bg-red-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    قيد الانتظار
                </a>
                <a href="{{ route('ministry.assignments.index', ['status' => 'in_progress']) }}" 
                   class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ request('status') === 'in_progress' ? 'bg-red-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    جاري التنفيذ
                </a>
                <a href="{{ route('ministry.assignments.index', ['status' => 'completed']) }}" 
                   class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ request('status') === 'completed' ? 'bg-red-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    مكتمل
                </a>
            </div>
        </div>

        <!-- Desktop Table View -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-bold border-b border-slate-200/80">
                    <tr>
                        <th class="py-3.5 px-6">الموظف الميداني</th>
                        <th class="py-3.5 px-6">البلاغ المرتبط</th>
                        <th class="py-3.5 px-6">مُسند من قِبل</th>
                        <th class="py-3.5 px-6">الحالة</th>
                        <th class="py-3.5 px-6">الأدلة المرفوعة</th>
                        <th class="py-3.5 px-6">تاريخ التكليف</th>
                        <th class="py-3.5 px-6 text-center">إجراء</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($assignments as $a)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-4 px-6 whitespace-nowrap">
                                <p class="font-bold text-slate-900">{{ $a->worker?->name }}</p>
                                <p class="text-[10px] text-slate-400 font-mono">{{ $a->worker?->phone }}</p>
                            </td>
                            <td class="py-4 px-6">
                                <p class="font-bold text-slate-800 max-w-xs truncate">{{ $a->complaint?->title }}</p>
                                <p class="text-[10px] text-slate-400 font-mono">#{{ $a->complaint?->complaint_number }}</p>
                            </td>
                            <td class="py-4 px-6 text-slate-600 whitespace-nowrap">{{ $a->assignedBy?->name }}</td>
                            <td class="py-4 px-6 whitespace-nowrap">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold 
                                    {{ $a->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : ($a->status === 'in_progress' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700') }}">
                                    {{ match($a->status) {
                                        'pending' => 'قيد الانتظار',
                                        'in_progress' => 'جاري التنفيذ',
                                        'completed' => 'مكتمل',
                                        default => $a->status,
                                    } }}
                                </span>
                            </td>
                            <td class="py-4 px-6 whitespace-nowrap">
                                <span class="font-bold text-slate-700">{{ $a->evidence->count() }} دليل / صورة</span>
                            </td>
                            <td class="py-4 px-6 text-slate-400 whitespace-nowrap">{{ $a->created_at->format('Y-m-d') }}</td>
                            <td class="py-4 px-6 text-center whitespace-nowrap">
                                <a href="{{ route('ministry.complaints.show', $a->complaint_id) }}" class="inline-flex items-center gap-1 py-1.5 px-3 rounded-xl bg-black text-white font-bold text-xs hover:bg-red-600 transition shadow-xs">
                                    <span>معاينة البلاغ</span>
                                    <span>←</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-12 text-slate-400">لا توجد تكليفات ميدانية مسجلة.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Card View -->
        <div class="block md:hidden divide-y divide-slate-100 p-3.5 space-y-3">
            @forelse($assignments as $a)
                <div class="p-4 rounded-2xl bg-slate-50/90 border border-slate-200/80 space-y-2.5">
                    <div class="flex items-center justify-between gap-2">
                        <span class="font-mono text-xs font-bold text-slate-900 bg-white px-2.5 py-1 rounded-lg border border-slate-200">
                            #{{ $a->complaint?->complaint_number }}
                        </span>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold 
                            {{ $a->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : ($a->status === 'in_progress' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700') }}">
                            {{ match($a->status) {
                                'pending' => 'قيد الانتظار',
                                'in_progress' => 'جاري التنفيذ',
                                'completed' => 'مكتمل',
                                default => $a->status,
                            } }}
                        </span>
                    </div>

                    <div>
                        <h4 class="text-xs font-bold text-slate-900 leading-snug">{{ $a->complaint?->title }}</h4>
                        <div class="mt-2 space-y-1 text-xs text-slate-600 pt-2 border-t border-slate-200/60">
                            <p class="font-medium text-slate-800">
                                <span class="text-slate-400">المكلف الميداني:</span> {{ $a->worker?->name }} ({{ $a->worker?->phone }})
                            </p>
                            <div class="flex items-center justify-between text-slate-500">
                                <span>الأدلة: {{ $a->evidence->count() }} ملف</span>
                                <span class="text-[11px] text-slate-400">{{ $a->created_at->format('Y-m-d') }}</span>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('ministry.complaints.show', $a->complaint_id) }}" 
                       class="block text-center w-full py-2 rounded-xl bg-black text-white font-bold text-xs hover:bg-red-600 transition shadow-xs">
                        معاينة البلاغ والتكليف ←
                    </a>
                </div>
            @empty
                <p class="text-xs text-slate-400 text-center py-8">لا توجد تكليفات ميدانية مسجلة.</p>
            @endforelse
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $assignments->withQueryString()->links() }}
        </div>
    </div>
</x-ministry-layout>

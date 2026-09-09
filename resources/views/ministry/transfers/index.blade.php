<x-ministry-layout title="إحالات الشكاوى بين الجهات" headerTitle="إدارة ومتابعة إحالات الشكاوى">
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-4 sm:p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-sm sm:text-base font-bold text-slate-900">سجل الإحالات بين الجهات الحكومية</h3>
                <p class="text-[11px] sm:text-xs text-slate-500 mt-0.5">متابعة الشكاوى المحالة إلى هذه الإدارة أو المحالة منها لعدم الاختصاص</p>
            </div>

            <!-- Type Tabs: Incoming vs Outgoing -->
            <div class="flex items-center gap-2 overflow-x-auto pb-1">
                <a href="{{ route('ministry.transfers.index', ['type' => 'incoming']) }}" 
                   class="whitespace-nowrap px-4 py-2 rounded-xl text-xs font-bold transition {{ $type === 'incoming' ? 'bg-red-600 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    الإحالات الواردة للجهة
                </a>
                <a href="{{ route('ministry.transfers.index', ['type' => 'outgoing']) }}" 
                   class="whitespace-nowrap px-4 py-2 rounded-xl text-xs font-bold transition {{ $type === 'outgoing' ? 'bg-red-600 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    الإحالات الصادرة من الجهة
                </a>
            </div>
        </div>

        <!-- Desktop Table View -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-bold border-b border-slate-200/80">
                    <tr>
                        <th class="py-3.5 px-6">رقم البلاغ</th>
                        <th class="py-3.5 px-6">موضوع البلاغ</th>
                        <th class="py-3.5 px-6">{{ $type === 'incoming' ? 'محال من جهة' : 'محال إلى جهة' }}</th>
                        <th class="py-3.5 px-6">سبب الإحالة</th>
                        <th class="py-3.5 px-6">المُحيل</th>
                        <th class="py-3.5 px-6">التاريخ</th>
                        <th class="py-3.5 px-6 text-center">إجراء</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($transfers as $t)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-4 px-6 font-mono font-bold text-slate-900 whitespace-nowrap">#{{ $t->complaint?->complaint_number }}</td>
                            <td class="py-4 px-6 font-bold text-slate-800 max-w-xs truncate">{{ $t->complaint?->title }}</td>
                            <td class="py-4 px-6 text-slate-600 whitespace-nowrap">
                                @if($type === 'incoming')
                                    <p class="font-bold text-slate-800">{{ $t->fromDepartment?->name ?? 'غير محدد' }}</p>
                                    <p class="text-[10px] text-slate-400">{{ $t->fromDepartment?->ministry?->name }}</p>
                                @else
                                    <p class="font-bold text-slate-800">{{ $t->toDepartment?->name ?? 'غير محدد' }}</p>
                                    <p class="text-[10px] text-slate-400">{{ $t->toDepartment?->ministry?->name }}</p>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-slate-600 max-w-xs truncate">{{ $t->reason }}</td>
                            <td class="py-4 px-6 text-slate-600 whitespace-nowrap">{{ $t->transferredByUser?->name }}</td>
                            <td class="py-4 px-6 text-slate-400 whitespace-nowrap">{{ $t->created_at->format('Y-m-d') }}</td>
                            <td class="py-4 px-6 text-center whitespace-nowrap">
                                <a href="{{ route('ministry.complaints.show', $t->complaint_id) }}" class="inline-flex items-center gap-1 py-1.5 px-3 rounded-xl bg-black text-white font-bold text-xs hover:bg-red-600 transition shadow-xs">
                                    <span>معاينة</span>
                                    <span>←</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-12 text-slate-400">لا توجد إحالات مسجلة في هذا القسم.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Card View -->
        <div class="block md:hidden divide-y divide-slate-100 p-3.5 space-y-3">
            @forelse($transfers as $t)
                <div class="p-4 rounded-2xl bg-slate-50/90 border border-slate-200/80 space-y-2.5">
                    <div class="flex items-center justify-between gap-2">
                        <span class="font-mono text-xs font-bold text-slate-900 bg-white px-2.5 py-1 rounded-lg border border-slate-200">
                            #{{ $t->complaint?->complaint_number }}
                        </span>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                            {{ $type === 'incoming' ? 'واردة' : 'صادرة' }}
                        </span>
                    </div>

                    <div>
                        <h4 class="text-xs font-bold text-slate-900 leading-snug">{{ $t->complaint?->title }}</h4>
                        <div class="mt-2 space-y-1 text-xs text-slate-600 pt-2 border-t border-slate-200/60">
                            <p class="font-medium text-slate-800">
                                <span class="text-slate-400">{{ $type === 'incoming' ? 'المُرسل:' : 'المُحال إليه:' }}</span>
                                {{ $type === 'incoming' ? ($t->fromDepartment?->name ?? 'غير محدد') : ($t->toDepartment?->name ?? 'غير محدد') }}
                            </p>
                            <p class="text-[11px] text-slate-500 line-clamp-2">
                                <span class="text-slate-400">السبب:</span> {{ $t->reason }}
                            </p>
                            <div class="flex items-center justify-between text-slate-400 text-[11px] pt-1">
                                <span>المسؤول: {{ $t->transferredByUser?->name }}</span>
                                <span>{{ $t->created_at->format('Y-m-d') }}</span>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('ministry.complaints.show', $t->complaint_id) }}" 
                       class="block text-center w-full py-2 rounded-xl bg-black text-white font-bold text-xs hover:bg-red-600 transition shadow-xs">
                        معاينة البلاغ المُحال ←
                    </a>
                </div>
            @empty
                <p class="text-xs text-slate-400 text-center py-8">لا توجد إحالات مسجلة في هذا القسم.</p>
            @endforelse
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $transfers->withQueryString()->links() }}
        </div>
    </div>
</x-ministry-layout>

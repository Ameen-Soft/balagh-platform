<x-admin-layout title="الرقابة الشاملة على البلاغات" headerTitle="منظومة الرقابة المركزية للبلاغات والشكاوى">
    <!-- Filter Section -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-6 mb-8 shadow-xs">
        <form method="GET" action="{{ route('admin.complaints.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <!-- Search -->
            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-slate-700 mb-1.5">البحث (رقم، عنوان، مواطن، هاتف)</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="ابحث برقم البلاغ، الكلمات، أو المواطن..." 
                       class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500 focus:border-red-500">
            </div>

            <!-- Ministry Filter -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">الوزارة المختصة</label>
                <select name="ministry_id" class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    <option value="">جميع الوزارات</option>
                    @foreach($ministries as $min)
                        <option value="{{ $min->id }}" {{ request('ministry_id') == $min->id ? 'selected' : '' }}>
                            {{ $min->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">الحالة الإجرائية</label>
                <select name="status" class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    <option value="all">جميع الحالات</option>
                    <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>جديد (Submitted)</option>
                    <option value="under_review" {{ request('status') === 'under_review' ? 'selected' : '' }}>قيد المراجعة (Under Review)</option>
                    <option value="assigned" {{ request('status') === 'assigned' ? 'selected' : '' }}>مسند للميدان (Assigned)</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>جاري التنفيذ (In Progress)</option>
                    <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>معالج (Resolved)</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>مرفوض (Rejected)</option>
                    <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>مغلق (Closed)</option>
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 px-4 rounded-xl bg-black text-white font-bold text-xs hover:bg-zinc-800 transition">
                    تطبيق الفلترة
                </button>
                @if(request()->hasAny(['search', 'ministry_id', 'status']))
                    <a href="{{ route('admin.complaints.index') }}" class="py-2 px-3 rounded-xl bg-slate-100 text-slate-600 text-xs font-bold hover:bg-slate-200 transition">
                        إعادة ضبط
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Complaints Table & Mobile Cards -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-2 text-xs text-slate-500">
            <span class="font-bold">إجمالي النتائج المعروضة: {{ $complaints->total() }} بلاغ</span>
            <span class="text-red-600 font-bold">منظومة الرقابة المركزية</span>
        </div>

        <!-- Desktop Table View (md and up) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-bold border-b border-slate-200/80">
                    <tr>
                        <th class="py-3.5 px-6">رقم البلاغ</th>
                        <th class="py-3.5 px-6">عنوان البلاغ</th>
                        <th class="py-3.5 px-6">المواطن</th>
                        <th class="py-3.5 px-6">الجهة الحكومية</th>
                        <th class="py-3.5 px-6">التصنيف</th>
                        <th class="py-3.5 px-6">الأولوية</th>
                        <th class="py-3.5 px-6">الحالة</th>
                        <th class="py-3.5 px-6">تاريخ التسجيل</th>
                        <th class="py-3.5 px-6 text-center">إجراء</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($complaints as $c)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-4 px-6 font-mono font-bold text-slate-900 whitespace-nowrap">#{{ $c->complaint_number }}</td>
                            <td class="py-4 px-6 font-bold text-slate-800 max-w-xs truncate">{{ $c->title }}</td>
                            <td class="py-4 px-6 text-slate-600 whitespace-nowrap">
                                <p class="font-bold text-slate-800">{{ $c->citizen?->name ?? 'غير مسجل' }}</p>
                                <p class="text-[10px] text-slate-400 font-mono">{{ $c->citizen?->phone }}</p>
                            </td>
                            <td class="py-4 px-6 text-slate-600 whitespace-nowrap">
                                <p class="font-bold text-slate-800">{{ $c->currentDepartment?->ministry?->name ?? 'غير محدد' }}</p>
                                <p class="text-[10px] text-slate-400">{{ $c->currentDepartment?->name }}</p>
                            </td>
                            <td class="py-4 px-6 text-slate-600 whitespace-nowrap">{{ $c->category?->name ?? 'عام' }}</td>
                            <td class="py-4 px-6 whitespace-nowrap">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold 
                                    {{ $c->priority === 'urgent' ? 'bg-red-100 text-red-800 border border-red-200' : ($c->priority === 'high' ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-slate-100 text-slate-700') }}">
                                    {{ $c->priority_arabic }}
                                </span>
                            </td>
                            <td class="py-4 px-6 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold
                                    {{ in_array($c->status, ['resolved', 'closed']) ? 'bg-emerald-100 text-emerald-800' : ($c->status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-zinc-100 text-zinc-800 border border-zinc-200') }}">
                                    {{ $c->status_arabic }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-slate-400 whitespace-nowrap">{{ $c->created_at->format('Y-m-d') }}</td>
                            <td class="py-4 px-6 text-center whitespace-nowrap">
                                <a href="{{ route('admin.complaints.show', $c->id) }}" class="inline-flex items-center gap-1 py-1.5 px-3 rounded-xl bg-black text-white font-bold text-xs hover:bg-red-600 transition shadow-xs">
                                    <span>معاينة الرقابة</span>
                                    <span>←</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-12 text-slate-400 font-bold">لا توجد بلاغات تطابق شروط التصفية.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Card View (Phone screens under md) -->
        <div class="block md:hidden divide-y divide-slate-100 p-3.5 space-y-3">
            @forelse($complaints as $c)
                <div class="p-4 rounded-2xl bg-slate-50/90 border border-slate-200/80 space-y-3">
                    <div class="flex items-center justify-between gap-2">
                        <span class="font-mono text-xs font-bold text-slate-900 bg-white px-2.5 py-1 rounded-lg border border-slate-200 shadow-2xs">
                            #{{ $c->complaint_number }}
                        </span>
                        <div class="flex items-center gap-1.5">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold 
                                {{ $c->priority === 'urgent' ? 'bg-red-100 text-red-800' : ($c->priority === 'high' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700') }}">
                                {{ $c->priority_arabic }}
                            </span>
                            <span class="px-2.5 py-0.5 rounded-lg text-[10px] font-bold 
                                {{ in_array($c->status, ['resolved', 'closed']) ? 'bg-emerald-100 text-emerald-800' : ($c->status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-zinc-100 text-zinc-800 border border-zinc-200') }}">
                                {{ $c->status_arabic }}
                            </span>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-sm font-bold text-slate-900 leading-snug">{{ $c->title }}</h4>
                        <div class="mt-2 space-y-1 text-xs text-slate-600 pt-2 border-t border-slate-200/60">
                            <p class="font-medium text-slate-800">
                                <span class="text-slate-400">الجهة:</span> {{ $c->currentDepartment?->ministry?->name ?? 'غير محدد' }} ({{ $c->currentDepartment?->name }})
                            </p>
                            <div class="flex items-center justify-between text-slate-500">
                                <span>المواطن: {{ $c->citizen?->name ?? 'غير مسجل' }}</span>
                                <span class="text-[11px] text-slate-400">{{ $c->created_at->format('Y-m-d') }}</span>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('admin.complaints.show', $c->id) }}" 
                       class="block text-center w-full py-2.5 rounded-xl bg-black text-white font-bold text-xs hover:bg-red-600 transition shadow-xs">
                        معاينة وتدقيق البلاغ ←
                    </a>
                </div>
            @empty
                <div class="text-center py-10 text-slate-400 font-bold text-xs">
                    لا توجد بلاغات تطابق شروط التصفية.
                </div>
            @endforelse
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $complaints->withQueryString()->links() }}
        </div>
    </div>
</x-admin-layout>

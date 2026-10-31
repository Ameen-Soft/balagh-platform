<x-ministry-layout :title="'تفاصيل الإدارة: ' . $department->name" headerTitle="تفاصيل وسجلات الإدارة الخدمية">
    <!-- Flash Messages -->
    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-bold flex items-center gap-3">
            <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 text-xs font-bold flex items-center gap-3">
            <svg class="w-5 h-5 text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="space-y-6" x-data="{ editModalOpen: false }">
        <!-- Top Navigation & Department Profile Header -->
        <div class="p-6 rounded-2xl bg-[#111116] border border-zinc-800 shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold text-zinc-400 mb-2">
                    <a href="{{ route('ministry.departments.index') }}" class="hover:text-amber-400 transition flex items-center gap-1">
                        <span>← العودة للإدارات الخدمية</span>
                    </a>
                    <span>/</span>
                    <span class="text-zinc-200">{{ $department->name }}</span>
                </div>
                <div class="flex items-center gap-3">
                    <h1 class="text-xl font-black text-white">{{ $department->name }}</h1>
                    <span class="px-2.5 py-1 rounded-md text-[11px] font-bold {{ $department->is_active ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-zinc-800 text-zinc-400 border border-zinc-700' }}">
                        {{ $department->is_active ? 'نشطة وتشغيلية' : 'معطلة / مؤرشفة' }}
                    </span>
                </div>
                <p class="text-xs text-zinc-400 mt-2 max-w-2xl leading-relaxed">
                    {{ $department->description }}
                </p>
                <div class="flex items-center gap-4 text-[11px] text-zinc-500 mt-3">
                    <span>الوزارة: <strong class="text-zinc-300">{{ $ministry->name }}</strong></span>
                    <span>•</span>
                    <span>تاريخ التأسيس: <strong class="text-zinc-300 font-mono">{{ $department->created_at->format('Y/m/d') }}</strong></span>
                </div>
            </div>

            <!-- Header Action Buttons -->
            <div class="flex items-center gap-2 shrink-0">
                <button type="button" @click="editModalOpen = true"
                        class="px-3.5 py-2 rounded-xl bg-amber-500/10 hover:bg-amber-500/20 border border-amber-500/30 text-amber-400 text-xs font-bold transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>تعديل الإدارة</span>
                </button>

                <form method="POST" action="{{ route('ministry.departments.toggle_active', $department->id) }}" class="inline"
                      onsubmit="return confirm('{{ $department->is_active ? 'هل تريد تعطيل هذه الإدارة؟ لن يتم حذف البيانات وستستمر المهام الجارية، ولكن لن تُستقبل بلاغات جديدة.' : 'هل تريد تفعيل هذه الإدارة؟' }}');">
                    @csrf
                    <button type="submit"
                            class="px-3.5 py-2 rounded-xl text-xs font-bold border transition {{ $department->is_active ? 'bg-zinc-800 text-zinc-300 border-zinc-700 hover:bg-zinc-700' : 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30 hover:bg-emerald-500/30' }}">
                        {{ $department->is_active ? 'تعطيل وأرشفة' : 'إعادة التفعيل' }}
                    </button>
                </form>

                <form method="POST" action="{{ route('ministry.departments.destroy', $department->id) }}" class="inline"
                      onsubmit="return confirm('هل أنت متأكد من حذف هذه الإدارة نهائياً؟ تنبيه: لن يُسمح بالحذف إذا وُجدت أي بلاغات أو تصنيفات أو موظفين.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="px-3 py-2 rounded-xl bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/20 text-xs font-bold transition">
                        حذف
                    </button>
                </form>
            </div>
        </div>

        <!-- Metrics Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            <div class="p-4 rounded-xl bg-[#111116] border border-zinc-800 text-center">
                <span class="text-[11px] font-bold text-zinc-400 block mb-1">إجمالي البلاغات</span>
                <span class="text-xl font-black text-white font-mono">{{ $totalComplaints }}</span>
            </div>
            <div class="p-4 rounded-xl bg-[#111116] border border-zinc-800 text-center">
                <span class="text-[11px] font-bold text-amber-400 block mb-1">بلاغات مفتوحة</span>
                <span class="text-xl font-black text-amber-400 font-mono">{{ $openComplaints }}</span>
            </div>
            <div class="p-4 rounded-xl bg-[#111116] border border-zinc-800 text-center">
                <span class="text-[11px] font-bold text-emerald-400 block mb-1">بلاغات منجزة</span>
                <span class="text-xl font-black text-emerald-400 font-mono">{{ $resolvedComplaints }}</span>
            </div>
            <div class="p-4 rounded-xl bg-[#111116] border border-zinc-800 text-center">
                <span class="text-[11px] font-bold text-zinc-400 block mb-1">الكادر الميداني</span>
                <span class="text-xl font-black text-white font-mono">{{ $workers->count() }}</span>
            </div>
            <div class="p-4 rounded-xl bg-[#111116] border border-zinc-800 text-center">
                <span class="text-[11px] font-bold text-amber-400 block mb-1">مهام ميدانية جارية</span>
                <span class="text-xl font-black text-amber-400 font-mono">{{ $tasksStats['active'] }}</span>
            </div>
            <div class="p-4 rounded-xl bg-[#111116] border border-zinc-800 text-center">
                <span class="text-[11px] font-bold text-emerald-400 block mb-1">مهام مكتملة</span>
                <span class="text-xl font-black text-emerald-400 font-mono">{{ $tasksStats['completed'] }}</span>
            </div>
        </div>

        <!-- Main Content 2-Columns: Workers & Categories -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Field Workers Section -->
            <div class="bg-[#111116] rounded-2xl border border-zinc-800 p-5 shadow-xl">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-zinc-800">
                    <h3 class="text-sm font-black text-white flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        الموظفون الميدانيون التابعون للإدارة ({{ $workers->count() }})
                    </h3>
                    <a href="{{ route('ministry.workers.index', ['department_id' => $department->id]) }}" class="text-[11px] font-bold text-amber-400 hover:underline">
                        إدارة الموظفين ←
                    </a>
                </div>

                <div class="space-y-3 max-h-96 overflow-y-auto">
                    @forelse($workers as $worker)
                        <div class="p-3.5 rounded-xl bg-zinc-900/80 border border-zinc-800 flex items-center justify-between gap-3 hover:border-zinc-700 transition">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-zinc-800 text-amber-400 font-black text-xs flex items-center justify-center shrink-0">
                                    {{ mb_substr($worker->name, 0, 1) }}
                                </div>
                                <div>
                                    <a href="{{ route('ministry.workers.show', $worker->id) }}" class="text-xs font-bold text-white hover:text-amber-400 transition block">
                                        {{ $worker->name }}
                                    </a>
                                    <span class="text-[11px] font-mono text-zinc-500">{{ $worker->phone }}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 text-center text-[10px]">
                                <span class="px-2 py-0.5 rounded bg-amber-500/10 text-amber-400 font-bold">
                                    {{ $worker->active_tasks_count }} نشطة
                                </span>
                                <span class="px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 font-bold">
                                    {{ $worker->completed_tasks_count }} مكتملة
                                </span>
                                <a href="{{ route('ministry.workers.show', $worker->id) }}" class="px-2 py-1 rounded bg-zinc-800 hover:bg-zinc-700 text-zinc-300 font-bold">
                                    الملف
                                </a>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-zinc-500 text-center py-6">لا يوجد موظفون ميدانيون مسكنون على هذه الإدارة حالياً.</p>
                    @endforelse
                </div>
            </div>

            <!-- Categories Linked Section -->
            <div class="bg-[#111116] rounded-2xl border border-zinc-800 p-5 shadow-xl">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-zinc-800">
                    <h3 class="text-sm font-black text-white flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                        تصنيفات البلاغات الموجهة للإدارة ({{ $categories->count() }})
                    </h3>
                    <a href="{{ route('ministry.categories.index') }}" class="text-[11px] font-bold text-amber-400 hover:underline">
                        شجرة التصنيفات ←
                    </a>
                </div>

                <div class="space-y-3 max-h-96 overflow-y-auto">
                    @forelse($categories as $cat)
                        <div class="p-3.5 rounded-xl bg-zinc-900/80 border border-zinc-800 flex items-center justify-between gap-3">
                            <div>
                                <h4 class="text-xs font-bold text-white">{{ $cat->name }}</h4>
                                <p class="text-[11px] text-zinc-500">
                                    التصنيف الرئيسي: <strong class="text-zinc-400">{{ $cat->parent?->name ?? 'رئيسي' }}</strong>
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-zinc-800 text-zinc-400 font-mono">
                                    {{ $cat->complaints_count }} بلاغ
                                </span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $cat->is_active ? 'bg-emerald-500/10 text-emerald-400' : 'bg-red-500/10 text-red-400' }}">
                                    {{ $cat->is_active ? 'نشط' : 'معطل' }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-zinc-500 text-center py-6">لا توجد تصنيفات بلاغات مسندة لهذه الإدارة حالياً.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Recent Complaints Table -->
        <div class="bg-[#111116] rounded-2xl border border-zinc-800 p-5 shadow-xl">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-zinc-800">
                <h3 class="text-sm font-black text-white flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-red-600"></span>
                    أحدث البلاغات الواردة للإدارة
                </h3>
                <a href="{{ route('ministry.complaints.index', ['department_id' => $department->id]) }}" class="text-[11px] font-bold text-amber-400 hover:underline">
                    استعراض جميع البلاغات ←
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-right text-xs">
                    <thead>
                        <tr class="text-zinc-500 border-b border-zinc-800">
                            <th class="py-2.5 px-3 font-bold">عنوان البلاغ</th>
                            <th class="py-2.5 px-3 font-bold">التصنيف</th>
                            <th class="py-2.5 px-3 font-bold">مقدم البلاغ</th>
                            <th class="py-2.5 px-3 font-bold text-center">الحالة</th>
                            <th class="py-2.5 px-3 font-bold text-center">الأولوية</th>
                            <th class="py-2.5 px-3 font-bold">التاريخ</th>
                            <th class="py-2.5 px-3 font-bold text-center">الإجراء</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-800/60">
                        @forelse($recentComplaints as $c)
                            <tr class="hover:bg-zinc-900/50 transition">
                                <td class="py-3 px-3 font-bold text-white max-w-xs truncate">{{ $c->title }}</td>
                                <td class="py-3 px-3 text-zinc-400">{{ $c->category?->name ?? 'غير محدد' }}</td>
                                <td class="py-3 px-3 text-zinc-400">{{ $c->citizen?->name ?? 'مواطن' }}</td>
                                <td class="py-3 px-3 text-center">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ in_array($c->status, ['resolved', 'closed']) ? 'bg-emerald-500/10 text-emerald-400' : 'bg-amber-500/10 text-amber-400' }}">
                                        {{ $c->status }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-zinc-800 text-zinc-300">
                                        {{ $c->priority }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 font-mono text-[11px] text-zinc-500">{{ $c->created_at->format('Y/m/d') }}</td>
                                <td class="py-3 px-3 text-center">
                                    <a href="{{ route('ministry.complaints.show', $c->id) }}" class="text-amber-400 hover:text-amber-300 font-bold text-[11px]">
                                        عرض
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-6 text-center text-zinc-500">لا توجد بلاغات مسجلة لهذه الإدارة حتى الآن.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Transfer Logs Section (Incoming / Outgoing) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Incoming -->
            <div class="bg-[#111116] rounded-2xl border border-zinc-800 p-5 shadow-xl">
                <h4 class="text-xs font-black text-white mb-3 flex items-center gap-2">
                    <span class="text-emerald-400">↓</span>
                    سجل التحويلات الواردة للإدارة
                </h4>
                <div class="space-y-2.5">
                    @forelse($incomingTransfers as $tr)
                        <div class="p-3 rounded-xl bg-zinc-900 border border-zinc-800 text-xs">
                            <div class="flex items-center justify-between text-[11px] text-zinc-400 mb-1">
                                <span>محول من: <strong class="text-zinc-200">{{ $tr->fromDepartment?->name ?? 'إدارة سابقة' }}</strong></span>
                                <span class="font-mono text-zinc-500">{{ $tr->created_at?->format('Y/m/d H:i') }}</span>
                            </div>
                            <p class="font-bold text-white truncate">{{ $tr->complaint?->title }}</p>
                            <p class="text-[11px] text-zinc-400 mt-1 italic">«{{ $tr->reason }}»</p>
                        </div>
                    @empty
                        <p class="text-xs text-zinc-500 text-center py-4">لا توجد تحويلات واردة مسجلة.</p>
                    @endforelse
                </div>
            </div>

            <!-- Outgoing -->
            <div class="bg-[#111116] rounded-2xl border border-zinc-800 p-5 shadow-xl">
                <h4 class="text-xs font-black text-white mb-3 flex items-center gap-2">
                    <span class="text-amber-400">↑</span>
                    سجل التحويلات الصادرة من الإدارة
                </h4>
                <div class="space-y-2.5">
                    @forelse($outgoingTransfers as $tr)
                        <div class="p-3 rounded-xl bg-zinc-900 border border-zinc-800 text-xs">
                            <div class="flex items-center justify-between text-[11px] text-zinc-400 mb-1">
                                <span>محول إلى: <strong class="text-zinc-200">{{ $tr->toDepartment?->name ?? 'إدارة أخرى' }}</strong></span>
                                <span class="font-mono text-zinc-500">{{ $tr->created_at?->format('Y/m/d H:i') }}</span>
                            </div>
                            <p class="font-bold text-white truncate">{{ $tr->complaint?->title }}</p>
                            <p class="text-[11px] text-zinc-400 mt-1 italic">«{{ $tr->reason }}»</p>
                        </div>
                    @empty
                        <p class="text-xs text-zinc-500 text-center py-4">لا توجد تحويلات صادرة مسجلة.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Edit Modal Embedded -->
        <div x-show="editModalOpen" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto bg-black/80 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-[#111116] rounded-2xl border border-zinc-800 p-6 max-w-md w-full shadow-2xl relative"
                 @click.away="editModalOpen = false">
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-zinc-800">
                    <h3 class="text-sm font-black text-white flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                        تعديل بيانات الإدارة
                    </h3>
                    <button type="button" @click="editModalOpen = false" class="text-zinc-500 hover:text-white text-xs font-bold">✕</button>
                </div>

                <form method="POST" action="{{ route('ministry.departments.update', $department->id) }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-xs font-bold text-zinc-300 mb-1.5">اسم الإدارة الخدمية</label>
                        <input type="text" name="name" value="{{ old('name', $department->name) }}" required
                               class="w-full py-2.5 px-3.5 rounded-xl bg-zinc-900 border border-zinc-700 text-xs text-white focus:ring-2 focus:ring-amber-400 focus:border-transparent transition">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-zinc-300 mb-1.5">الوصف والاختصاص</label>
                        <textarea name="description" rows="4" required
                                  class="w-full py-2.5 px-3.5 rounded-xl bg-zinc-900 border border-zinc-700 text-xs text-white focus:ring-2 focus:ring-amber-400 focus:border-transparent transition">{{ old('description', $department->description) }}</textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-zinc-800">
                        <button type="button" @click="editModalOpen = false"
                                class="px-4 py-2 rounded-xl bg-zinc-900 border border-zinc-700 text-xs text-zinc-300 font-bold hover:bg-zinc-800 transition">
                            إلغاء
                        </button>
                        <button type="submit"
                                class="px-5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-black font-black text-xs transition shadow-lg shadow-amber-500/20">
                            حفظ التعديلات
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-ministry-layout>


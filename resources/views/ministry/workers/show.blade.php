<x-ministry-layout :title="'ملف الموظف: ' . $user->name" headerTitle="ملف الموظف وسجل المهام الميدانية">
    <!-- Flash Messages -->
    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center gap-3">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-xs font-bold flex items-center gap-3">
            <svg class="w-5 h-5 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="space-y-6" x-data="{ editModalOpen: false, selectedEvidenceImg: null }">
        <!-- Worker Profile Header Card -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-start gap-4">
                <div class="w-16 h-16 rounded-2xl bg-slate-900 text-amber-400 font-black text-2xl flex items-center justify-center shrink-0 shadow-md">
                    {{ mb_substr($user->name, 0, 1) }}
                </div>
                <div>
                    <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-1">
                        <a href="{{ route('ministry.workers.index') }}" class="hover:text-red-600 transition">← العودة لقائمة الموظفين</a>
                        <span>/</span>
                        <span class="text-slate-700">الملف الشخصي والمهام</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-xl font-black text-slate-900">{{ $user->name }}</h1>
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $user->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                            {{ $user->is_active ? 'حساب نشط' : 'حساب معطل ومجمد' }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-3 text-xs">
                        <div>
                            <span class="text-[11px] text-slate-400 block">الإدارة التابع لها:</span>
                            <span class="font-bold text-slate-800">{{ $user->department?->name ?? 'غير محدد' }}</span>
                        </div>
                        <div>
                            <span class="text-[11px] text-slate-400 block">رقم الهاتف:</span>
                            <span class="font-mono font-semibold text-slate-800">{{ $user->phone }}</span>
                        </div>
                        <div>
                            <span class="text-[11px] text-slate-400 block">البريد الإلكتروني:</span>
                            <span class="font-mono text-slate-800">{{ $user->email }}</span>
                        </div>
                        <div>
                            <span class="text-[11px] text-slate-400 block">الرقم الوطني:</span>
                            <span class="font-mono text-slate-800">{{ $user->national_id ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Controls -->
            <div class="flex items-center gap-2 shrink-0">
                <button type="button" @click="editModalOpen = true"
                        class="px-3.5 py-2 rounded-xl bg-amber-50 hover:bg-amber-100 border border-amber-200 text-amber-800 text-xs font-bold transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>تعديل الموظف</span>
                </button>

                <!-- Toggle Active -->
                <form method="POST" action="{{ route('ministry.workers.toggle_active', $user->id) }}" class="inline"
                      onsubmit="return confirm('{{ $user->is_active ? 'هل تريد تعطيل حساب الموظف؟ سيتم إبطال جميع رموزه وجلساته ومنعه من استلام مهام جديدة.' : 'هل تريد تفعيل حساب الموظف؟' }}');">
                    @csrf
                    <button type="submit"
                            class="px-3.5 py-2 rounded-xl text-xs font-bold border transition {{ $user->is_active ? 'bg-slate-100 text-slate-700 hover:bg-slate-200 border-slate-200' : 'bg-emerald-50 text-emerald-800 hover:bg-emerald-100 border-emerald-200' }}">
                        {{ $user->is_active ? 'تعطيل الحساب' : 'إعادة التفعيل' }}
                    </button>
                </form>

                <!-- Demote or Delete -->
                @if($tasksStats['active'] === 0)
                    <form method="POST" action="{{ route('ministry.workers.demote', $user->id) }}" class="inline"
                          onsubmit="return confirm('هل تريد إلغاء صفة الموظف الميداني وإعادته إلى مواطن؟');">
                        @csrf
                        <button type="submit"
                                class="px-3 py-2 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-600 border border-slate-200 text-xs font-bold transition">
                            إلغاء التعيين
                        </button>
                    </form>

                    @if($tasksStats['total'] === 0)
                        <form method="POST" action="{{ route('ministry.workers.destroy', $user->id) }}" class="inline"
                              onsubmit="return confirm('هل أنت متأكد من حذف حساب الموظف نهائياً؟');">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="px-3 py-2 rounded-xl bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 text-xs font-bold transition">
                                حذف نهائي
                            </button>
                        </form>
                    @endif
                @endif
            </div>
        </div>

        <!-- Task Metrics Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs text-center">
                <span class="text-[11px] font-bold text-slate-400 block mb-1">إجمالي المهام المسندة</span>
                <span class="text-2xl font-black text-slate-900 font-mono">{{ $tasksStats['total'] }}</span>
            </div>
            <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs text-center">
                <span class="text-[11px] font-bold text-amber-600 block mb-1">مهام قيد التنفيذ (نشطة)</span>
                <span class="text-2xl font-black text-amber-600 font-mono">{{ $tasksStats['active'] }}</span>
            </div>
            <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs text-center">
                <span class="text-[11px] font-bold text-emerald-600 block mb-1">مهام مكتملة بنجاح</span>
                <span class="text-2xl font-black text-emerald-600 font-mono">{{ $tasksStats['completed'] }}</span>
            </div>
            <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs text-center">
                <span class="text-[11px] font-bold text-rose-600 block mb-1">مهام ملغاة أو متعثرة</span>
                <span class="text-2xl font-black text-rose-600 font-mono">{{ $tasksStats['failed'] }}</span>
            </div>
        </div>

        <!-- Field Assignments History Table -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-black text-slate-900">سجل المهام الميدانية والتكليفات</h2>
                    <p class="text-xs text-slate-500 mt-0.5">تفاصيل جميع المهام التي باشرها أو يباشرها الموظف مع إثباتات الإنجاز.</p>
                </div>
                <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 text-slate-700 font-mono">
                    {{ $tasks->total() }} مهمة
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-right text-xs">
                    <thead>
                        <tr class="bg-slate-50/80 text-slate-500 border-b border-slate-100">
                            <th class="py-3 px-4 font-bold">البلاغ المرتبط</th>
                            <th class="py-3 px-4 font-bold">التصنيف</th>
                            <th class="py-3 px-4 font-bold">الإدارة المعنية</th>
                            <th class="py-3 px-4 font-bold text-center">حالة المهمة</th>
                            <th class="py-3 px-4 font-bold">تاريخ البدء</th>
                            <th class="py-3 px-4 font-bold">تاريخ الإنجاز</th>
                            <th class="py-3 px-4 font-bold">إثبات الإنجاز الميداني</th>
                            <th class="py-3 px-4 font-bold text-center">البلاغ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($tasks as $task)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="py-3.5 px-4">
                                    <p class="font-bold text-slate-900 max-w-xs truncate">{{ $task->complaint?->title ?? 'بلاغ محذوف' }}</p>
                                    @if($task->notes)
                                        <p class="text-[11px] text-slate-500 mt-0.5 italic">تقرير الموظف: {{ Str::limit($task->notes, 60) }}</p>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-slate-600">
                                    {{ $task->complaint?->category?->name ?? '-' }}
                                </td>
                                <td class="py-3.5 px-4 text-slate-600">
                                    {{ $task->complaint?->currentDepartment?->name ?? '-' }}
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if(in_array($task->status, ['pending', 'accepted', 'in_progress']))
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800">
                                            {{ $task->status === 'in_progress' ? 'قيد التنفيذ' : $task->status }}
                                        </span>
                                    @elseif($task->status === 'completed')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">
                                            مكتملة
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800">
                                            {{ $task->status }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 font-mono text-[11px] text-slate-500">
                                    {{ $task->started_at?->format('Y/m/d H:i') ?? '-' }}
                                </td>
                                <td class="py-3.5 px-4 font-mono text-[11px] text-slate-500">
                                    {{ $task->completed_at?->format('Y/m/d H:i') ?? '-' }}
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($task->evidence && $task->evidence->count() > 0)
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            @foreach($task->evidence as $att)
                                                <button type="button" @click="selectedEvidenceImg = '{{ asset('storage/' . $att->file_path) }}'"
                                                        class="w-8 h-8 rounded-lg overflow-hidden border border-slate-200 hover:scale-105 transition" title="عرض صورة الإثبات">
                                                    <img src="{{ asset('storage/' . $att->file_path) }}" alt="إثبات الإنجاز" class="w-full h-full object-cover">
                                                </button>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-[11px] text-slate-400">لا توجد صور</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if($task->complaint)
                                        <a href="{{ route('ministry.complaints.show', $task->complaint->id) }}" class="text-red-600 hover:text-red-700 font-bold text-[11px]">
                                            عرض البلاغ
                                        </a>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-8 text-center text-slate-400">
                                    لا توجد مهام ميدانية مسجلة لهذا الموظف حتى الآن.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($tasks->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $tasks->links() }}
                </div>
            @endif
        </div>

        <!-- Edit Worker Modal Embedded -->
        <div x-show="editModalOpen" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto bg-black/70 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl border border-slate-200 p-6 max-w-lg w-full shadow-2xl relative"
                 @click.away="editModalOpen = false">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                    <h3 class="text-sm font-black text-slate-900 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                        تعديل بيانات الموظف الميداني
                    </h3>
                    <button type="button" @click="editModalOpen = false" class="text-slate-400 hover:text-slate-700 text-xs font-bold">✕</button>
                </div>

                <form method="POST" action="{{ route('ministry.workers.update', $user->id) }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">الاسم الرباعي</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                               class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500 transition">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">البريد الإلكتروني</label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                                   class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500 transition font-mono">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">رقم الهاتف</label>
                            <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" required
                                   class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500 transition font-mono">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">الرقم الوطني</label>
                            <input type="text" name="national_id" value="{{ old('national_id', $user->national_id) }}"
                                   class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500 transition font-mono">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">الإدارة الخدمية المسند إليها</label>
                            <select name="department_id" required class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 text-xs font-bold focus:ring-2 focus:ring-red-500 bg-white">
                                @foreach($departments as $d)
                                    <option value="{{ $d->id }}" {{ (string)$d->id === (string)$user->department_id ? 'selected' : '' }}>
                                        {{ $d->name }}
                                    </option>
                                @endforeach
                            </select>
                            @if($tasksStats['active'] > 0)
                                <p class="text-[10px] text-amber-600 font-bold mt-1">
                                    تنبيه: لدى الموظف مهام نشطة قيد التنفيذ، لذا لا يمكن تغيير إدارته الخدمية حتى إنهاء المهام.
                                </p>
                            @endif
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">تغيير كلمة المرور (اترك الحقل فارغاً إذا كنت لا ترغب في التغيير)</label>
                        <input type="password" name="password" minlength="8" placeholder="كلمة مرور جديدة (اختياري)..."
                               class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500 transition">
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="editModalOpen = false"
                                class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold hover:bg-slate-200 transition">
                            إلغاء
                        </button>
                        <button type="submit"
                                class="px-5 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-black text-xs transition shadow-sm">
                            حفظ التعديلات
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Evidence Image Lightbox Modal -->
        <div x-show="selectedEvidenceImg" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto bg-black/90 backdrop-blur-xs flex items-center justify-center p-4"
             @click="selectedEvidenceImg = null">
            <div class="max-w-3xl w-full p-2 relative" @click.stop>
                <button type="button" @click="selectedEvidenceImg = null" class="absolute -top-3 -right-3 w-8 h-8 rounded-full bg-red-600 text-white font-bold text-sm flex items-center justify-center shadow-lg">✕</button>
                <img :src="selectedEvidenceImg" alt="صورة إثبات الإنجاز" class="w-full h-auto max-h-[80vh] object-contain rounded-xl shadow-2xl">
            </div>
        </div>
    </div>
</x-ministry-layout>


<x-ministry-layout title="الإدارات الخدمية" headerTitle="إدارة وهيكلية الإدارات الخدمية">
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

    @if($errors->any())
        <div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 text-xs font-bold space-y-1">
            @foreach($errors->all() as $error)
                <p>• {{ $error }}</p>
            @endforeach
        </div>
    @endif

    <!-- Top Stats & Ministry Header -->
    <div class="mb-8 p-6 rounded-2xl bg-[#111116] border border-zinc-800 shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-amber-400/10 border border-amber-400/30 text-amber-400 flex items-center justify-center font-black">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>
            <div>
                <span class="text-[11px] font-bold text-zinc-500 uppercase tracking-wider block">الوزارة والجهة المركزية</span>
                <h3 class="text-lg font-black text-white">{{ $ministry->name }}</h3>
                <p class="text-xs text-zinc-400 mt-0.5">{{ $ministry->contact_email }}</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <div class="px-4 py-2 rounded-xl bg-zinc-900 border border-zinc-800 text-center">
                <span class="text-[10px] text-zinc-400 font-bold block">إجمالي الإدارات</span>
                <span class="text-base font-black text-amber-400">{{ $departments->count() }}</span>
            </div>
            <div class="px-4 py-2 rounded-xl bg-zinc-900 border border-zinc-800 text-center">
                <span class="text-[10px] text-zinc-400 font-bold block">إجمالي البلاغات</span>
                <span class="text-base font-black text-red-500">{{ $departments->sum('complaints_count') }}</span>
            </div>
            <div class="px-4 py-2 rounded-xl bg-zinc-900 border border-zinc-800 text-center">
                <span class="text-[10px] text-zinc-400 font-bold block">الكادر الوظيفي</span>
                <span class="text-base font-black text-emerald-400">{{ $departments->sum('users_count') }}</span>
            </div>
        </div>
    </div>

    <!-- Main Grid: Add Department Form & Departments List -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8" x-data="{ editModalOpen: false, editDeptId: null, editDeptName: '', editDeptDescription: '', editActionUrl: '' }">
        <!-- Add Department Form Card -->
        <div class="bg-[#111116] rounded-2xl border border-zinc-800 p-6 shadow-xl h-fit">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-black text-white flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-red-600"></span>
                    إضافة إدارة خدمية فرعية
                </h3>
                <span class="text-[10px] font-bold text-amber-400/90 bg-amber-400/10 px-2 py-0.5 rounded-md border border-amber-400/20">
                    ربط آلي بالوزارة
                </span>
            </div>

            <p class="text-xs text-zinc-400 leading-relaxed mb-4">
                تُربط الإدارة الجديدة تلقائياً بـ <strong class="text-zinc-200">{{ $ministry->name }}</strong>، وتصبح متاحة فوراً لإسناد المهام وربط تصنيفات البلاغات وتوجيه الشكاوى.
            </p>

            <form method="POST" action="{{ route('ministry.departments.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-zinc-300 mb-1.5">اسم الإدارة الخدمية</label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="مثال: إدارة صيانة شبكات الإنارة" 
                           class="w-full py-2.5 px-3.5 rounded-xl bg-zinc-900 border border-zinc-700 text-xs text-white placeholder-zinc-500 focus:ring-2 focus:ring-red-500 focus:border-transparent transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-300 mb-1.5">الوصف والاختصاص</label>
                    <textarea name="description" rows="3" required placeholder="حدد نطاق عمل واختصاص الإدارة الخدمية..." 
                              class="w-full py-2.5 px-3.5 rounded-xl bg-zinc-900 border border-zinc-700 text-xs text-white placeholder-zinc-500 focus:ring-2 focus:ring-red-500 focus:border-transparent transition">{{ old('description') }}</textarea>
                </div>

                <button type="submit" class="w-full py-3 rounded-xl bg-red-600 text-white font-black text-xs hover:bg-red-700 active:scale-[0.99] transition shadow-lg shadow-red-900/20">
                    + تسجيل الإدارة
                </button>
            </form>
        </div>

        <!-- Departments List -->
        <div class="lg:col-span-2 space-y-4">
            <div class="flex items-center justify-between text-xs text-zinc-400 font-bold px-1">
                <span>الإدارات الخدمية المسجلة ({{ $departments->count() }})</span>
                <span>الهيكل الميداني والتشغيلي</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @forelse($departments as $dept)
                    <div class="p-5 rounded-2xl bg-[#111116] border border-zinc-800/90 shadow-md hover:border-zinc-700 transition flex flex-col justify-between">
                        <div>
                            <div class="flex items-start justify-between gap-3 mb-2">
                                <h4 class="text-sm font-bold text-white">{{ $dept->name }}</h4>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold shrink-0 {{ $dept->is_active ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-zinc-800 text-zinc-500 border border-zinc-700' }}">
                                    {{ $dept->is_active ? 'نشطة' : 'معطلة / مؤرشفة' }}
                                </span>
                            </div>
                            <p class="text-xs text-zinc-400 leading-relaxed mb-4 line-clamp-2">
                                {{ $dept->description }}
                            </p>
                        </div>

                        <div>
                            <div class="pt-3 border-t border-zinc-800 flex items-center justify-between text-[11px] mb-4">
                                <span class="text-zinc-400">
                                    <strong class="text-red-500 font-mono">{{ $dept->complaints_count }}</strong> بلاغ
                                </span>
                                <span class="text-zinc-400">
                                    <strong class="text-amber-400 font-mono">{{ $dept->categories_count }}</strong> تصنيف
                                </span>
                                <span class="text-zinc-400">
                                    <strong class="text-emerald-400 font-mono">{{ $dept->users_count }}</strong> موظف
                                </span>
                            </div>

                            <!-- Action Buttons Bar -->
                            <div class="flex items-center justify-between gap-1.5 pt-2 border-t border-zinc-800/60">
                                <div class="flex items-center gap-1.5">
                                    <!-- View Details -->
                                    <a href="{{ route('ministry.departments.show', $dept->id) }}" 
                                       class="px-2.5 py-1.5 rounded-lg bg-zinc-900 hover:bg-zinc-800 border border-zinc-700 text-[11px] font-bold text-zinc-300 hover:text-white transition flex items-center gap-1"
                                       title="عرض التفاصيل والسجلات">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <span>تفاصيل</span>
                                    </a>

                                    <!-- Edit -->
                                    <button type="button" 
                                            @click="editDeptId = {{ $dept->id }}; editDeptName = '{{ addslashes($dept->name) }}'; editDeptDescription = '{{ addslashes($dept->description) }}'; editActionUrl = '{{ route('ministry.departments.update', $dept->id) }}'; editModalOpen = true;"
                                            class="px-2.5 py-1.5 rounded-lg bg-zinc-900 hover:bg-zinc-800 border border-zinc-700 text-[11px] font-bold text-amber-400 hover:text-amber-300 transition flex items-center gap-1"
                                            title="تعديل بيانات الإدارة">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        <span>تعديل</span>
                                    </button>
                                </div>

                                <div class="flex items-center gap-1.5">
                                    <!-- Toggle Active -->
                                    <form method="POST" action="{{ route('ministry.departments.toggle_active', $dept->id) }}" class="inline"
                                          onsubmit="return confirm('{{ $dept->is_active ? 'هل أنت متأكد من تعطيل وأرشفة هذه الإدارة؟ لن تتأثر البلاغات أو السجلات السابقة ولكن سيتم استبعادها من استقبال بلاغات جديدة.' : 'هل تريد إعادة تفعيل هذه الإدارة لتستقبل بلاغات وتكليفات جديدة؟' }}');">
                                        @csrf
                                        <button type="submit" 
                                                class="px-2.5 py-1.5 rounded-lg text-[11px] font-bold border transition {{ $dept->is_active ? 'bg-amber-400/10 text-amber-400 border-amber-400/20 hover:bg-amber-400/20' : 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20 hover:bg-emerald-500/20' }}">
                                            {{ $dept->is_active ? 'تعطيل' : 'تفعيل' }}
                                        </button>
                                    </form>

                                    <!-- Delete (Conditional) -->
                                    @php
                                        $hasRelations = ($dept->complaints_count > 0 || $dept->categories_count > 0 || $dept->users_count > 0 || ($dept->projects_count ?? 0) > 0);
                                    @endphp
                                    <form method="POST" action="{{ route('ministry.departments.destroy', $dept->id) }}" class="inline"
                                          onsubmit="return confirm('هل أنت متأكد من حذف هذه الإدارة نهائياً؟ تنبيه: لن يُسمح بالحذف إلا إذا كانت خالية تماماً من البلاغات والتصنيفات والموظفين.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="px-2.5 py-1.5 rounded-lg text-[11px] font-bold border transition {{ $hasRelations ? 'bg-zinc-900 text-zinc-500 border-zinc-800 hover:text-red-400' : 'bg-red-500/10 text-red-400 border-red-500/20 hover:bg-red-600 hover:text-white' }}"
                                                title="{{ $hasRelations ? 'لا يمكن الحذف لوجود سجلات مرتبطة - استخدم التعطيل' : 'حذف نهائي مسموح' }}">
                                            حذف
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-2 p-10 rounded-2xl bg-[#111116] border border-zinc-800 text-center text-zinc-500 text-xs">
                        لا توجد إدارات خدمية مضافة تحت هذه الوزارة حتى الآن. يمكنك تسجيل الإدارة الأولى أعلاه.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Edit Department Modal -->
        <div x-show="editModalOpen" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto bg-black/80 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-[#111116] rounded-2xl border border-zinc-800 p-6 max-w-md w-full shadow-2xl relative"
                 @click.away="editModalOpen = false">
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-zinc-800">
                    <h3 class="text-sm font-black text-white flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                        تعديل بيانات الإدارة الخدمية
                    </h3>
                    <button type="button" @click="editModalOpen = false" class="text-zinc-500 hover:text-white text-xs font-bold">
                        ✕
                    </button>
                </div>

                <form method="POST" :action="editActionUrl" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-xs font-bold text-zinc-300 mb-1.5">اسم الإدارة الخدمية</label>
                        <input type="text" name="name" x-model="editDeptName" required
                               class="w-full py-2.5 px-3.5 rounded-xl bg-zinc-900 border border-zinc-700 text-xs text-white focus:ring-2 focus:ring-amber-400 focus:border-transparent transition">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-zinc-300 mb-1.5">الوصف والاختصاص</label>
                        <textarea name="description" rows="4" x-model="editDeptDescription" required
                                  class="w-full py-2.5 px-3.5 rounded-xl bg-zinc-900 border border-zinc-700 text-xs text-white focus:ring-2 focus:ring-amber-400 focus:border-transparent transition"></textarea>
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

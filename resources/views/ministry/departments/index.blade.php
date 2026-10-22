<x-ministry-layout title="الإدارات الخدمية" headerTitle="إدارة وهيكلية الإدارات الخدمية">
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
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
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
                    <input type="text" name="name" required placeholder="مثال: إدارة صيانة شبكات الإنارة" 
                           class="w-full py-2.5 px-3.5 rounded-xl bg-zinc-900 border border-zinc-700 text-xs text-white placeholder-zinc-500 focus:ring-2 focus:ring-red-500 focus:border-transparent transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-300 mb-1.5">الوصف والاختصاص</label>
                    <textarea name="description" rows="3" required placeholder="حدد نطاق عمل واختصاص الإدارة الخدمية..." 
                              class="w-full py-2.5 px-3.5 rounded-xl bg-zinc-900 border border-zinc-700 text-xs text-white placeholder-zinc-500 focus:ring-2 focus:ring-red-500 focus:border-transparent transition"></textarea>
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
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold shrink-0 {{ $dept->is_active ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-zinc-800 text-zinc-500' }}">
                                    {{ $dept->is_active ? 'نشطة' : 'معطلة' }}
                                </span>
                            </div>
                            <p class="text-xs text-zinc-400 leading-relaxed mb-4 line-clamp-2">
                                {{ $dept->description }}
                            </p>
                        </div>

                        <div class="pt-3 border-t border-zinc-800 flex items-center justify-between text-[11px]">
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
                    </div>
                @empty
                    <div class="col-span-2 p-10 rounded-2xl bg-[#111116] border border-zinc-800 text-center text-zinc-500 text-xs">
                        لا توجد إدارات خدمية مضافة تحت هذه الوزارة حتى الآن. يمكنك تسجيل الإدارة الأولى أعلاه.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-ministry-layout>

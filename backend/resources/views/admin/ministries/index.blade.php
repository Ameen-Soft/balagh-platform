<x-admin-layout title="الوزارات والإدارات الحكومية" headerTitle="إدارة الهيكل المؤسسي والوزارات">
    <!-- Top Action Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8" x-data="{ addMinistry: false, addDepartment: false }">
        <!-- Add Ministry Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs">
            <h3 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-red-600"></span>
                إضافة وزارة أو جهة مركزية جديدة
            </h3>
            <form method="POST" action="{{ route('admin.ministries.store') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">اسم الوزارة</label>
                    <input type="text" name="name" required placeholder="مثال: وزارة الأشغال العامة والطرق" 
                           class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">الرمز التعريفي (Code)</label>
                    <input type="text" name="code" required placeholder="مثال: MPW" 
                           class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">البريد الإلكتروني الرسمي</label>
                    <input type="email" name="contact_email" required placeholder="contact@mpw.gov.ye" 
                           class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                </div>
                <button type="submit" class="w-full py-2.5 rounded-xl bg-red-600 text-white font-bold text-xs hover:bg-red-700 transition">
                    + تسجيل الوزارة
                </button>
            </form>
        </div>

        <!-- Add Department Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs">
            <h3 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-black"></span>
                إضافة إدارة خدمية فرعية
            </h3>
            <form method="POST" action="{{ route('admin.departments.store') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">الوزارة التابعة</label>
                    <select name="ministry_id" required class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                        <option value="">اختر الوزارة...</option>
                        @foreach($ministries as $min)
                            <option value="{{ $min->id }}">{{ $min->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">اسم الإدارة الخدمية</label>
                    <input type="text" name="name" required placeholder="مثال: إدارة صيانة الطرق والجسور" 
                           class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">الوصف والاختصاص</label>
                    <textarea name="description" rows="2" required placeholder="وصف مهام واختصاص الإدارة..." 
                              class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500"></textarea>
                </div>
                <button type="submit" class="w-full py-2.5 rounded-xl bg-black text-white font-bold text-xs hover:bg-zinc-800 transition">
                    + تسجيل الإدارة
                </button>
            </form>
        </div>

        <!-- Structure Overview Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs flex flex-col justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900 mb-2">إحصائيات الهيكل المؤسسي</h3>
                <p class="text-xs text-slate-500 leading-relaxed mb-6">
                    الربط المؤسسي بين الوزارات والإدارات يحدد التوجيه الآلي للبلاغات وصلاحيات مشرفي الجهات في معالجة الشكاوى.
                </p>
                <div class="space-y-3">
                    <div class="flex justify-between items-center text-xs p-3 rounded-xl bg-slate-50 border border-slate-200/60">
                        <span class="font-bold text-slate-700">إجمالي الوزارات المركزية:</span>
                        <span class="font-black text-red-600 text-sm">{{ $ministries->count() }}</span>
                    </div>
                    <div class="flex justify-between items-center text-xs p-3 rounded-xl bg-slate-50 border border-slate-200/60">
                        <span class="font-bold text-slate-700">إجمالي الإدارات الخدمية:</span>
                        <span class="font-black text-slate-900 text-sm">{{ $ministries->sum(fn($m) => $m->departments->count()) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ministries Directory -->
    <div class="space-y-6">
        @foreach($ministries as $ministry)
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-red-50 text-red-600 border border-red-200 flex items-center justify-center font-bold">
                            {{ $ministry->code }}
                        </div>
                        <div>
                            <h4 class="text-base font-bold text-slate-900">{{ $ministry->name }}</h4>
                            <p class="text-xs text-slate-500">{{ $ministry->description ?? 'لا يوجد وصف مدخل' }}</p>
                        </div>
                    </div>
                    <span class="text-xs font-bold text-slate-400">
                        {{ $ministry->departments->count() }} إدارة خدمية
                    </span>
                </div>

                <!-- Departments under this ministry -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @forelse($ministry->departments as $dept)
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center justify-between">
                            <div>
                                <p class="text-xs font-bold text-slate-800">{{ $dept->name }}</p>
                                <span class="text-[10px] font-mono text-slate-400">{{ $dept->code }}</span>
                            </div>
                            <div class="text-left">
                                <span class="text-[11px] font-bold text-slate-600 block">{{ $dept->complaints->count() }} بلاغ</span>
                                <span class="text-[10px] text-slate-400 block">{{ $dept->users->count() }} موظف</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 col-span-3">لا توجد إدارات مسجلة تحت هذه الوزارة بعد.</p>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
</x-admin-layout>

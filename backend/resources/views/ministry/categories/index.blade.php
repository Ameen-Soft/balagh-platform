<x-ministry-layout title="تصنيفات البلاغات" headerTitle="إدارة تصنيفات البلاغات الخاصة بالوزارة">
    <!-- Top Stats & Ministry Header -->
    <div class="mb-8 p-6 rounded-2xl bg-[#111116] border border-zinc-800 shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-red-600/10 border border-red-500/30 text-red-500 flex items-center justify-center font-black">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
            </div>
            <div>
                <span class="text-[11px] font-bold text-zinc-500 uppercase tracking-wider block">تصنيفات البلاغات التابعة لـ</span>
                <h3 class="text-lg font-black text-white">{{ $ministry->name }}</h3>
                <p class="text-xs text-zinc-400 mt-0.5">التصنيفات المعتمدة للتوجيه الآلي للشكاوى نحو إدارات الوزارة</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <div class="px-4 py-2 rounded-xl bg-zinc-900 border border-zinc-800 text-center">
                <span class="text-[10px] text-zinc-400 font-bold block">إجمالي التصنيفات</span>
                <span class="text-base font-black text-white">{{ $categories->total() }}</span>
            </div>
            <div class="px-4 py-2 rounded-xl bg-zinc-900 border border-zinc-800 text-center">
                <span class="text-[10px] text-zinc-400 font-bold block">تصنيفات رئيسية</span>
                <span class="text-base font-black text-amber-400">{{ $parentCategories->count() }}</span>
            </div>
            <div class="px-4 py-2 rounded-xl bg-zinc-900 border border-zinc-800 text-center">
                <span class="text-[10px] text-zinc-400 font-bold block">إجمالي البلاغات</span>
                <span class="text-base font-black text-red-500">{{ $categories->sum('complaints_count') }}</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Add Category Form Card -->
        <div class="bg-[#111116] rounded-2xl border border-zinc-800 p-6 shadow-xl h-fit">
            <h3 class="text-sm font-black text-white mb-4 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-red-600"></span>
                إضافة تصنيف بلاغ جديد
            </h3>

            <p class="text-xs text-zinc-400 leading-relaxed mb-4">
                يحدد التصنيف الإدارة الخدمية المسؤولة عن معالجة البلاغ تلقائياً بمجرد تقديمه من المواطن.
            </p>

            <form method="POST" action="{{ route('ministry.categories.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-zinc-300 mb-1.5">اسم التصنيف</label>
                    <input type="text" name="name" required placeholder="مثال: تسرب في شبكة الصرف الصحي" 
                           class="w-full py-2.5 px-3.5 rounded-xl bg-zinc-900 border border-zinc-700 text-xs text-white placeholder-zinc-500 focus:ring-2 focus:ring-red-500 focus:border-transparent transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-300 mb-1.5">الإدارة الخدمية المسؤولة تلقائياً (Routing)</label>
                    <select name="department_id" required class="w-full py-2.5 px-3.5 rounded-xl bg-zinc-900 border border-zinc-700 text-xs text-white focus:ring-2 focus:ring-red-500 focus:border-transparent transition">
                        <option value="">اختر الإدارة المختصة...</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                    <p class="text-[10px] text-zinc-500 mt-1">تقتصر القائمة على الإدارات التابعة لوزارتك فقط.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-300 mb-1.5">التصنيف الرئيسي (اختياري للتصنيف الفرعي)</label>
                    <select name="parent_id" class="w-full py-2.5 px-3.5 rounded-xl bg-zinc-900 border border-zinc-700 text-xs text-white focus:ring-2 focus:ring-red-500 focus:border-transparent transition">
                        <option value="">تصنيف رئيسي (مستوى أول)</option>
                        @foreach($parentCategories as $parent)
                            <option value="{{ $parent->id }}">{{ $parent->name }} ({{ $parent->department?->name }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-300 mb-1.5">الوصف والإرشادات</label>
                    <textarea name="description" rows="3" placeholder="توضيح الحالات التي يشملها هذا التصنيف للمواطنين..." 
                              class="w-full py-2.5 px-3.5 rounded-xl bg-zinc-900 border border-zinc-700 text-xs text-white placeholder-zinc-500 focus:ring-2 focus:ring-red-500 focus:border-transparent transition"></textarea>
                </div>

                <button type="submit" class="w-full py-3 rounded-xl bg-red-600 text-white font-black text-xs hover:bg-red-700 active:scale-[0.99] transition shadow-lg shadow-red-900/20">
                    + إضافة التصنيف
                </button>
            </form>
        </div>

        <!-- Categories Table Card -->
        <div class="lg:col-span-2 bg-[#111116] rounded-2xl border border-zinc-800 shadow-xl overflow-hidden">
            <div class="p-4 border-b border-zinc-800/80 flex items-center justify-between text-xs text-zinc-400 font-bold">
                <span>تصنيفات البلاغات المسجلة ({{ $categories->total() }})</span>
                <span>التوجيه الآلي للشكاوى</span>
            </div>

            <!-- Desktop View -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-right text-xs">
                    <thead class="bg-zinc-900/60 text-zinc-400 uppercase tracking-wider font-bold border-b border-zinc-800">
                        <tr>
                            <th class="py-3.5 px-5">اسم التصنيف</th>
                            <th class="py-3.5 px-5">المستوى</th>
                            <th class="py-3.5 px-5">الإدارة المسؤولة</th>
                            <th class="py-3.5 px-5">البلاغات المسجلة</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-800/60 font-medium">
                        @forelse($categories as $cat)
                            <tr class="hover:bg-zinc-900/40 transition">
                                <td class="py-4 px-5 text-white font-bold">
                                    {{ $cat->name }}
                                    @if($cat->parent)
                                        <span class="block text-[10px] text-zinc-500 font-normal">تابع لـ: {{ $cat->parent->name }}</span>
                                    @endif
                                </td>
                                <td class="py-4 px-5 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $cat->level == 1 ? 'bg-amber-400/10 text-amber-300 border border-amber-400/20' : 'bg-zinc-800 text-zinc-300' }}">
                                        {{ $cat->level == 1 ? 'رئيسي' : 'فرعي' }}
                                    </span>
                                </td>
                                <td class="py-4 px-5 text-zinc-300">
                                    <p class="font-bold text-white">{{ $cat->department?->name ?? 'غير محدد' }}</p>
                                </td>
                                <td class="py-4 px-5">
                                    <span class="font-bold text-red-500 font-mono">{{ $cat->complaints_count }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-10 text-zinc-500">لا توجد تصنيفات مضافة لهذه الوزارة حتى الآن.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile View -->
            <div class="block md:hidden divide-y divide-zinc-800 p-3 space-y-3">
                @forelse($categories as $cat)
                    <div class="p-4 rounded-xl bg-zinc-900/60 border border-zinc-800 space-y-2">
                        <div class="flex items-center justify-between gap-2">
                            <span class="font-bold text-xs text-white">{{ $cat->name }}</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $cat->level == 1 ? 'bg-amber-400/10 text-amber-300 border border-amber-400/20' : 'bg-zinc-800 text-zinc-300' }}">
                                {{ $cat->level == 1 ? 'رئيسي' : 'فرعي' }}
                            </span>
                        </div>

                        <div class="text-xs text-zinc-400">
                            <p><span class="text-zinc-500">الإدارة:</span> {{ $cat->department?->name ?? 'غير محدد' }}</p>
                            @if($cat->parent)
                                <p class="text-[11px] text-zinc-500 mt-0.5">تابع لـ: {{ $cat->parent->name }}</p>
                            @endif
                        </div>

                        <div class="pt-2 border-t border-zinc-800/80 flex items-center justify-between text-xs">
                            <span class="text-zinc-500">البلاغات المسجلة:</span>
                            <span class="font-bold text-red-500 font-mono">{{ $cat->complaints_count }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-zinc-500 text-center py-6">لا توجد تصنيفات مضافة حالياً.</p>
                @endforelse
            </div>

            <div class="p-4 border-t border-zinc-800/80">
                {{ $categories->links() }}
            </div>
        </div>
    </div>
</x-ministry-layout>

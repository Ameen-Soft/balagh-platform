<x-admin-layout title="تصنيفات البلاغات والشكاوى" headerTitle="إدارة تصنيفات البلاغات والربط بالجهات">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Add Category Form -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs h-fit">
            <h3 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-red-600"></span>
                إضافة تصنيف بلاغ جديد
            </h3>
            <form method="POST" action="{{ route('admin.categories.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">اسم التصنيف</label>
                    <input type="text" name="name" required placeholder="مثال: حفر وانهيارات الأسفلت" 
                           class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">الإدارة المسؤولة تلقائياً (Routing)</label>
                    <select name="department_id" required class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                        <option value="">اختر الإدارة الخدمية المختصة...</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->ministry?->name }} - {{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">التصنيف الرئيسي (اختياري للتصنيف الفرعي)</label>
                    <select name="parent_id" class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                        <option value="">تصنيف رئيسي (مستوى أول)</option>
                        @foreach($parentCategories as $parent)
                            <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">الوصف والإرشادات</label>
                    <textarea name="description" rows="3" placeholder="توضيح الحالات التي يشملها هذا التصنيف..." 
                              class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500"></textarea>
                </div>

                <button type="submit" class="w-full py-2.5 rounded-xl bg-red-600 text-white font-bold text-xs hover:bg-red-700 transition">
                    + إضافة التصنيف
                </button>
            </form>
        </div>

        <!-- Categories Table -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between text-xs text-slate-500 font-bold">
                <span>إجمالي التصنيفات: {{ $categories->total() }}</span>
                <span>التوجيه الآلي للبلاغات</span>
            </div>

            <!-- Desktop Table View -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-right text-xs">
                    <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-bold border-b border-slate-200/80">
                        <tr>
                            <th class="py-3.5 px-6">اسم التصنيف</th>
                            <th class="py-3.5 px-6">المستوى</th>
                            <th class="py-3.5 px-6">الإدارة المسؤولة</th>
                            <th class="py-3.5 px-6">البلاغات المسجلة</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse($categories as $cat)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-4 px-6 text-slate-900 font-bold">
                                    {{ $cat->name }}
                                    @if($cat->parent)
                                        <span class="block text-[10px] text-slate-400 font-normal">تابع لـ: {{ $cat->parent->name }}</span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $cat->level == 1 ? 'bg-black text-white' : 'bg-zinc-100 text-zinc-700' }}">
                                        {{ $cat->level == 1 ? 'رئيسي' : 'فرعي' }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-slate-600">
                                    <p class="font-bold text-slate-800">{{ $cat->department?->name ?? 'غير محدد' }}</p>
                                    <p class="text-[10px] text-slate-400">{{ $cat->department?->ministry?->name }}</p>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="font-bold text-red-600 font-mono">{{ $cat->complaints_count }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-8 text-slate-400">لا توجد تصنيفات مضافة حالياً.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div class="block md:hidden divide-y divide-slate-100 p-3.5 space-y-3">
                @forelse($categories as $cat)
                    <div class="p-4 rounded-2xl bg-slate-50/90 border border-slate-200/80 space-y-2">
                        <div class="flex items-center justify-between gap-2">
                            <span class="font-bold text-xs text-slate-900">{{ $cat->name }}</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $cat->level == 1 ? 'bg-black text-white' : 'bg-zinc-100 text-zinc-700' }}">
                                {{ $cat->level == 1 ? 'رئيسي' : 'فرعي' }}
                            </span>
                        </div>

                        <div class="text-xs text-slate-600 space-y-1">
                            <p class="font-medium text-slate-800">
                                <span class="text-slate-400">الإدارة:</span> {{ $cat->department?->name ?? 'غير محدد' }} ({{ $cat->department?->ministry?->name }})
                            </p>
                            @if($cat->parent)
                                <p class="text-[11px] text-slate-400">تابع لتصنيف: {{ $cat->parent->name }}</p>
                            @endif
                        </div>

                        <div class="pt-2 border-t border-slate-200/60 flex items-center justify-between text-xs">
                            <span class="text-slate-500">إجمالي البلاغات المسجلة:</span>
                            <span class="font-bold text-red-600 font-mono">{{ $cat->complaints_count }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 text-center py-6">لا توجد تصنيفات مضافة حالياً.</p>
                @endforelse
            </div>

            <div class="p-4 border-t border-slate-100">
                {{ $categories->links() }}
            </div>
        </div>
    </div>
</x-admin-layout>

<x-admin-layout title="تصنيفات البلاغات والشكاوى" headerTitle="إدارة تصنيفات البلاغات والربط بالجهات">
    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-700 text-xs font-bold flex items-center justify-between">
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-700 text-xs font-bold flex items-center justify-between">
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-700 text-xs font-bold space-y-1">
            @foreach($errors->all() as $err)
                <p>• {{ $err }}</p>
            @endforeach
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Add Category Form Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs h-fit" x-data="{ isSub: false, selectedParent: '', parentMinId: '' }">
            <h3 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-red-600"></span>
                إضافة تصنيف جديد
            </h3>

            <div class="flex gap-2 p-1 bg-slate-100 rounded-xl mb-4 text-xs font-bold">
                <button type="button" @click="isSub = false" :class="!isSub ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500'" class="flex-1 py-1.5 rounded-lg transition text-center">
                    تصنيف رئيسي (قطاع عام)
                </button>
                <button type="button" @click="isSub = true" :class="isSub ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500'" class="flex-1 py-1.5 rounded-lg transition text-center">
                    تصنيف فرعي (نوع المشكلة)
                </button>
            </div>

            <form method="POST" action="{{ route('admin.categories.store') }}" class="space-y-4">
                @csrf

                <!-- Main Category Mode -->
                <template x-if="!isSub">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">الوزارة التابع لها التصنيف <span class="text-red-500">*</span></label>
                            <select name="ministry_id" required class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                                <option value="">اختر الوزارة أو الجهة المختصة...</option>
                                @foreach($ministries as $min)
                                    <option value="{{ $min->id }}">{{ $min->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">اسم التصنيف الرئيسي (القطاع) <span class="text-red-500">*</span></label>
                            <input type="text" name="name" required placeholder="مثال: البنية التحتية وشبكة الطرق" 
                                   class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                        </div>
                    </div>
                </template>

                <!-- Subcategory Mode -->
                <template x-if="isSub">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">التصنيف الرئيسي (الأب) <span class="text-red-500">*</span></label>
                            <select name="parent_id" required class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500"
                                    x-model="selectedParent"
                                    @change="parentMinId = $event.target.options[$event.target.selectedIndex].dataset.min">
                                <option value="">اختر التصنيف الرئيسي...</option>
                                @foreach($mainCategories as $parent)
                                    <option value="{{ $parent->id }}" data-min="{{ $parent->ministry_id }}">
                                        {{ $parent->ministry?->name }} — {{ $parent->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">الإدارة الخدمية المسؤولة (التوجيه الآلي) <span class="text-red-500">*</span></label>
                            <select name="department_id" required class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                                <option value="">اختر الإدارة الخدمية المختصة...</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" x-show="!parentMinId || parentMinId == '{{ $dept->ministry_id }}'">
                                        {{ $dept->ministry?->name }} — {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-[10px] text-slate-400 mt-1">يجب أن تتبع الإدارة نفس وزارة التصنيف الرئيسي.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">اسم نوع المشكلة (التصنيف الفرعي) <span class="text-red-500">*</span></label>
                            <input type="text" name="name" required placeholder="مثال: حفر وتشققات إسفلتية خطرة" 
                                   class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                        </div>
                    </div>
                </template>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">الوصف والإرشادات</label>
                    <textarea name="description" rows="3" placeholder="توضيح الحالات التي يشملها هذا التصنيف للمواطنين..." 
                              class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500"></textarea>
                </div>

                <button type="submit" class="w-full py-2.5 rounded-xl bg-red-600 text-white font-bold text-xs hover:bg-red-700 transition">
                    + إضافة التصنيف
                </button>
            </form>
        </div>

        <!-- Tree View & Listing -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h4 class="text-sm font-bold text-slate-900">شجرة تصنيفات البلاغات والتوجيه الآلي</h4>
                    <p class="text-xs text-slate-500 mt-0.5">تنظيم هرمي يربط القطاع بالوزارة، ويوجّه المشكلات الفرعية للإدارة المختصة.</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-3 py-1 rounded-lg bg-slate-100 text-slate-700 font-bold text-xs">
                        رئيسية: {{ $mainCategories->count() }}
                    </span>
                    <span class="px-3 py-1 rounded-lg bg-red-50 text-red-700 font-bold text-xs">
                        إجمالي التصنيفات: {{ $categories->total() }}
                    </span>
                </div>
            </div>

            <!-- Main Categories Tree Cards -->
            <div class="space-y-4">
                @forelse($mainCategories as $main)
                    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden" x-data="{ expanded: true, editOpen: false }">
                        <!-- Main Category Header -->
                        <div class="p-4 bg-slate-50/80 border-b border-slate-200/80 flex flex-wrap items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <button type="button" @click="expanded = !expanded" class="p-1.5 rounded-lg bg-white border border-slate-200 text-slate-600 hover:text-slate-900">
                                    <svg class="w-4 h-4 transform transition-transform" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-slate-900 text-sm">{{ $main->name }}</span>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-900 text-white">رئيسي</span>
                                        @if(!$main->is_active)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">معطل / مؤرشف</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">نشط</span>
                                        @endif
                                    </div>
                                    <span class="text-xs text-slate-500 font-medium">{{ $main->ministry?->name }}</span>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-xs font-mono font-bold text-slate-700">
                                    إجمالي البلاغات: <strong class="text-red-600">{{ $main->total_complaints_count }}</strong>
                                </span>

                                <!-- Toggle Active -->
                                <form method="POST" action="{{ route('admin.categories.toggle_active', $main) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="px-2.5 py-1 rounded-lg text-xs font-bold border transition {{ $main->is_active ? 'border-amber-300 text-amber-700 hover:bg-amber-50' : 'border-emerald-300 text-emerald-700 hover:bg-emerald-50' }}">
                                        {{ $main->is_active ? 'تعطيل' : 'تفعيل' }}
                                    </button>
                                </form>

                                <!-- Edit Trigger -->
                                <button type="button" @click="editOpen = !editOpen" class="px-2.5 py-1 rounded-lg text-xs font-bold border border-slate-300 text-slate-700 hover:bg-slate-100">
                                    تعديل
                                </button>

                                <!-- Delete -->
                                <form method="POST" action="{{ route('admin.categories.destroy', $main) }}" onsubmit="return confirm('هل أنت متأكد من رغبتك في حذف هذا التصنيف؟ لن يتم الحذف إذا ارتبطت به بلاغات أو تصنيفات فرعية.');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1 rounded-lg text-xs font-bold border border-red-200 text-red-600 hover:bg-red-50">
                                        حذف
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Edit Inline Box -->
                        <div x-show="editOpen" x-cloak class="p-4 bg-amber-50/40 border-b border-amber-200/60">
                            <form method="POST" action="{{ route('admin.categories.update', $main) }}" class="space-y-3">
                                @csrf
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">اسم التصنيف الرئيسي</label>
                                        <input type="text" name="name" value="{{ $main->name }}" required class="w-full py-1.5 px-3 rounded-lg border border-slate-300 text-xs">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">الوصف والإرشادات</label>
                                        <input type="text" name="description" value="{{ $main->description }}" class="w-full py-1.5 px-3 rounded-lg border border-slate-300 text-xs">
                                    </div>
                                </div>
                                <div class="flex justify-end gap-2">
                                    <button type="button" @click="editOpen = false" class="px-3 py-1 text-xs text-slate-600 font-bold">إلغاء</button>
                                    <button type="submit" class="px-4 py-1 rounded-lg bg-slate-900 text-white text-xs font-bold">حفظ التعديل</button>
                                </div>
                            </form>
                        </div>

                        <!-- Subcategories Table -->
                        <div x-show="expanded" class="divide-y divide-slate-100">
                            @if($main->children->isEmpty())
                                <p class="p-4 text-center text-xs text-slate-400">لا توجد تصنيفات فرعية مضافة تحت هذا القطاع حتى الآن.</p>
                            @else
                                <div class="overflow-x-auto">
                                    <table class="w-full text-right text-xs">
                                        <thead class="bg-slate-50 text-slate-500 font-bold">
                                            <tr>
                                                <th class="py-2.5 px-5">نوع المشكلة (التصنيف الفرعي)</th>
                                                <th class="py-2.5 px-5">الإدارة المسؤولة (التوجيه الآلي)</th>
                                                <th class="py-2.5 px-5">الحالة</th>
                                                <th class="py-2.5 px-5">البلاغات</th>
                                                <th class="py-2.5 px-5 text-left">إجراءات</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100">
                                            @foreach($main->children as $sub)
                                                <tr class="hover:bg-slate-50/60" x-data="{ subEditOpen: false }">
                                                    <td class="py-3 px-5 font-bold text-slate-800">
                                                        {{ $sub->name }}
                                                        @if($sub->description)
                                                            <span class="block text-[10px] text-slate-400 font-normal">{{ $sub->description }}</span>
                                                        @endif
                                                    </td>
                                                    <td class="py-3 px-5 text-slate-600 font-medium">
                                                        {{ $sub->department?->name ?? 'غير محدد' }}
                                                    </td>
                                                    <td class="py-3 px-5">
                                                        @if($sub->is_active)
                                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700">نشط</span>
                                                        @else
                                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700">معطل</span>
                                                        @endif
                                                    </td>
                                                    <td class="py-3 px-5 font-mono font-bold text-red-600">
                                                        {{ $sub->complaints_count }}
                                                    </td>
                                                    <td class="py-3 px-5 text-left whitespace-nowrap">
                                                        <div class="flex items-center justify-end gap-1.5">
                                                            <!-- Toggle Active -->
                                                            <form method="POST" action="{{ route('admin.categories.toggle_active', $sub) }}" class="inline">
                                                                @csrf
                                                                <button type="submit" class="px-2 py-0.5 text-[11px] font-bold rounded border {{ $sub->is_active ? 'border-amber-200 text-amber-700' : 'border-emerald-200 text-emerald-700' }}">
                                                                    {{ $sub->is_active ? 'تعطيل' : 'تفعيل' }}
                                                                </button>
                                                            </form>

                                                            <!-- Edit Trigger -->
                                                            <button type="button" @click="subEditOpen = !subEditOpen" class="px-2 py-0.5 text-[11px] font-bold rounded border border-slate-200 text-slate-700">
                                                                تعديل
                                                            </button>

                                                            <!-- Delete -->
                                                            <form method="POST" action="{{ route('admin.categories.destroy', $sub) }}" onsubmit="return confirm('هل أنت متأكد من حذف هذا التصنيف الفرعي؟');" class="inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="px-2 py-0.5 text-[11px] font-bold rounded border border-red-200 text-red-600 hover:bg-red-50">
                                                                    حذف
                                                                </button>
                                                            </form>
                                                        </div>

                                                        <!-- Subcategory Inline Edit -->
                                                        <div x-show="subEditOpen" x-cloak class="mt-2 p-3 bg-amber-50 rounded-xl border border-amber-200 text-right space-y-2">
                                                            <form method="POST" action="{{ route('admin.categories.update', $sub) }}" class="space-y-2">
                                                                @csrf
                                                                <div>
                                                                    <label class="block text-[10px] font-bold text-slate-700">اسم التصنيف الفرعي</label>
                                                                    <input type="text" name="name" value="{{ $sub->name }}" required class="w-full py-1 px-2 rounded border border-slate-300 text-xs">
                                                                </div>
                                                                <div>
                                                                    <label class="block text-[10px] font-bold text-slate-700">الإدارة الخدمية المسؤولة</label>
                                                                    <select name="department_id" required class="w-full py-1 px-2 rounded border border-slate-300 text-xs">
                                                                        @foreach($departments as $dept)
                                                                            @if($dept->ministry_id === $main->ministry_id)
                                                                                <option value="{{ $dept->id }}" {{ $sub->department_id == $dept->id ? 'selected' : '' }}>
                                                                                    {{ $dept->name }}
                                                                                </option>
                                                                            @endif
                                                                        @endforeach
                                                                    </select>
                                                                    <p class="text-[9px] text-amber-800">تغيير الإدارة يسري على البلاغات الجديدة فقط.</p>
                                                                </div>
                                                                <div class="flex justify-end gap-1.5 pt-1">
                                                                    <button type="button" @click="subEditOpen = false" class="px-2 py-0.5 text-[10px] text-slate-600 font-bold">إلغاء</button>
                                                                    <button type="submit" class="px-3 py-0.5 rounded bg-slate-900 text-white text-[10px] font-bold">حفظ</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center bg-white rounded-2xl border border-slate-200/80 text-slate-400">
                        لا توجد تصنيفات رئيسية مضافة حالياً.
                    </div>
                @endforelse
            </div>

            <div class="p-4 bg-white rounded-2xl border border-slate-200/80 shadow-xs">
                {{ $categories->links() }}
            </div>
        </div>
    </div>
</x-admin-layout>

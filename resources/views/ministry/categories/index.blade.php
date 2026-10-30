<x-ministry-layout title="تصنيفات البلاغات" headerTitle="إدارة تصنيفات البلاغات الخاصة بالوزارة">
    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-bold flex items-center justify-between">
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-xs font-bold flex items-center justify-between">
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-xs font-bold space-y-1">
            @foreach($errors->all() as $err)
                <p>• {{ $err }}</p>
            @endforeach
        </div>
    @endif

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
                <p class="text-xs text-zinc-400 mt-0.5">التصنيفات المعتمدة للتوجيه الآلي للشكاوى نحو إدارات الوزارة الخدمية</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <div class="px-4 py-2 rounded-xl bg-zinc-900 border border-zinc-800 text-center">
                <span class="text-[10px] text-zinc-400 font-bold block">إجمالي التصنيفات</span>
                <span class="text-base font-black text-white">{{ $categories->total() }}</span>
            </div>
            <div class="px-4 py-2 rounded-xl bg-zinc-900 border border-zinc-800 text-center">
                <span class="text-[10px] text-zinc-400 font-bold block">تصنيفات رئيسية</span>
                <span class="text-base font-black text-amber-400">{{ $mainCategories->count() }}</span>
            </div>
            <div class="px-4 py-2 rounded-xl bg-zinc-900 border border-zinc-800 text-center">
                <span class="text-[10px] text-zinc-400 font-bold block">إجمالي بلاغات الوزارة</span>
                <span class="text-base font-black text-red-500 font-mono">{{ $totalMinistryComplaints }}</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Add Category Form Card -->
        <div class="bg-[#111116] rounded-2xl border border-zinc-800 p-6 shadow-xl h-fit" x-data="{ isSub: false }">
            <h3 class="text-sm font-black text-white mb-4 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-red-600"></span>
                إضافة تصنيف جديد
            </h3>

            <div class="flex gap-2 p-1 bg-zinc-900 rounded-xl mb-4 text-xs font-bold border border-zinc-800">
                <button type="button" @click="isSub = false" :class="!isSub ? 'bg-zinc-800 text-white shadow-xs' : 'text-zinc-500'" class="flex-1 py-1.5 rounded-lg transition text-center">
                    تصنيف رئيسي (قطاع)
                </button>
                <button type="button" @click="isSub = true" :class="isSub ? 'bg-zinc-800 text-white shadow-xs' : 'text-zinc-500'" class="flex-1 py-1.5 rounded-lg transition text-center">
                    تصنيف فرعي (مشكلة)
                </button>
            </div>

            <form method="POST" action="{{ route('ministry.categories.store') }}" class="space-y-4">
                @csrf

                <!-- Main Category Mode -->
                <template x-if="!isSub">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-zinc-300 mb-1.5">اسم التصنيف الرئيسي (القطاع) <span class="text-red-500">*</span></label>
                            <input type="text" name="name" required placeholder="مثال: البنية التحتية وشبكة الطرق" 
                                   class="w-full py-2.5 px-3.5 rounded-xl bg-zinc-900 border border-zinc-700 text-xs text-white placeholder-zinc-500 focus:ring-2 focus:ring-red-500 transition">
                            <p class="text-[10px] text-zinc-500 mt-1">يمثل قطاعاً عاماً يتبع الوزارة مباشرة دون حصره في إدارة واحدة.</p>
                        </div>
                    </div>
                </template>

                <!-- Subcategory Mode -->
                <template x-if="isSub">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-zinc-300 mb-1.5">التصنيف الرئيسي (الأب) <span class="text-red-500">*</span></label>
                            <select name="parent_id" required class="w-full py-2.5 px-3.5 rounded-xl bg-zinc-900 border border-zinc-700 text-xs text-white focus:ring-2 focus:ring-red-500 transition">
                                <option value="">اختر التصنيف الرئيسي...</option>
                                @foreach($parentCategories as $parent)
                                    <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-zinc-300 mb-1.5">الإدارة الخدمية المسؤولة (التوجيه الآلي) <span class="text-red-500">*</span></label>
                            <select name="department_id" required class="w-full py-2.5 px-3.5 rounded-xl bg-zinc-900 border border-zinc-700 text-xs text-white focus:ring-2 focus:ring-red-500 transition">
                                <option value="">اختر الإدارة المختصة...</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                            <p class="text-[10px] text-zinc-500 mt-1">تقتصر القائمة على الإدارات التابعة لوزارتك فقط.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-zinc-300 mb-1.5">اسم نوع المشكلة (التصنيف الفرعي) <span class="text-red-500">*</span></label>
                            <input type="text" name="name" required placeholder="مثال: حفر وتشققات إسفلتية خطرة" 
                                   class="w-full py-2.5 px-3.5 rounded-xl bg-zinc-900 border border-zinc-700 text-xs text-white placeholder-zinc-500 focus:ring-2 focus:ring-red-500 transition">
                        </div>
                    </div>
                </template>

                <div>
                    <label class="block text-xs font-bold text-zinc-300 mb-1.5">الوصف والإرشادات للمواطنين</label>
                    <textarea name="description" rows="3" placeholder="توضيح الحالات التي يشملها هذا التصنيف للمواطنين..." 
                              class="w-full py-2.5 px-3.5 rounded-xl bg-zinc-900 border border-zinc-700 text-xs text-white placeholder-zinc-500 focus:ring-2 focus:ring-red-500 transition"></textarea>
                </div>

                <button type="submit" class="w-full py-3 rounded-xl bg-red-600 text-white font-black text-xs hover:bg-red-700 active:scale-[0.99] transition shadow-lg shadow-red-900/20">
                    + إضافة التصنيف
                </button>
            </form>
        </div>

        <!-- Tree View Listing -->
        <div class="lg:col-span-2 space-y-6">
            <div class="p-5 rounded-2xl bg-[#111116] border border-zinc-800 shadow-xl flex items-center justify-between">
                <div>
                    <h4 class="text-sm font-bold text-white">الهيكل الشجري للتصنيفات والتوجيه الآلي</h4>
                    <p class="text-xs text-zinc-400 mt-0.5">التصنيفات الرئيسية تمثل القطاعات، وتتفرع منها أنواع المشكلات المسندة للإدارات.</p>
                </div>
            </div>

            <!-- Main Categories Tree Cards -->
            <div class="space-y-4">
                @forelse($mainCategories as $main)
                    <div class="bg-[#111116] rounded-2xl border border-zinc-800 shadow-xl overflow-hidden" x-data="{ expanded: true, editOpen: false }">
                        <!-- Main Category Header -->
                        <div class="p-4 bg-zinc-900/80 border-b border-zinc-800 flex flex-wrap items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <button type="button" @click="expanded = !expanded" class="p-1.5 rounded-lg bg-zinc-800 text-zinc-400 hover:text-white">
                                    <svg class="w-4 h-4 transform transition-transform" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-white text-sm">{{ $main->name }}</span>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-400/10 text-amber-300 border border-amber-400/20">رئيسي</span>
                                        @if(!$main->is_active)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-500/10 text-red-400 border border-red-500/20">معطل / مؤرشف</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">نشط</span>
                                        @endif
                                    </div>
                                    @if($main->description)
                                        <p class="text-[11px] text-zinc-400 mt-0.5">{{ $main->description }}</p>
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-1 rounded-lg bg-zinc-900 border border-zinc-800 text-xs font-mono font-bold text-zinc-300">
                                    إجمالي البلاغات: <strong class="text-red-500">{{ $main->total_complaints_count }}</strong>
                                </span>

                                <!-- Toggle Active -->
                                <form method="POST" action="{{ route('ministry.categories.toggle_active', $main) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="px-2.5 py-1 rounded-lg text-xs font-bold border transition {{ $main->is_active ? 'border-amber-500/40 text-amber-400 hover:bg-amber-500/10' : 'border-emerald-500/40 text-emerald-400 hover:bg-emerald-500/10' }}">
                                        {{ $main->is_active ? 'تعطيل' : 'تفعيل' }}
                                    </button>
                                </form>

                                <!-- Edit Trigger -->
                                <button type="button" @click="editOpen = !editOpen" class="px-2.5 py-1 rounded-lg text-xs font-bold border border-zinc-700 text-zinc-300 hover:bg-zinc-800">
                                    تعديل
                                </button>

                                <!-- Delete -->
                                <form method="POST" action="{{ route('ministry.categories.destroy', $main) }}" onsubmit="return confirm('هل أنت متأكد من حذف هذا التصنيف الرئيسي؟ لن يتم الحذف إذا كانت تحته تصنيفات فرعية أو بلاغات.');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1 rounded-lg text-xs font-bold border border-red-500/40 text-red-400 hover:bg-red-500/10">
                                        حذف
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Edit Inline Box -->
                        <div x-show="editOpen" x-cloak class="p-4 bg-zinc-900 border-b border-zinc-800">
                            <form method="POST" action="{{ route('ministry.categories.update', $main) }}" class="space-y-3">
                                @csrf
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-bold text-zinc-300 mb-1">اسم التصنيف الرئيسي</label>
                                        <input type="text" name="name" value="{{ $main->name }}" required class="w-full py-1.5 px-3 rounded-lg bg-zinc-800 border border-zinc-700 text-xs text-white">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-zinc-300 mb-1">الوصف والإرشادات</label>
                                        <input type="text" name="description" value="{{ $main->description }}" class="w-full py-1.5 px-3 rounded-lg bg-zinc-800 border border-zinc-700 text-xs text-white">
                                    </div>
                                </div>
                                <div class="flex justify-end gap-2">
                                    <button type="button" @click="editOpen = false" class="px-3 py-1 text-xs text-zinc-400 font-bold">إلغاء</button>
                                    <button type="submit" class="px-4 py-1 rounded-lg bg-red-600 text-white text-xs font-bold hover:bg-red-700">حفظ التعديل</button>
                                </div>
                            </form>
                        </div>

                        <!-- Subcategories Table -->
                        <div x-show="expanded" class="divide-y divide-zinc-800/60">
                            @if($main->children->isEmpty())
                                <p class="p-4 text-center text-xs text-zinc-500">لا توجد تصنيفات فرعية مضافة تحت هذا القطاع حتى الآن.</p>
                            @else
                                <div class="overflow-x-auto">
                                    <table class="w-full text-right text-xs">
                                        <thead class="bg-zinc-900/60 text-zinc-400 uppercase font-bold border-b border-zinc-800">
                                            <tr>
                                                <th class="py-2.5 px-5">نوع المشكلة (التصنيف الفرعي)</th>
                                                <th class="py-2.5 px-5">الإدارة المسؤولة (التوجيه الآلي)</th>
                                                <th class="py-2.5 px-5">الحالة</th>
                                                <th class="py-2.5 px-5">البلاغات</th>
                                                <th class="py-2.5 px-5 text-left">إجراءات</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-zinc-800/40">
                                            @foreach($main->children as $sub)
                                                <tr class="hover:bg-zinc-900/40" x-data="{ subEditOpen: false }">
                                                    <td class="py-3 px-5 font-bold text-white">
                                                        {{ $sub->name }}
                                                        @if($sub->description)
                                                            <span class="block text-[10px] text-zinc-500 font-normal">{{ $sub->description }}</span>
                                                        @endif
                                                    </td>
                                                    <td class="py-3 px-5 text-zinc-300 font-medium">
                                                        {{ $sub->department?->name ?? 'غير محدد' }}
                                                    </td>
                                                    <td class="py-3 px-5">
                                                        @if($sub->is_active)
                                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-400">نشط</span>
                                                        @else
                                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-500/10 text-red-400">معطل</span>
                                                        @endif
                                                    </td>
                                                    <td class="py-3 px-5 font-mono font-bold text-red-500">
                                                        {{ $sub->complaints_count }}
                                                    </td>
                                                    <td class="py-3 px-5 text-left whitespace-nowrap">
                                                        <div class="flex items-center justify-end gap-1.5">
                                                            <!-- Toggle Active -->
                                                            <form method="POST" action="{{ route('ministry.categories.toggle_active', $sub) }}" class="inline">
                                                                @csrf
                                                                <button type="submit" class="px-2 py-0.5 text-[11px] font-bold rounded border {{ $sub->is_active ? 'border-amber-500/40 text-amber-400 hover:bg-amber-500/10' : 'border-emerald-500/40 text-emerald-400 hover:bg-emerald-500/10' }}">
                                                                    {{ $sub->is_active ? 'تعطيل' : 'تفعيل' }}
                                                                </button>
                                                            </form>

                                                            <!-- Edit Trigger -->
                                                            <button type="button" @click="subEditOpen = !subEditOpen" class="px-2 py-0.5 text-[11px] font-bold rounded border border-zinc-700 text-zinc-300 hover:bg-zinc-800">
                                                                تعديل
                                                            </button>

                                                            <!-- Delete -->
                                                            <form method="POST" action="{{ route('ministry.categories.destroy', $sub) }}" onsubmit="return confirm('هل أنت متأكد من حذف هذا التصنيف الفرعي؟ لن يتم الحذف إذا كانت مسجلة به بلاغات.');" class="inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="px-2 py-0.5 text-[11px] font-bold rounded border border-red-500/40 text-red-400 hover:bg-red-500/10">
                                                                    حذف
                                                                </button>
                                                            </form>
                                                        </div>

                                                        <!-- Subcategory Inline Edit -->
                                                        <div x-show="subEditOpen" x-cloak class="mt-2 p-3 bg-zinc-900 rounded-xl border border-zinc-800 text-right space-y-2">
                                                            <form method="POST" action="{{ route('ministry.categories.update', $sub) }}" class="space-y-2">
                                                                @csrf
                                                                <div>
                                                                    <label class="block text-[10px] font-bold text-zinc-300">اسم التصنيف الفرعي</label>
                                                                    <input type="text" name="name" value="{{ $sub->name }}" required class="w-full py-1 px-2 rounded bg-zinc-800 border border-zinc-700 text-xs text-white">
                                                                </div>
                                                                <div>
                                                                    <label class="block text-[10px] font-bold text-zinc-300">الإدارة الخدمية المسؤولة</label>
                                                                    <select name="department_id" required class="w-full py-1 px-2 rounded bg-zinc-800 border border-zinc-700 text-xs text-white">
                                                                        @foreach($departments as $dept)
                                                                            <option value="{{ $dept->id }}" {{ $sub->department_id == $dept->id ? 'selected' : '' }}>
                                                                                {{ $dept->name }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                    <p class="text-[9px] text-zinc-400">تغيير الإدارة يسري على البلاغات الجديدة فقط.</p>
                                                                </div>
                                                                <div class="flex justify-end gap-1.5 pt-1">
                                                                    <button type="button" @click="subEditOpen = false" class="px-2 py-0.5 text-[10px] text-zinc-400 font-bold">إلغاء</button>
                                                                    <button type="submit" class="px-3 py-0.5 rounded bg-red-600 text-white text-[10px] font-bold">حفظ</button>
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
                    <div class="p-8 text-center bg-[#111116] rounded-2xl border border-zinc-800 text-zinc-500">
                        لا توجد تصنيفات رئيسية مضافة لهذه الوزارة حتى الآن.
                    </div>
                @endforelse
            </div>

            <div class="p-4 bg-[#111116] rounded-2xl border border-zinc-800 shadow-xl">
                {{ $categories->links() }}
            </div>
        </div>
    </div>
</x-ministry-layout>

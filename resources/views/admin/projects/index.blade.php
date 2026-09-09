<x-admin-layout title="المشاريع التنموية والرقابة المجتمعية" headerTitle="إدارة المشاريع التنموية الوطنية والرقابة">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        <!-- Add Project Form -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs h-fit">
            <h3 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-red-600"></span>
                إضافة مشروع تنموي جديد
            </h3>
            <form method="POST" action="{{ route('admin.projects.store') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">اسم المشروع التنموي</label>
                    <input type="text" name="name" required placeholder="مثال: مشروع تأهيل شبكة المياه في صنعاء القديمة" 
                           class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">المحافظة / الموقع</label>
                    <input type="text" name="governorate" placeholder="أمانة العاصمة / صنعاء" 
                           class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">الميزانية المقدرة</label>
                        <input type="number" name="budget" step="0.01" placeholder="ريال يمني" 
                               class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">المستفيدين</label>
                        <input type="number" name="beneficiaries_count" placeholder="عدد الأفراد" 
                               class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">خط العرض (Lat)</label>
                        <input type="number" step="0.0000001" name="latitude" required value="15.3694" 
                               class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">خط الطول (Lng)</label>
                        <input type="number" step="0.0000001" name="longitude" required value="44.1910" 
                               class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">الحالة</label>
                        <select name="status" required class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                            <option value="planning">تخطيط (Planning)</option>
                            <option value="in_progress" selected>قيد التنفيذ (In Progress)</option>
                            <option value="completed">مكتمل (Completed)</option>
                            <option value="halted">متوقف (Halted)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">نسبة الإنجاز (%)</label>
                        <input type="number" name="progress_percentage" min="0" max="100" value="0" required 
                               class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">وصف المشروع</label>
                    <textarea name="description" rows="3" required placeholder="أهداف المشروع ومخرجاته المتوقعة..." 
                              class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500"></textarea>
                </div>

                <button type="submit" class="w-full py-2.5 rounded-xl bg-red-600 text-white font-bold text-xs hover:bg-red-700 transition">
                    + إطلاق المشروع التنموي
                </button>
            </form>
        </div>

        <!-- Projects Table -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between text-xs text-slate-500 font-bold">
                <span>المشاريع الوطنية المسجلة: {{ $projects->total() }}</span>
                <span>الرقابة التنموية</span>
            </div>

            <!-- Desktop Table View -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-right text-xs">
                    <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-bold border-b border-slate-200/80">
                        <tr>
                            <th class="py-3.5 px-6">المشروع</th>
                            <th class="py-3.5 px-6">نسبة الإنجاز</th>
                            <th class="py-3.5 px-6">الحالة</th>
                            <th class="py-3.5 px-6">الميزانية</th>
                            <th class="py-3.5 px-6 text-center">تحديث النسبة</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse($projects as $p)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-4 px-6">
                                    <p class="font-bold text-slate-900">{{ $p->name }}</p>
                                    <p class="text-[10px] text-slate-400">#PRJ-{{ $p->id }} | {{ $p->governorate ?? 'صنعاء' }}</p>
                                </td>
                                <td class="py-4 px-6 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <div class="w-16 h-2 bg-slate-100 rounded-full overflow-hidden">
                                            <div class="h-full bg-red-600 rounded-full" style="width: {{ $p->progress_percentage }}%"></div>
                                        </div>
                                        <span class="font-bold text-slate-700">{{ $p->progress_percentage }}%</span>
                                    </div>
                                </td>
                                <td class="py-4 px-6 whitespace-nowrap">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $p->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ match($p->status) {
                                            'planning' => 'تخطيط',
                                            'in_progress' => 'قيد التنفيذ',
                                            'completed' => 'مكتمل',
                                            'halted' => 'متوقف',
                                            default => $p->status
                                        } }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-slate-600 whitespace-nowrap">
                                    {{ $p->budget ? number_format($p->budget) . ' ر.ي' : '-' }}
                                </td>
                                <td class="py-4 px-6 text-center whitespace-nowrap">
                                    <form method="POST" action="{{ route('admin.projects.update_progress', $p->id) }}" class="flex items-center justify-center gap-1.5">
                                        @csrf
                                        <input type="number" name="progress_percentage" value="{{ $p->progress_percentage }}" min="0" max="100" class="w-14 py-1 px-1.5 rounded-lg border border-slate-300 text-xs text-center">
                                        <select name="status" class="py-1 px-2 rounded-lg border border-slate-300 text-xs">
                                            <option value="in_progress" {{ $p->status === 'in_progress' ? 'selected' : '' }}>تنفيذ</option>
                                            <option value="completed" {{ $p->status === 'completed' ? 'selected' : '' }}>مكتمل</option>
                                            <option value="halted" {{ $p->status === 'halted' ? 'selected' : '' }}>متوقف</option>
                                        </select>
                                        <button type="submit" class="py-1 px-3 rounded-lg bg-black text-white text-xs font-bold hover:bg-zinc-800 transition shadow-2xs">
                                            حفظ
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-8 text-slate-400">لا توجد مشاريع مضافة.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div class="block md:hidden divide-y divide-slate-100 p-3.5 space-y-3">
                @forelse($projects as $p)
                    <div class="p-4 rounded-2xl bg-slate-50/90 border border-slate-200/80 space-y-3">
                        <div class="flex items-center justify-between gap-2">
                            <span class="font-mono text-xs font-bold text-slate-900 bg-white px-2 py-0.5 rounded border border-slate-200">
                                #PRJ-{{ $p->id }}
                            </span>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $p->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ match($p->status) {
                                    'planning' => 'تخطيط',
                                    'in_progress' => 'قيد التنفيذ',
                                    'completed' => 'مكتمل',
                                    'halted' => 'متوقف',
                                    default => $p->status
                                } }}
                            </span>
                        </div>

                        <div>
                            <h4 class="text-sm font-bold text-slate-900 leading-snug">{{ $p->name }}</h4>
                            <p class="text-xs text-slate-500 mt-1">الموقع: {{ $p->governorate ?? 'صنعاء' }}</p>
                        </div>

                        <!-- Progress Bar -->
                        <div class="space-y-1">
                            <div class="flex justify-between text-xs font-bold">
                                <span class="text-slate-500">نسبة الإنجاز</span>
                                <span class="text-red-600">{{ $p->progress_percentage }}%</span>
                            </div>
                            <div class="w-full h-2 bg-slate-200 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-red-600 to-amber-500 rounded-full" style="width: {{ $p->progress_percentage }}%"></div>
                            </div>
                        </div>

                        <!-- Update Form -->
                        <form method="POST" action="{{ route('admin.projects.update_progress', $p->id) }}" class="pt-2 border-t border-slate-200/60 flex items-center gap-2">
                            @csrf
                            <input type="number" name="progress_percentage" value="{{ $p->progress_percentage }}" min="0" max="100" class="w-16 py-1.5 px-2 rounded-xl border border-slate-300 text-xs text-center font-bold">
                            <select name="status" class="flex-1 py-1.5 px-2 rounded-xl border border-slate-300 text-xs font-medium">
                                <option value="in_progress" {{ $p->status === 'in_progress' ? 'selected' : '' }}>قيد التنفيذ</option>
                                <option value="completed" {{ $p->status === 'completed' ? 'selected' : '' }}>مكتمل</option>
                                <option value="halted" {{ $p->status === 'halted' ? 'selected' : '' }}>متوقف</option>
                            </select>
                            <button type="submit" class="py-1.5 px-4 rounded-xl bg-black text-white text-xs font-bold hover:bg-zinc-800 transition">
                                تحديث
                            </button>
                        </form>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 text-center py-6">لا توجد مشاريع مضافة.</p>
                @endforelse
            </div>

            <div class="p-4 border-t border-slate-100">
                {{ $projects->links() }}
            </div>
        </div>
    </div>
</x-admin-layout>

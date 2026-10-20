<x-ministry-layout title="المشاريع التنموية" headerTitle="إدارة المشاريع التنموية للوزارة">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        <!-- Add Project Form for Ministry -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs h-fit">
            <h3 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-red-600"></span>
                إضافة مشروع تنموي تابع للوزارة
            </h3>
            <form method="POST" action="{{ route('ministry.projects.store') }}" enctype="multipart/form-data" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">اسم المشروع التنموي</label>
                    <input type="text" name="name" required placeholder="مثال: مشروع تأهيل شبكة المياه والري" 
                           class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">الجهة / الإدارة التابعة</label>
                    <select name="department_id" required class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">الميزانية التقديرية (ر.ي)</label>
                        <input type="number" name="budget" step="0.01" required placeholder="0.00" 
                               class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">الحالة الأولية</label>
                        <select name="status" required class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                            <option value="planning">تخطيط (Planning)</option>
                            <option value="in_progress" selected>قيد التنفيذ (In Progress)</option>
                            <option value="active">نشط ومتاح (Active)</option>
                            <option value="completed">مكتمل (Completed)</option>
                        </select>
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

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">صورة المشروع (اختياري)</label>
                    <input type="file" name="image" accept="image/*" 
                           class="w-full py-1.5 px-3 rounded-xl border border-slate-300 text-xs text-slate-600 focus:ring-2 focus:ring-red-500 file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
                    <p class="text-[10px] text-slate-400 mt-1">يمكنك رفع صورة تعريفية للمشروع (JPEG, PNG, WebP بحد أقصى 5 ميجابايت).</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">وصف المشروع ومستهدفاته</label>
                    <textarea name="description" rows="3" required placeholder="أهداف المشروع ومخرجاته التنموية..." 
                              class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500"></textarea>
                </div>

                <button type="submit" class="w-full py-2.5 rounded-xl bg-red-600 text-white font-bold text-xs hover:bg-red-700 transition">
                    + إطلاق المشروع التنموي
                </button>
            </form>
        </div>

        <!-- Ministry Projects Table -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between text-xs text-slate-500 font-bold">
                <span>مشاريع الوزارة المسجلة: {{ $projects->total() }}</span>
                <span>المتابعة التنموية</span>
            </div>

            <!-- Desktop Table View -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-right text-xs">
                    <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-bold border-b border-slate-200/80">
                        <tr>
                            <th class="py-3.5 px-6">المشروع</th>
                            <th class="py-3.5 px-6">الجهة</th>
                            <th class="py-3.5 px-6">الحالة</th>
                            <th class="py-3.5 px-6">الميزانية</th>
                            <th class="py-3.5 px-6 text-center">إدارة الحالة</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse($projects as $p)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-3">
                                        @if($p->image_url)
                                            <img src="{{ $p->image_url }}" alt="{{ $p->name }}" class="w-10 h-10 rounded-lg object-cover shrink-0 border border-slate-200">
                                        @else
                                            <div class="w-10 h-10 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center shrink-0 text-slate-400">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            </div>
                                        @endif
                                        <div>
                                            <p class="font-bold text-slate-900">{{ $p->name }}</p>
                                            <p class="text-[10px] text-slate-400">#PRJ-{{ $p->id }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-6 text-slate-600 whitespace-nowrap">
                                    {{ $p->department?->name ?? 'غير محدد' }}
                                </td>
                                <td class="py-4 px-6 whitespace-nowrap">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $p->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ match($p->status) {
                                            'planning' => 'تخطيط',
                                            'in_progress' => 'قيد التنفيذ',
                                            'completed' => 'مكتمل',
                                            'active' => 'نشط ومتاح',
                                            'suspended' => 'معلق',
                                            default => $p->status
                                        } }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-slate-600 whitespace-nowrap">
                                    {{ $p->target_amount ? number_format($p->target_amount) . ' ر.ي' : '-' }}
                                </td>
                                <td class="py-4 px-6 text-center whitespace-nowrap">
                                    <form method="POST" action="{{ route('ministry.projects.update_progress', $p->id) }}" class="flex items-center justify-center gap-1.5">
                                        @csrf
                                        <select name="status" class="py-1 px-2 rounded-lg border border-slate-300 text-xs">
                                            <option value="planning" {{ $p->status === 'planning' ? 'selected' : '' }}>تخطيط</option>
                                            <option value="in_progress" {{ $p->status === 'in_progress' ? 'selected' : '' }}>قيد التنفيذ</option>
                                            <option value="active" {{ $p->status === 'active' ? 'selected' : '' }}>نشط</option>
                                            <option value="completed" {{ $p->status === 'completed' ? 'selected' : '' }}>مكتمل</option>
                                            <option value="suspended" {{ $p->status === 'suspended' ? 'selected' : '' }}>معلق</option>
                                        </select>
                                        <button type="submit" class="py-1 px-3 rounded-lg bg-black text-white text-xs font-bold hover:bg-zinc-800 transition shadow-2xs">
                                            حفظ
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-8 text-slate-400">لا توجد مشاريع مضافة لهذه الوزارة حتى الآن.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100">
                {{ $projects->links() }}
            </div>
        </div>
    </div>
</x-ministry-layout>

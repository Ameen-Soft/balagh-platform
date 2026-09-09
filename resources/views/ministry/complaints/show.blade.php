<x-ministry-layout title="معالجة البلاغ #{{ $complaint->complaint_number }}" headerTitle="محطة العمل والمعالجة التشغيلية للبلاغ">
    <!-- Header Dossier Banner -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-6 mb-8 shadow-xs">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <span class="font-mono font-bold text-xs bg-black text-white px-2.5 py-1 rounded-md border border-zinc-800">
                        #{{ $complaint->complaint_number }}
                    </span>
                    <span class="text-xs font-bold text-amber-700 bg-amber-50 border border-amber-200 px-2.5 py-0.5 rounded-full">
                        {{ $complaint->currentDepartment?->name }}
                    </span>
                </div>
                <h1 class="text-2xl font-black text-slate-900 mt-2">{{ $complaint->title }}</h1>
            </div>

            <div class="flex items-center gap-3">
                <span class="px-3.5 py-1.5 rounded-xl text-xs font-bold 
                    {{ in_array($complaint->status, ['resolved', 'closed']) ? 'bg-emerald-100 text-emerald-800' : ($complaint->status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800') }}">
                    الحالة: {{ $complaint->status_arabic }}
                </span>
                <span class="px-3.5 py-1.5 rounded-xl text-xs font-bold 
                    {{ $complaint->priority === 'urgent' ? 'bg-red-100 text-red-800' : ($complaint->priority === 'high' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-800') }}">
                    الأولوية: {{ $complaint->priority_arabic }}
                </span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Complaint Description -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs">
                <h3 class="text-sm font-bold text-slate-900 mb-3">تفاصيل البلاغ المسجل</h3>
                <p class="text-xs text-slate-700 leading-relaxed whitespace-pre-line">{{ $complaint->description }}</p>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center gap-4 text-[11px] text-slate-500">
                    <span>الإحداثيات: <span class="font-mono text-slate-800">{{ $complaint->latitude }}, {{ $complaint->longitude }}</span></span>
                    <span>المحافظة: <span class="font-bold text-slate-800">{{ $complaint->governorate ?? 'صنعاء' }}</span></span>
                </div>
            </div>

            <!-- Operational Actions Hub -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs" x-data="{ activeTab: 'status' }">
                <h3 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-red-600"></span>
                    الإجراءات والقرارات التشغيلية للجهة
                </h3>

                <!-- Action Tabs -->
                <div class="flex items-center gap-2 border-b border-slate-200 pb-3 mb-6 overflow-x-auto">
                    <button type="button" @click="activeTab = 'status'" 
                            :class="activeTab === 'status' ? 'bg-black text-white shadow-xs' : 'bg-zinc-100 text-zinc-700 hover:bg-zinc-200'"
                            class="whitespace-nowrap px-4 py-2 rounded-xl text-xs font-bold transition shrink-0">
                        تحديث حالة البلاغ
                    </button>
                    <button type="button" @click="activeTab = 'assign'" 
                            :class="activeTab === 'assign' ? 'bg-black text-white shadow-xs' : 'bg-zinc-100 text-zinc-700 hover:bg-zinc-200'"
                            class="whitespace-nowrap px-4 py-2 rounded-xl text-xs font-bold transition shrink-0">
                        إسناد لموظف ميداني
                    </button>
                    <button type="button" @click="activeTab = 'transfer'" 
                            :class="activeTab === 'transfer' ? 'bg-black text-white shadow-xs' : 'bg-zinc-100 text-zinc-700 hover:bg-zinc-200'"
                            class="whitespace-nowrap px-4 py-2 rounded-xl text-xs font-bold transition shrink-0">
                        إحالة لجهة حكومية أخرى
                    </button>
                </div>

                <!-- Tab 1: Update Status Form -->
                <div x-show="activeTab === 'status'">
                    <form method="POST" action="{{ route('ministry.complaints.update_status', $complaint->id) }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">الحالة الجديدة للبلاغ</label>
                            <select name="status" required class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                                <option value="under_review" {{ $complaint->status === 'under_review' ? 'selected' : '' }}>قيد المراجعة الفنية (Under Review)</option>
                                <option value="in_progress" {{ $complaint->status === 'in_progress' ? 'selected' : '' }}>جاري التنفيذ الميداني (In Progress)</option>
                                <option value="resolved" {{ $complaint->status === 'resolved' ? 'selected' : '' }}>تم الحل والإنجاز (Resolved)</option>
                                <option value="rejected" {{ $complaint->status === 'rejected' ? 'selected' : '' }}>رفض البلاغ مع التعليل (Rejected)</option>
                                <option value="closed" {{ $complaint->status === 'closed' ? 'selected' : '' }}>إغلاق نهائي (Closed)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">ملاحظات القرار الإجرائي (تصل للمواطن وتوثق بالسجل)</label>
                            <textarea name="notes" rows="3" placeholder="اكتب الملاحظات أو سبب الرفض أو تفاصيل الإنجاز..." 
                                      class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500"></textarea>
                        </div>
                        <button type="submit" class="py-2 px-6 rounded-xl bg-red-600 text-white font-bold text-xs hover:bg-red-700 transition">
                            اعتماد وتحديث الحالة
                        </button>
                    </form>
                </div>

                <!-- Tab 2: Assign Field Worker -->
                <div x-show="activeTab === 'assign'" style="display: none;">
                    <form method="POST" action="{{ route('ministry.complaints.assign_worker', $complaint->id) }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">اختر المهندس أو الفني الميداني</label>
                            <select name="worker_id" required class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                                <option value="">اختر الموظف الميداني المكلف...</option>
                                @foreach($fieldWorkers as $worker)
                                    <option value="{{ $worker->id }}">{{ $worker->name }} ({{ $worker->phone }})</option>
                                @endforeach
                            </select>
                            @if($fieldWorkers->isEmpty())
                                <p class="text-[11px] text-amber-600 mt-1">لا يوجد موظفين ميدانيين مسجلين لهذه الإدارة حالياً.</p>
                            @endif
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">توجيهات العمل الميداني</label>
                            <textarea name="notes" rows="3" placeholder="تعليمات النزول والمعاينة ورفع التقرير المصور..." 
                                      class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500"></textarea>
                        </div>
                        <button type="submit" {{ $fieldWorkers->isEmpty() ? 'disabled' : '' }} class="py-2 px-6 rounded-xl bg-black text-white font-bold text-xs hover:bg-zinc-800 disabled:opacity-50 transition">
                            إسناد المهمة الميدانية فوراً
                        </button>
                    </form>
                </div>

                <!-- Tab 3: Transfer to Another Department -->
                <div x-show="activeTab === 'transfer'" style="display: none;">
                    <form method="POST" action="{{ route('ministry.complaints.transfer', $complaint->id) }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">الجهة الحكومية المحال إليها</label>
                            <select name="to_department_id" required class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                                <option value="">اختر الجهة المختصة البديلة...</option>
                                @foreach($otherDepartments as $odept)
                                    <option value="{{ $odept->id }}">{{ $odept->ministry?->name }} - {{ $odept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">سبب الإحالة وعدم الاختصاص</label>
                            <textarea name="reason" rows="3" required placeholder="توضيح سبب إحالة البلاغ لخارج اختصاص هذه الإدارة..." 
                                      class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500"></textarea>
                        </div>
                        <button type="submit" class="py-2 px-6 rounded-xl bg-amber-600 text-white font-bold text-xs hover:bg-amber-700 transition">
                            تأكيد إحالة البلاغ
                        </button>
                    </form>
                </div>
            </div>

            <!-- Audit Trail Timeline -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs">
                <h3 class="text-sm font-bold text-slate-900 mb-6">السجل التاريخي للمعالجة</h3>
                <div class="space-y-6 relative before:absolute before:right-3.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
                    @forelse($complaint->timelines as $timeline)
                        <div class="relative pr-8">
                            <div class="absolute right-2 top-1.5 w-3.5 h-3.5 rounded-full bg-white border-2 border-slate-800"></div>
                            <div>
                                <div class="flex items-center justify-between">
                                    <p class="text-xs font-bold text-slate-800">{{ $timeline->action }}</p>
                                    <span class="text-[11px] text-slate-400">{{ $timeline->created_at->translatedFormat('d F Y - h:i a') }}</span>
                                </div>
                                <p class="text-xs text-slate-500 mt-0.5">المنفذ: {{ $timeline->actor?->name ?? 'النظام الآلي' }}</p>
                                @if($timeline->notes)
                                    <p class="text-xs text-slate-600 mt-1 leading-relaxed bg-slate-50 p-2.5 rounded-lg border border-slate-200/60">{{ $timeline->notes }}</p>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400">لا يوجد سجل تاريخي بعد.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Sidebar Info -->
        <div class="space-y-6">
            <!-- Citizen Identity -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs space-y-3">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">بيانات المواطن صاحب البلاغ</h4>
                <div>
                    <span class="text-[11px] text-slate-400 block font-semibold">الاسم</span>
                    <span class="text-xs font-bold text-slate-800">{{ $complaint->citizen?->name }}</span>
                </div>
                <div>
                    <span class="text-[11px] text-slate-400 block font-semibold">رقم الهاتف</span>
                    <span class="text-xs font-bold text-slate-800 font-mono">{{ $complaint->citizen?->phone }}</span>
                </div>
                <div>
                    <span class="text-[11px] text-slate-400 block font-semibold">الرقم الوطني</span>
                    <span class="text-xs font-bold text-slate-800 font-mono">{{ $complaint->citizen?->national_id }}</span>
                </div>
            </div>

            <!-- Field Assignments for this Complaint -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">التكليف الميداني الحالي</h4>
                @if($complaint->fieldAssignments->isNotEmpty())
                    @foreach($complaint->fieldAssignments as $asgn)
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80 mb-2">
                            <p class="text-xs font-bold text-slate-800">{{ $asgn->worker?->name }}</p>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-amber-100 text-amber-800 mt-1 inline-block">{{ $asgn->status }}</span>
                            @if($asgn->notes)
                                <p class="text-[11px] text-slate-500 mt-1">{{ $asgn->notes }}</p>
                            @endif
                        </div>
                    @endforeach
                @else
                    <p class="text-xs text-slate-400">لم يتم تكليف موظف ميداني بعد.</p>
                @endif
            </div>

            <a href="{{ route('ministry.complaints.index') }}" 
               class="block text-center w-full py-2.5 rounded-xl bg-black text-white text-xs font-bold hover:bg-zinc-800 transition">
                ← العودة لسجل بلاغات الجهة
            </a>
        </div>
    </div>
</x-ministry-layout>

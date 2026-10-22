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
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs" x-data="{ actionType: 'assign', showSecondary: false }">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h3 class="text-sm font-black text-slate-900 flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-red-600"></span>
                            مركز اتخاذ القرار الإجرائي
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">اختر الإجراء المناسب لمعالجة هذا البلاغ وفق دورة العمل المعتمدة.</p>
                    </div>
                    @if(in_array($complaint->status, ['closed', 'rejected']))
                        <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200">
                            البلاغ مكتمل / مؤرشف
                        </span>
                    @endif
                </div>

                <!-- Primary Action Switcher (Two Main Choices) -->
                <div class="grid grid-cols-2 gap-3 mb-6">
                    <button type="button" @click="actionType = 'assign'" 
                            :class="actionType === 'assign' ? 'border-red-600 bg-red-50/50 text-red-900 ring-2 ring-red-600/20' : 'border-slate-200 bg-white text-slate-700 hover:border-slate-300'"
                            class="p-4 rounded-xl border-2 text-right transition flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-red-600 text-white flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <div>
                            <span class="block text-xs font-black">إسناد لموظف ميداني</span>
                            <span class="block text-[11px] text-slate-500 mt-0.5">بدء التنفيذ المباشر والمعاينة الميدانية</span>
                        </div>
                    </button>

                    <button type="button" @click="actionType = 'reject'" 
                            :class="actionType === 'reject' ? 'border-zinc-900 bg-zinc-50 text-zinc-900 ring-2 ring-zinc-900/20' : 'border-slate-200 bg-white text-slate-700 hover:border-slate-300'"
                            class="p-4 rounded-xl border-2 text-right transition flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-zinc-900 text-white flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </div>
                        <div>
                            <span class="block text-xs font-black">رفض البلاغ مع التعليل</span>
                            <span class="block text-[11px] text-slate-500 mt-0.5">عدم المطابقة أو التعارض مع الضوابط</span>
                        </div>
                    </button>
                </div>

                <!-- Action 1 Form: Direct Assignment to Field Worker -->
                <div x-show="actionType === 'assign'" class="bg-slate-50/80 rounded-xl p-5 border border-slate-200/80 space-y-4">
                    <form method="POST" action="{{ route('ministry.complaints.assign_worker', $complaint->id) }}" class="space-y-4">
                        @csrf
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-bold text-slate-800">اختيار الموظف أو الفني الميداني المكلف</label>
                                <a href="{{ route('ministry.workers.index') }}" class="text-[11px] font-bold text-red-600 hover:text-red-700 transition">
                                    + إدارة الكادر الميداني
                                </a>
                            </div>
                            <select name="worker_id" required class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500 bg-white">
                                <option value="">-- اختر الموظف الميداني من كادر القسم --</option>
                                @foreach($fieldWorkers as $worker)
                                    <option value="{{ $worker->id }}">{{ $worker->name }} (هاتف: {{ $worker->phone }})</option>
                                @endforeach
                            </select>
                            @if($fieldWorkers->isEmpty())
                                <div class="mt-2 p-3 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-[11px] flex items-center justify-between">
                                    <span>لا يوجد موظفون ميدانيون متاحون حالياً في هذا القسم.</span>
                                    <a href="{{ route('ministry.workers.index') }}" class="font-bold underline text-amber-900">ترقية مواطن إلى موظف ميداني الآن</a>
                                </div>
                            @endif
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-800 mb-1.5">توجيهات وتعليمات العمل الميداني (اختياري)</label>
                            <textarea name="notes" rows="3" placeholder="أدخل تعليمات النزول والمعاينة، الأدوات المطلوبة، أو نقاط التركيز الميدانية..." 
                                      class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500 bg-white"></textarea>
                        </div>

                        <div class="p-3 rounded-lg bg-blue-50/80 border border-blue-200/80 text-blue-900 text-[11px] flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>بمجرد اعتماد الإسناد، ستتحول حالة البلاغ والمهمة مباشرة إلى <strong class="font-black">قيد التنفيذ (In Progress)</strong> وسيتم إشعار الموظف والمواطن فوراً لمباشرة المعاينة الميدانية.</span>
                        </div>

                        <button type="submit" {{ $fieldWorkers->isEmpty() ? 'disabled' : '' }} 
                                class="w-full py-3 px-6 rounded-xl bg-red-600 text-white font-black text-xs hover:bg-red-700 disabled:opacity-50 transition shadow-xs flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                            <span>إسناد المهمة الميدانية وبدء التنفيذ فوراً</span>
                        </button>
                    </form>
                </div>

                <!-- Action 2 Form: Direct Rejection with Mandatory Reason -->
                <div x-show="actionType === 'reject'" style="display: none;" class="bg-zinc-50 rounded-xl p-5 border border-zinc-200 space-y-4">
                    <form method="POST" action="{{ route('ministry.complaints.update_status', $complaint->id) }}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="status" value="rejected">

                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-bold text-zinc-900">
                                    سبب رفض البلاغ <span class="text-red-600">* (إلزامي)</span>
                                </label>
                                <span class="text-[10px] text-zinc-500">سيصل السبب للمواطن في الإشعار الرسمي</span>
                            </div>
                            <textarea name="notes" rows="3" required minlength="5" placeholder="وضح أسباب عدم قبول البلاغ (مثل: عدم وضوح الصور، تكرار الشكوى، خروج الطلب عن اللوائح المنظمة)..." 
                                      class="w-full py-2 px-3 rounded-xl border border-zinc-300 text-xs focus:ring-2 focus:ring-zinc-900 bg-white"></textarea>
                        </div>

                        <div class="p-3 rounded-lg bg-amber-50 border border-amber-200 text-amber-900 text-[11px] flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span>تنبيه: سيتم توثيق قرار الرفض وسببه في السجل الزمني للبلاغ ولن تتم معالجته ميدانياً.</span>
                        </div>

                        <button type="submit" class="w-full py-3 px-6 rounded-xl bg-zinc-900 text-white font-black text-xs hover:bg-black transition shadow-xs flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                            </svg>
                            <span>تأكيد رفض البلاغ مع التعليل</span>
                        </button>
                    </form>
                </div>

                <!-- Secondary Actions Toggle -->
                <div class="mt-6 pt-4 border-t border-slate-200">
                    <button type="button" @click="showSecondary = !showSecondary" class="flex items-center justify-between w-full text-xs font-bold text-slate-500 hover:text-slate-800 transition">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                            </svg>
                            إجراءات ثانوية (إحالة لجهة حكومية أخرى / تعديل الحالة يدوياً)
                        </span>
                        <svg class="w-4 h-4 transition transform" :class="showSecondary ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="showSecondary" style="display: none;" class="mt-4 pt-4 border-t border-slate-100 space-y-6">
                        <!-- Transfer to Another Department -->
                        <div class="p-4 rounded-xl bg-amber-50/60 border border-amber-200/80">
                            <h4 class="text-xs font-black text-amber-900 mb-3 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                </svg>
                                إحالة البلاغ لجهة حكومية أخرى
                            </h4>
                            <form method="POST" action="{{ route('ministry.complaints.transfer', $complaint->id) }}" class="space-y-3">
                                @csrf
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">الجهة الحكومية المحال إليها</label>
                                    <select name="to_department_id" required class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-amber-500 bg-white">
                                        <option value="">اختر الجهة المختصة البديلة...</option>
                                        @foreach($otherDepartments as $odept)
                                            <option value="{{ $odept->id }}">{{ $odept->ministry?->name }} - {{ $odept->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">سبب الإحالة وعدم الاختصاص</label>
                                    <textarea name="reason" rows="2" required placeholder="توضيح سبب إحالة البلاغ لخارج اختصاص هذه الإدارة..." 
                                              class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-amber-500 bg-white"></textarea>
                                </div>
                                <button type="submit" class="py-2 px-4 rounded-xl bg-amber-600 text-white font-bold text-xs hover:bg-amber-700 transition">
                                    تأكيد إحالة البلاغ
                                </button>
                            </form>
                        </div>

                        <!-- Manual Status Override -->
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                            <h4 class="text-xs font-black text-slate-800 mb-3">تحديث يدوي لحالة البلاغ</h4>
                            <form method="POST" action="{{ route('ministry.complaints.update_status', $complaint->id) }}" class="space-y-3">
                                @csrf
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">الحالة الإجرائية</label>
                                    <select name="status" required class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500 bg-white">
                                        <option value="under_review" {{ $complaint->status === 'under_review' ? 'selected' : '' }}>قيد المراجعة الفنية (Under Review)</option>
                                        <option value="in_progress" {{ $complaint->status === 'in_progress' ? 'selected' : '' }}>قيد التنفيذ الميداني (In Progress)</option>
                                        <option value="resolved" {{ $complaint->status === 'resolved' ? 'selected' : '' }}>تم الحل والإنجاز (Resolved)</option>
                                        <option value="closed" {{ $complaint->status === 'closed' ? 'selected' : '' }}>إغلاق نهائي (Closed)</option>
                                        <option value="reopened" {{ $complaint->status === 'reopened' ? 'selected' : '' }}>إعادة فتح البلاغ (Reopened)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">ملاحظات التحديث</label>
                                    <textarea name="notes" rows="2" placeholder="ملاحظات توثق في السجل الزمني..." 
                                              class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500 bg-white"></textarea>
                                </div>
                                <button type="submit" class="py-2 px-4 rounded-xl bg-slate-800 text-white font-bold text-xs hover:bg-black transition">
                                    حفظ التحديث
                                </button>
                            </form>
                        </div>
                    </div>
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
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded {{ $asgn->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : ($asgn->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800') }} mt-1 inline-block">
                                {{ match($asgn->status) { 'in_progress' => 'قيد التنفيذ', 'completed' => 'مكتمل', 'failed' => 'ملغي', 'pending' => 'قيد الانتظار', 'accepted' => 'مقبول', default => $asgn->status } }}
                            </span>
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

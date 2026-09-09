<x-admin-layout title="معاينة الرقابة على البلاغ #{{ $complaint->complaint_number }}" headerTitle="ملف الرقابة الشامل للبلاغ">
    <!-- Header Summary Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-6 mb-8 shadow-xs">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <span class="font-mono font-bold text-xs bg-black text-white px-2.5 py-1 rounded-md border border-zinc-800">
                        #{{ $complaint->complaint_number }}
                    </span>
                    <span class="text-xs font-bold text-red-600 bg-red-50 border border-red-200 px-2.5 py-0.5 rounded-full">
                        رقابة عليا
                    </span>
                </div>
                <h1 class="text-2xl font-black text-slate-900 mt-2">{{ $complaint->title }}</h1>
            </div>

            <div class="flex items-center gap-3">
                <span class="px-3.5 py-1.5 rounded-xl text-xs font-bold 
                    {{ in_array($complaint->status, ['resolved', 'closed']) ? 'bg-emerald-100 text-emerald-800' : 'bg-zinc-100 text-zinc-800 border border-zinc-200' }}">
                    حالة البلاغ: {{ $complaint->status_arabic }}
                </span>
                <span class="px-3 py-1.5 rounded-xl text-xs font-bold 
                    {{ $complaint->priority === 'urgent' ? 'bg-red-100 text-red-800' : ($complaint->priority === 'high' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-800') }}">
                    الأولوية: {{ $complaint->priority_arabic }}
                </span>
            </div>
        </div>

        <!-- Architectural Boundary Notice (Recommendation #4) -->
        <div class="mt-4 p-3 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-800 flex items-center gap-2">
            <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>
                <strong>ضوابط الصلاحيات:</strong> هذه الصفحة مخصصة للمراقبة والتدقيق الشامل. الإجراءات التشغيلية الميدانية (التكليف الميداني، الإحالة وتحديث الحالة) منوطة بمشرفي الوزارة المعنية.
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Dossier Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Complaint Description -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs">
                <h3 class="text-sm font-bold text-slate-900 mb-3">نص ووصف البلاغ</h3>
                <p class="text-xs text-slate-700 leading-relaxed whitespace-pre-line">{{ $complaint->description }}</p>
            </div>

            <!-- Audit Timeline -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs">
                <h3 class="text-sm font-bold text-slate-900 mb-6">سجل التتبع الزمني والإجرائي (Audit Trail)</h3>
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

            <!-- Field Assignments Log -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs">
                <h3 class="text-sm font-bold text-slate-900 mb-4">المهام الميدانية المسندة</h3>
                <div class="space-y-3">
                    @forelse($complaint->fieldAssignments as $fa)
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                            <div>
                                <p class="text-xs font-bold text-slate-800">المكلف الميداني: {{ $fa->worker?->name }}</p>
                                <p class="text-[11px] text-slate-500">الحالة: {{ $fa->status }}</p>
                            </div>
                            <span class="text-[11px] text-slate-400">{{ $fa->created_at->format('Y-m-d') }}</span>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400">لم يتم إسناد أي مهام ميدانية لهذا البلاغ حتى الآن.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Sidebar Info -->
        <div class="space-y-6">
            <!-- Citizen Info -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs space-y-3">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">بيانات مقدم البلاغ</h4>
                <div>
                    <span class="text-[11px] text-slate-400 block font-semibold">اسم المواطن</span>
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

            <!-- Ministry Info -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs space-y-3">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">الجهة المعنية</h4>
                <div>
                    <span class="text-[11px] text-slate-400 block font-semibold">الوزارة</span>
                    <span class="text-xs font-bold text-slate-800">{{ $complaint->currentDepartment?->ministry?->name }}</span>
                </div>
                <div>
                    <span class="text-[11px] text-slate-400 block font-semibold">الإدارة الخدمية</span>
                    <span class="text-xs font-bold text-slate-800">{{ $complaint->currentDepartment?->name }}</span>
                </div>
                <div>
                    <span class="text-[11px] text-slate-400 block font-semibold">التصنيف</span>
                    <span class="text-xs font-bold text-slate-800">{{ $complaint->category?->name }}</span>
                </div>
            </div>

            <a href="{{ route('admin.complaints.index') }}" 
               class="block text-center w-full py-2.5 rounded-xl bg-black text-white text-xs font-bold hover:bg-zinc-800 transition">
                ← العودة لسجل الرقابة
            </a>
        </div>
    </div>
</x-admin-layout>

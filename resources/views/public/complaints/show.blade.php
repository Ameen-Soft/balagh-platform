<x-public-layout title="تفاصيل البلاغ #{{ $complaint->complaint_number }} - منصة بادر">
    <div class="bg-gradient-to-b from-black via-[#08080a] to-[#0d0d11] text-white pt-28 pb-12 border-b border-zinc-800/80">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-mono font-bold text-red-400 bg-red-950/80 px-2.5 py-1 rounded-md border border-red-800">
                            #{{ $complaint->complaint_number }}
                        </span>
                        <span class="text-xs font-bold text-slate-400">سجل الشفافية المفتوح</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-black mt-2 text-white">{{ $complaint->title }}</h1>
                </div>

                <div>
                    @if($complaint->status === 'resolved' || $complaint->status === 'closed')
                        <span class="px-3.5 py-1.5 rounded-xl text-sm font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                            تم حل البلاغ وإنجازه
                        </span>
                    @elseif($complaint->status === 'in_progress')
                        <span class="px-3.5 py-1.5 rounded-xl text-sm font-bold bg-amber-500/20 text-amber-400 border border-amber-500/30">
                            جاري العمل الميداني
                        </span>
                    @elseif($complaint->status === 'rejected')
                        <span class="px-3.5 py-1.5 rounded-xl text-sm font-bold bg-red-500/20 text-red-400 border border-red-500/30">
                            مرفوض مع الإفادة
                        </span>
                    @else
                        <span class="px-3.5 py-1.5 rounded-xl text-sm font-bold bg-blue-500/20 text-blue-400 border border-blue-500/30">
                            قيد المراجعة الفنية
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <!-- Privacy Notice Banner -->
        <div class="p-4 rounded-xl bg-slate-100 border border-slate-200 text-xs text-slate-600 font-semibold mb-8 flex items-center gap-2">
            <svg class="w-5 h-5 text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
            <span>حماية الخصوصية: تم حجب البيانات الشخصية للمواطن (الاسم، الهاتف، الرقم الوطني) في بوابة الشفافية العامة.</span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Description Card -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
                    <h2 class="text-sm font-bold text-slate-900 mb-3">تفاصيل موضوع البلاغ</h2>
                    <p class="text-sm text-slate-700 leading-relaxed whitespace-pre-line">{{ $complaint->description }}</p>
                </div>

                <!-- Timeline of Actions -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
                    <h2 class="text-sm font-bold text-slate-900 mb-6">المسار الزمني للمعالجة والشفافية</h2>
                    <div class="space-y-6 relative before:absolute before:right-3.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
                        @forelse($complaint->timelines as $timeline)
                            <div class="relative pr-8">
                                <div class="absolute right-2 top-1.5 w-3.5 h-3.5 rounded-full bg-white border-2 border-red-600"></div>
                                <div>
                                    <div class="flex items-center justify-between">
                                        <p class="text-xs font-bold text-slate-800">{{ $timeline->action }}</p>
                                        <span class="text-[11px] text-slate-400">{{ $timeline->created_at->translatedFormat('d F Y - h:i a') }}</span>
                                    </div>
                                    @if($timeline->notes)
                                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">{{ $timeline->notes }}</p>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-slate-400">تم تسجيل البلاغ وبانتظار التحديثات الإجرائية.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Attachments (Photos) -->
                @if($complaint->attachments->isNotEmpty())
                    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
                        <h2 class="text-sm font-bold text-slate-900 mb-4">الصور والمرفقات التوثيقية</h2>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                            @foreach($complaint->attachments as $att)
                                <div class="rounded-xl overflow-hidden border border-slate-200 bg-slate-50">
                                    <img src="{{ asset('storage/' . $att->file_path) }}" alt="مرفق البلاغ" class="w-full h-32 object-cover">
                                    <div class="p-2 text-[10px] text-slate-500 truncate text-center">
                                        {{ $att->file_type ?? 'صورة توثيقية' }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar Info -->
            <div class="space-y-6">
                <!-- Metadata Card -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">بيانات البلاغ</h3>
                    
                    <div>
                        <span class="text-[11px] text-slate-400 font-bold block">التصنيف</span>
                        <span class="text-sm font-bold text-slate-800">{{ $complaint->category?->name ?? 'غير محدد' }}</span>
                    </div>

                    <div>
                        <span class="text-[11px] text-slate-400 font-bold block">الجهة الحكومية المسؤولة</span>
                        <span class="text-sm font-bold text-slate-800">
                            {{ $complaint->currentDepartment?->ministry?->name ?? 'الوزارة المختصة' }}
                            - 
                            {{ $complaint->currentDepartment?->name ?? '' }}
                        </span>
                    </div>

                    <div>
                        <span class="text-[11px] text-slate-400 font-bold block">تاريخ التسجيل</span>
                        <span class="text-sm font-bold text-slate-800">{{ $complaint->created_at->translatedFormat('d F Y') }}</span>
                    </div>

                    <div>
                        <span class="text-[11px] text-slate-400 font-bold block">المدينة / المحافظة</span>
                        <span class="text-sm font-bold text-slate-800">{{ $complaint->governorate ?? 'صنعاء - الجمهورية اليمنية' }}</span>
                    </div>
                </div>

                <!-- Back Link -->
                <a href="{{ route('public.complaints.index') }}" 
                   class="block text-center w-full py-3 rounded-xl bg-black text-white text-xs font-bold hover:bg-zinc-800 transition">
                    ← العودة لسجل البلاغات العام
                </a>
            </div>
        </div>
    </div>
</x-public-layout>

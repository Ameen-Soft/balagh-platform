<x-public-layout title="تفاصيل البلاغ #{{ $complaint->complaint_number }} - منصة بادر">
    <!-- Institutional Case Header -->
    <div class="bg-[#09090b] text-white py-10 border-b border-zinc-800">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="flex items-center gap-2 text-xs text-zinc-400 mb-4 font-semibold">
                <a href="{{ url('/') }}" class="hover:text-white transition">الرئيسية</a>
                <span>/</span>
                <a href="{{ route('public.complaints.index') }}" class="hover:text-white transition">سجل البلاغات العام</a>
                <span>/</span>
                <span class="text-zinc-200 font-mono">#{{ $complaint->complaint_number }}</span>
            </nav>

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2.5 mb-2">
                        <span class="text-xs font-mono font-bold text-white bg-zinc-900 px-2.5 py-1 rounded border border-zinc-700">
                            #{{ $complaint->complaint_number }}
                        </span>
                        <span class="text-xs font-bold text-zinc-400">ملف بلاغ عام</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-black text-white leading-snug">{{ $complaint->title }}</h1>
                </div>

                <div class="shrink-0">
                    @if($complaint->status === 'resolved' || $complaint->status === 'closed')
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-bold bg-emerald-950/90 text-emerald-400 border border-emerald-800">
                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                            تم حل البلاغ وإنجازه
                        </span>
                    @elseif($complaint->status === 'in_progress')
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-bold bg-amber-950/90 text-amber-400 border border-amber-800">
                            <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                            جاري العمل الميداني
                        </span>
                    @elseif($complaint->status === 'rejected')
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-bold bg-red-950/90 text-red-400 border border-red-800">
                            <span class="w-2 h-2 rounded-full bg-red-400"></span>
                            مرفوض مع الإفادة
                        </span>
                    @else
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-bold bg-zinc-900 text-zinc-300 border border-zinc-700">
                            <span class="w-2 h-2 rounded-full bg-zinc-400"></span>
                            قيد المراجعة الفنية
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <!-- Main Case Content (8 Cols) -->
            <div class="lg:col-span-8 space-y-6">
                <!-- Description Box -->
                <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                    <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                        <h2 class="text-sm font-black text-slate-900">تفاصيل وموضوع البلاغ</h2>
                        <span class="text-[11px] font-semibold text-slate-500">النص المسجل</span>
                    </div>
                    <div class="p-6">
                        <p class="text-sm text-slate-700 leading-relaxed whitespace-pre-line">{{ $complaint->description }}</p>
                    </div>
                </div>

                <!-- Attachments / Photographic Evidence -->
                @if($complaint->attachments->isNotEmpty())
                    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden"
                         x-data="{ 
                            modalOpen: false, 
                            currentIndex: 0,
                            items: {{ Js::from($complaint->attachments->map(fn($att) => [
                                'url' => asset('storage/' . $att->file_path),
                                'title' => $att->file_type ?? 'صورة توثيقية'
                            ])) }},
                            openModal(index) {
                                this.currentIndex = index;
                                this.modalOpen = true;
                            },
                            next() {
                                if (this.items && this.items.length > 0) {
                                    this.currentIndex = (this.currentIndex + 1) % this.items.length;
                                }
                            },
                            prev() {
                                if (this.items && this.items.length > 0) {
                                    this.currentIndex = (this.currentIndex - 1 + this.items.length) % this.items.length;
                                }
                            }
                         }"
                         @keydown.escape.window="modalOpen = false"
                         @keydown.right.window="if (modalOpen) prev()"
                         @keydown.left.window="if (modalOpen) next()">
                        
                        <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                            <h2 class="text-sm font-black text-slate-900">المرفقات والأدلة المصورة</h2>
                            <span class="text-xs font-mono font-bold text-slate-600">{{ $complaint->attachments->count() }} مرفق</span>
                        </div>

                        <div class="p-6 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                            @foreach($complaint->attachments as $att)
                                <button type="button" 
                                        @click="openModal({{ $loop->index }})"
                                        class="group text-right rounded-lg overflow-hidden border border-slate-200 bg-slate-50 flex flex-col hover:border-[#CE1126]/50 hover:shadow-md transition duration-200 focus:outline-none focus:ring-2 focus:ring-[#CE1126] cursor-pointer">
                                    <div class="relative h-36 overflow-hidden bg-slate-100 w-full">
                                        <img src="{{ asset('storage/' . $att->file_path) }}" alt="مرفق البلاغ" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                        <!-- Hover Magnify Overlay -->
                                        <div class="absolute inset-0 bg-slate-900/30 opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center justify-center">
                                            <span class="p-2 rounded-full bg-white/90 text-slate-900 shadow-md transform scale-90 group-hover:scale-100 transition duration-200">
                                                <svg class="w-5 h-5 text-[#CE1126]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                                                </svg>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="px-3 py-2 bg-white border-t border-slate-200 flex items-center justify-between text-[11px] font-bold text-slate-700 w-full">
                                        <span class="truncate">{{ $att->file_type ?? 'صورة توثيقية' }}</span>
                                        <span class="text-[#CE1126] flex items-center gap-1 group-hover:underline">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            تكبير
                                        </span>
                                    </div>
                                </button>
                            @endforeach
                        </div>

                        <!-- Image Lightbox Modal (Same Page Viewer) -->
                        <div x-show="modalOpen" 
                             x-cloak 
                             x-transition:enter="transition ease-out duration-200" 
                             x-transition:enter-start="opacity-0" 
                             x-transition:enter-end="opacity-100" 
                             x-transition:leave="transition ease-in duration-150" 
                             x-transition:leave-start="opacity-100" 
                             x-transition:leave-end="opacity-0" 
                             @click.self="modalOpen = false"
                             class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-black/90 backdrop-blur-md">
                            
                            <!-- Close Button (Top Left in RTL) -->
                            <button type="button" 
                                    @click.stop="modalOpen = false" 
                                    class="absolute top-4 left-4 z-20 p-2.5 rounded-full bg-white/10 hover:bg-white/20 text-white transition focus:outline-none"
                                    title="إغلاق (Esc)">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>

                            <!-- Navigation: Previous (in RTL, Right Arrow goes to previous) -->
                            <button type="button" 
                                    x-show="items && items.length > 1"
                                    @click.stop="prev()" 
                                    class="absolute right-3 sm:right-6 top-1/2 -translate-y-1/2 z-20 p-3 rounded-full bg-white/15 hover:bg-white/30 text-white shadow-lg transition active:scale-95 focus:outline-none"
                                    title="الصورة السابقة (السهم الأيمن)">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>

                            <!-- Navigation: Next (in RTL, Left Arrow goes to next) -->
                            <button type="button" 
                                    x-show="items && items.length > 1"
                                    @click.stop="next()" 
                                    class="absolute left-3 sm:left-6 top-1/2 -translate-y-1/2 z-20 p-3 rounded-full bg-white/15 hover:bg-white/30 text-white shadow-lg transition active:scale-95 focus:outline-none"
                                    title="الصورة التالية (السهم الأيسر)">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                                </svg>
                            </button>

                            <!-- Modal Content & Active Image -->
                            <div class="relative max-w-4xl max-h-[92vh] flex flex-col items-center justify-center z-10 w-full select-none">
                                <img :src="items[currentIndex]?.url" 
                                     :alt="items[currentIndex]?.title" 
                                     class="max-h-[78vh] max-w-full rounded-xl shadow-2xl object-contain border border-white/10 bg-black/40">
                                
                                <!-- Bottom Meta & Actions Bar -->
                                <div class="mt-3.5 flex flex-wrap items-center justify-between w-full max-w-2xl px-3 py-2 rounded-lg bg-neutral-900/80 border border-neutral-700/60 text-white gap-3">
                                    <div class="flex items-center gap-3">
                                        <span class="text-xs font-bold text-neutral-200" x-text="items[currentIndex]?.title"></span>
                                        <template x-if="items && items.length > 1">
                                            <span class="text-[11px] font-mono text-neutral-400 bg-neutral-800 px-2 py-0.5 rounded" x-text="(currentIndex + 1) + ' / ' + items.length"></span>
                                        </template>
                                    </div>
                                    <a :href="items[currentIndex]?.url" 
                                       download 
                                       class="text-xs text-neutral-300 hover:text-white flex items-center gap-1.5 bg-white/10 hover:bg-white/20 px-3 py-1.5 rounded-md transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                        <span>تحميل</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Official Audit Timeline -->
                <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                    <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                        <h2 class="text-sm font-black text-slate-900">السجل الزمني للإجراءات والمعالجة</h2>
                        <span class="text-[11px] font-semibold text-slate-500">تتبع رسمي</span>
                    </div>
                    <div class="p-6">
                        <div class="space-y-6 relative before:absolute before:right-3 before:top-2 before:bottom-2 before:w-px before:bg-slate-200">
                            @forelse($complaint->timelines as $timeline)
                                <div class="relative pr-8">
                                    <div class="absolute right-1.5 top-1.5 w-3 h-3 rounded-full bg-white border-2 border-[#CE1126]"></div>
                                    <div class="p-4 rounded-lg bg-[#F8FAFC] border border-slate-200/80">
                                        <div class="flex flex-wrap items-center justify-between gap-2 mb-1">
                                            <p class="text-xs font-black text-slate-900">{{ $timeline->action }}</p>
                                            <span class="text-[11px] font-mono text-slate-500">{{ $timeline->created_at->translatedFormat('d F Y - h:i a') }}</span>
                                        </div>
                                        @if($timeline->notes)
                                            <p class="text-xs text-slate-600 mt-1.5 leading-relaxed">{{ $timeline->notes }}</p>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <p class="text-xs font-semibold text-slate-500 py-2">تم قيد البلاغ في النظام وبانتظار التحديثات الإجرائية من الجهة المختصة.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar Specification Table (4 Cols) -->
            <div class="lg:col-span-4 space-y-5">
                <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                    <div class="px-5 py-4 bg-[#08080a] text-white">
                        <h3 class="text-xs font-bold uppercase tracking-wider">بطاقة تعريف البلاغ</h3>
                    </div>
                    
                    <div class="divide-y divide-slate-200 text-xs">
                        <div class="p-4 flex flex-col gap-1">
                            <span class="text-slate-500 font-semibold">التصنيف الخدمي</span>
                            <span class="font-black text-slate-900 text-sm">{{ $complaint->category?->name ?? 'غير محدد' }}</span>
                        </div>

                        <div class="p-4 flex flex-col gap-1">
                            <span class="text-slate-500 font-semibold">الجهة الحكومية المختصة</span>
                            <span class="font-black text-slate-900 text-sm">
                                {{ $complaint->currentDepartment?->ministry?->name ?? 'الوزارة المختصة' }}
                            </span>
                            @if($complaint->currentDepartment?->name)
                                <span class="text-slate-600 font-semibold">{{ $complaint->currentDepartment->name }}</span>
                            @endif
                        </div>

                        <div class="p-4 flex flex-col gap-1">
                            <span class="text-slate-500 font-semibold">تاريخ القيد والتسجيل</span>
                            <span class="font-bold text-slate-900 font-mono">{{ $complaint->created_at->translatedFormat('d F Y') }}</span>
                        </div>

                        <div class="p-4 flex flex-col gap-1">
                            <span class="text-slate-500 font-semibold">النطاق الجغرافي</span>
                            <span class="font-bold text-slate-900">{{ $complaint->governorate ?? 'صنعاء - الجمهورية اليمنية' }}</span>
                        </div>
                    </div>
                </div>

                <a href="{{ route('public.complaints.index') }}" 
                   class="flex items-center justify-center gap-2 w-full py-3 px-4 rounded-lg bg-white border border-slate-300 text-slate-900 text-xs font-bold hover:bg-slate-100 transition">
                    <span>← العودة لسجل البلاغات العام</span>
                </a>
            </div>

        </div>
    </div>
</x-public-layout>
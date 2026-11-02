<x-public-layout title="سجل الشفافية العامة للبلاغات والشكاوى - منصة بادر">
    <!-- Institutional Page Header -->
    <div class="bg-[#09090b] text-white py-12 border-b border-zinc-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="flex items-center gap-2 text-xs text-zinc-400 mb-4 font-semibold">
                <a href="{{ url('/') }}" class="hover:text-white transition">الرئيسية</a>
                <span>/</span>
                <span class="text-red-400">سجل البلاغات العام</span>
            </nav>
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div class="max-w-2xl">
                    <h1 class="text-2xl sm:text-4xl font-black text-white">سجل البلاغات والشكاوى العام</h1>
                    <p class="text-zinc-400 text-xs sm:text-sm mt-2 leading-relaxed">
                        قاعدة بيانات مفتوحة تتيح للمواطنين متابعة كفاءة الوزارات والجهات الخدمية في معالجة البلاغات الميدانية مع الحفاظ الكامل على سرية بيانات مقدمي البلاغات.
                    </p>
                </div>
                <div class="shrink-0">
                    <span class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-zinc-900 border border-zinc-800 text-xs font-bold text-zinc-300">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span>سجل رقابي مفتوح</span>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <!-- Structured Filter Control Panel -->
        <div class="bg-white rounded-xl border border-slate-200 p-5 mb-8 shadow-2xs">
            <form method="GET" action="{{ route('public.complaints.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                <!-- Search Input (6 Cols) -->
                <div class="md:col-span-6">
                    <label class="block text-xs font-bold text-slate-800 mb-1.5">البحث بالرقم المرجعي أو موضوع البلاغ</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="مثال: CMP-000015 أو هبوط إسفلتي..." 
                               class="w-full pl-4 pr-10 py-2.5 rounded-lg bg-[#F8FAFC] border border-slate-300 text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-[#CE1126] focus:border-[#CE1126] transition">
                        <svg class="w-4 h-4 text-slate-400 absolute right-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>

                <!-- Category Dropdown (4 Cols) -->
                <div class="md:col-span-4">
                    <label class="block text-xs font-bold text-slate-800 mb-1.5">التصنيف الخدمي</label>
                    <select name="category_id" class="w-full py-2.5 px-3 rounded-lg bg-[#F8FAFC] border border-slate-300 text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-[#CE1126] focus:border-[#CE1126] transition">
                        <option value="">كافة القطاعات والتصنيفات</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Submit & Reset Actions (2 Cols) -->
                <div class="md:col-span-2 flex items-center gap-2">
                    <button type="submit" class="flex-1 py-2.5 px-4 rounded-lg bg-[#09090b] text-white font-bold text-xs hover:bg-zinc-800 transition h-[42px]">
                        تطبيق الفلتر
                    </button>
                    @if(request()->hasAny(['search', 'category_id', 'status']))
                        <a href="{{ route('public.complaints.index') }}" 
                           title="إعادة تعيين"
                           class="inline-flex items-center justify-center px-3 rounded-lg bg-slate-100 text-slate-700 text-xs font-bold hover:bg-slate-200 border border-slate-200 transition h-[42px]">
                            إلغاء
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Complaints Dossier Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($complaints as $complaint)
                <article class="bg-white rounded-xl border border-slate-200 hover:border-slate-300 transition flex flex-col justify-between overflow-hidden">
                    <div class="p-6">
                        <!-- Top Bar: Reference & Status -->
                        <div class="flex items-center justify-between gap-2 pb-3.5 mb-4 border-b border-slate-100">
                            <span class="text-xs font-mono font-bold text-slate-800 bg-slate-100 px-2.5 py-1 rounded border border-slate-200/60">
                                #{{ $complaint->complaint_number }}
                            </span>

                            @if($complaint->status === 'resolved' || $complaint->status === 'closed')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                    معالج بنجاح
                                </span>
                            @elseif($complaint->status === 'in_progress')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span>
                                    قيد العمل الميداني
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-500"></span>
                                    قيد المراجعة الفنية
                                </span>
                            @endif
                        </div>

                        <!-- Title & Summary -->
                        <h2 class="text-base font-black text-slate-900 mb-2 line-clamp-1">
                            {{ $complaint->title }}
                        </h2>
                        <p class="text-xs text-slate-600 line-clamp-3 leading-relaxed mb-5">
                            {{ $complaint->description }}
                        </p>

                        <!-- Structured Taxonomy Tags -->
                        <div class="flex flex-wrap items-center gap-1.5 text-[11px] font-bold">
                            <span class="px-2.5 py-1 rounded bg-[#F8FAFC] text-slate-700 border border-slate-200">
                                {{ $complaint->category?->name ?? 'بلاغ عام' }}
                            </span>
                            <span class="px-2.5 py-1 rounded bg-[#F8FAFC] text-slate-600 border border-slate-200 truncate max-w-[180px]">
                                {{ $complaint->currentDepartment?->ministry?->name ?? 'الجهة المختصة' }}
                            </span>
                        </div>
                    </div>

                    <!-- Card Footer -->
                    <div class="px-6 py-3.5 bg-slate-50 border-t border-slate-200/80 flex items-center justify-between text-xs">
                        <span class="text-slate-500 font-mono font-semibold">{{ $complaint->created_at->format('Y-m-d') }}</span>
                        <a href="{{ route('public.complaints.show', $complaint->id) }}" class="font-bold text-[#CE1126] hover:text-[#B70E20] flex items-center gap-1">
                            <span>استعراض الملف</span>
                            <span>←</span>
                        </a>
                    </div>
                </article>
            @empty
                <div class="col-span-3 text-center py-16 bg-white rounded-xl border border-slate-200">
                    <p class="text-slate-600 font-bold text-sm">لا توجد بلاغات مطابقة لمعايير البحث المحددة.</p>
                    <a href="{{ route('public.complaints.index') }}" class="mt-3 inline-block text-xs font-bold text-[#CE1126] hover:underline">عرض كافة البلاغات</a>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $complaints->withQueryString()->links() }}
        </div>
    </div>
</x-public-layout>
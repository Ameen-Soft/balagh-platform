<x-public-layout title="سجل الشفافية العامة للبلاغات والشكاوى - منصة بادر">
    <div class="bg-gradient-to-b from-black via-[#08080a] to-[#0d0d11] text-white pt-28 pb-12 border-b border-zinc-800/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl">
                <span class="text-xs font-bold text-red-500 uppercase tracking-wide">الشفافية والرقابة المجتمعية</span>
                <h1 class="text-3xl sm:text-4xl font-black mt-2 text-white">سجل البلاغات والشكاوى العام</h1>
                <p class="text-slate-400 text-sm mt-2 leading-relaxed">
                    منصة مفتوحة تتيح للمواطنين متابعة أداء الوزارات والجهات الحكومية في معالجة القضايا والشكاوى الخدمية مع حماية الخصوصية الفردية للمواطنين.
                </p>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <!-- Filter and Search Bar -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-8 shadow-xs">
            <form method="GET" action="{{ route('public.complaints.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">البحث برقم البلاغ أو العنوان</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="ابحث عن بلاغ معين..." 
                               class="w-full pl-4 pr-10 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500">
                        <svg class="w-5 h-5 text-slate-400 absolute right-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>

                <!-- Category -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">التصنيف الخدمي</label>
                    <select name="category_id" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500">
                        <option value="">جميع التصنيفات</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Button -->
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 py-2.5 px-4 rounded-xl bg-black text-white font-bold text-sm hover:bg-zinc-800 transition">
                        تصفية النتائج
                    </button>
                    @if(request()->hasAny(['search', 'category_id', 'status']))
                        <a href="{{ route('public.complaints.index') }}" class="py-2.5 px-3 rounded-xl bg-slate-100 text-slate-600 text-sm font-bold hover:bg-slate-200 transition">
                            إلغاء
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Complaints Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($complaints as $complaint)
                <div class="bg-white rounded-2xl border border-slate-200 p-6 flex flex-col justify-between shadow-xs hover:shadow-md transition">
                    <div>
                        <!-- Header / Badges -->
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="text-xs font-mono font-bold text-slate-500">#{{ $complaint->complaint_number }}</span>
                            @if($complaint->status === 'resolved' || $complaint->status === 'closed')
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    معالج بنجاح
                                </span>
                            @elseif($complaint->status === 'in_progress')
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    جاري العمل الميداني
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                    قيد المراجعة الفنية
                                </span>
                            @endif
                        </div>

                        <!-- Title -->
                        <h3 class="text-base font-bold text-slate-900 mb-2 line-clamp-1">
                            {{ $complaint->title }}
                        </h3>

                        <!-- Description -->
                        <p class="text-xs text-slate-600 line-clamp-3 leading-relaxed mb-4">
                            {{ $complaint->description }}
                        </p>

                        <!-- Category & Privacy Masked Info -->
                        <div class="flex flex-wrap items-center gap-2 text-[11px] text-slate-700 font-semibold mb-4">
                            <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700">
                                {{ $complaint->category?->name ?? 'بلاغ عام' }}
                            </span>
                            <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700">
                                {{ $complaint->currentDepartment?->ministry?->name ?? 'الجهة المختصة' }}
                            </span>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs">
                        <span class="text-slate-600 font-medium">{{ $complaint->created_at->format('Y-m-d') }}</span>
                        <a href="{{ route('public.complaints.show', $complaint->id) }}" class="font-bold text-red-600 hover:text-red-700 flex items-center gap-1">
                            <span>ملف البلاغ</span>
                            <span class="text-sm">←</span>
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-3 text-center py-16 bg-white rounded-2xl border border-slate-200">
                    <p class="text-slate-500 font-bold">لا توجد بلاغات تطابق معايير البحث الحالية.</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $complaints->withQueryString()->links() }}
        </div>
    </div>
</x-public-layout>

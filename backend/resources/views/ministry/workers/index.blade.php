<x-ministry-layout>
    <div class="space-y-6">
        <!-- Header & Breadcrumbs -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-1">
                    <a href="{{ route('ministry.dashboard') }}" class="hover:text-red-600 transition">لوحة المؤشرات</a>
                    <span>/</span>
                    <span class="text-slate-700">الموظفون الميدانيون</span>
                </div>
                <h1 class="text-xl font-black text-slate-900 tracking-tight">إدارة الموظفين الميدانيين</h1>
                <p class="text-xs text-slate-500 mt-1">
                    إدارة الكادر الهندسي والميداني التابع لقسم 
                    <span class="font-bold text-slate-700">[{{ $admin->department?->name }}]</span>
                    وترقية المواطنين المؤهلين لمباشرة المعاينات الميدانية.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <div class="px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200/80 text-left">
                    <span class="text-[11px] font-semibold text-slate-400 block">إجمالي موظفي القسم</span>
                    <span class="text-lg font-black text-slate-900">{{ $workers->count() }}</span>
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center gap-3">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-xs font-bold flex items-center gap-3">
                <svg class="w-5 h-5 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Section 1: Active Field Workers in This Department -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-black text-slate-900">الكادر الميداني الحالي بالقسم</h2>
                    <p class="text-xs text-slate-500 mt-0.5">الموظفون المعتمدون لاستقبال تكليفات البلاغات والمعاينة الجغرافية.</p>
                </div>
                <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-red-50 text-red-700 border border-red-100">
                    {{ $workers->count() }} موظف معتمد
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-right text-xs">
                    <thead>
                        <tr class="bg-slate-50/80 text-slate-500 border-b border-slate-100">
                            <th class="py-3 px-4 font-bold">الموظف الميداني</th>
                            <th class="py-3 px-4 font-bold">بيانات الاتصال</th>
                            <th class="py-3 px-4 font-bold">الرقم الوطني</th>
                            <th class="py-3 px-4 font-bold text-center">المهام النشطة</th>
                            <th class="py-3 px-4 font-bold text-center">المهام المنجزة</th>
                            <th class="py-3 px-4 font-bold text-center">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($workers as $worker)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-slate-900 text-amber-400 font-black flex items-center justify-center text-xs shrink-0">
                                            {{ mb_substr($worker->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-900">{{ $worker->name }}</p>
                                            <p class="text-[11px] text-slate-400">انضم: {{ $worker->created_at->format('Y/m/d') }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <p class="font-mono text-slate-800 font-semibold">{{ $worker->phone }}</p>
                                    <p class="text-[11px] text-slate-400 font-mono">{{ $worker->email }}</p>
                                </td>
                                <td class="py-3.5 px-4 font-mono text-slate-700">
                                    {{ $worker->national_id }}
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if($worker->active_tasks_count > 0)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800">
                                            {{ $worker->active_tasks_count }} نشطة
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600">
                                            0
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">
                                        {{ $worker->completed_tasks_count }} مكتملة
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if($worker->active_tasks_count > 0)
                                        <span class="text-[11px] text-amber-700 bg-amber-50 px-2 py-1 rounded border border-amber-200/80 cursor-help" title="لا يمكن إلغاء التعيين قبل إتمام المهام النشطة">
                                            لديه مهام نشطة
                                        </span>
                                    @else
                                        <form method="POST" action="{{ route('ministry.workers.demote', $worker->id) }}" onsubmit="return confirm('هل أنت متأكد من إلغاء تعيين الموظف وإعادته إلى مواطن؟');" class="inline">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 rounded-lg text-[11px] font-bold text-red-600 hover:text-white hover:bg-red-600 border border-red-200 transition">
                                                إلغاء التعيين
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-400">
                                    لا يوجد موظفون ميدانيون مسجلون في هذا القسم حالياً. يمكنك ترقية مواطن من القائمة أدناه.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Section 2: Search & Promote Citizens -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden"
             x-data="{ quickCitizenId: '', quickCitizenName: '' }"
             @user-selected="quickCitizenId = $event.detail.id; quickCitizenName = $event.detail.user.name"
             @user-cleared="quickCitizenId = ''; quickCitizenName = ''">
            
            <div class="p-5 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-sm font-black text-slate-900">ترقية حساب مواطن إلى موظف ميداني</h2>
                    <p class="text-xs text-slate-500 mt-0.5">البحث السريع المباشر في الحسابات لتعيينهم كموظفين ميدانيين بالقسم فوراً.</p>
                </div>
            </div>

            <!-- Quick Instant Autocomplete Promotion Bar -->
            <div class="p-5 bg-slate-50/60 border-b border-slate-100">
                <form method="POST" 
                      :action="'{{ url('/ministry/workers') }}/' + quickCitizenId + '/promote'" 
                      @submit="if(!confirm('هل تريد ترقية المواطن [' + (quickCitizenName || 'المحدد') + '] إلى موظف ميداني تابع لهذا القسم؟')) $event.preventDefault()"
                      class="flex flex-col sm:flex-row items-end gap-3 max-w-2xl">
                    @csrf
                    <div class="flex-1 w-full">
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">البحث التفاعلي اللحظي عن مواطن (بالاسم، الهاتف، أو الرقم الوطني)</label>
                        <x-user-search-picker 
                            name="selected_citizen_id" 
                            scope="citizens_for_promotion" 
                            placeholder="اكتب اسم المواطن، رقم الهاتف أو الرقم الوطني للبحث الفوري..." 
                            :required="false" />
                    </div>
                    <button type="submit" 
                            :disabled="!quickCitizenId"
                            :class="quickCitizenId ? 'bg-emerald-600 hover:bg-emerald-700 text-white cursor-pointer shadow-xs' : 'bg-slate-200 text-slate-400 cursor-not-allowed'"
                            class="py-2.5 px-5 rounded-xl text-xs font-black transition shrink-0 flex items-center justify-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        <span>ترقية الحساب المختار</span>
                    </button>
                </form>
            </div>

            <div class="p-5 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h3 class="text-xs font-bold text-slate-700">قائمة المواطنين المؤهلين</h3>
                    <p class="text-[11px] text-slate-400">استعراض وتصفح الحسابات المسجلة للترقية الميدانية</p>
                </div>
                <form method="GET" action="{{ route('ministry.workers.index') }}" class="flex items-center gap-2">
                    <input type="text" name="search" value="{{ $search }}" placeholder="بحث بالاسم، الهاتف، الرقم الوطني..." 
                           class="py-2 px-3.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500 w-64">
                    <button type="submit" class="py-2 px-4 rounded-xl bg-black text-white text-xs font-bold hover:bg-zinc-800 transition">
                        تصفية
                    </button>
                    @if($search)
                        <a href="{{ route('ministry.workers.index') }}" class="py-2 px-3 rounded-xl bg-slate-100 text-slate-600 text-xs font-bold hover:bg-slate-200 transition">
                            إلغاء
                        </a>
                    @endif
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-right text-xs">
                    <thead>
                        <tr class="bg-slate-50/80 text-slate-500 border-b border-slate-100">
                            <th class="py-3 px-4 font-bold">اسم المواطن</th>
                            <th class="py-3 px-4 font-bold">الهاتف</th>
                            <th class="py-3 px-4 font-bold">البريد الإلكتروني</th>
                            <th class="py-3 px-4 font-bold">الرقم الوطني</th>
                            <th class="py-3 px-4 font-bold text-center">الإجراء</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($citizens as $citizen)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="py-3.5 px-4 font-bold text-slate-900">
                                    {{ $citizen->name }}
                                </td>
                                <td class="py-3.5 px-4 font-mono text-slate-700">
                                    {{ $citizen->phone }}
                                </td>
                                <td class="py-3.5 px-4 font-mono text-slate-600">
                                    {{ $citizen->email }}
                                </td>
                                <td class="py-3.5 px-4 font-mono text-slate-600">
                                    {{ $citizen->national_id }}
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <form method="POST" action="{{ route('ministry.workers.promote', $citizen->id) }}" onsubmit="return confirm('هل تريد ترقية [{{ $citizen->name }}] إلى موظف ميداني تابع لهذا القسم؟');" class="inline">
                                        @csrf
                                        <button type="submit" class="px-3.5 py-1.5 rounded-lg text-[11px] font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-600 hover:text-white border border-emerald-200 transition">
                                            + ترقية إلى موظف ميداني
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-400">
                                    @if($search)
                                        لا توجد نتائج مطابقة لبحثك: "{{ $search }}".
                                    @else
                                        لا يوجد مواطنون متاحون للترقية حالياً.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($citizens->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $citizens->links() }}
                </div>
            @endif
        </div>
    </div>
</x-ministry-layout>

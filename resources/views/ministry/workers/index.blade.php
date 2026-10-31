<x-ministry-layout title="إدارة الموظفين الميدانيين">
    <div class="space-y-6" x-data="{ 
        createModalOpen: false, 
        editModalOpen: false,
        editWorkerId: null,
        editWorkerName: '',
        editWorkerEmail: '',
        editWorkerPhone: '',
        editWorkerNationalId: '',
        editWorkerDeptId: '',
        editWorkerActiveTasks: 0,
        editActionUrl: ''
    }">
        <!-- Header & Breadcrumbs -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-1">
                    <a href="{{ route('ministry.dashboard') }}" class="hover:text-red-600 transition">لوحة المؤشرات</a>
                    <span>/</span>
                    <span class="text-slate-700">الموظفون الميدانيون</span>
                </div>
                <h1 class="text-xl font-black text-slate-900 tracking-tight">إدارة الموظفين الميدانيين بالوزارة</h1>
                <p class="text-xs text-slate-500 mt-1">
                    إدارة الكادر الهندسي والميداني عبر جميع الإدارات الخدمية التابعة لـ
                    <span class="font-bold text-slate-700">[{{ $admin->department?->ministry?->name }}]</span>
                </p>
            </div>
            <div class="flex items-center gap-3">
                <button type="button" @click="createModalOpen = true"
                        class="px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-black text-xs transition flex items-center gap-2 shadow-sm shadow-red-600/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>+ إضافة موظف ميداني جديد</span>
                </button>
                <div class="px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200/80 text-left">
                    <span class="text-[11px] font-semibold text-slate-400 block">إجمالي كادر الوزارة</span>
                    <span class="text-lg font-black text-slate-900 font-mono">{{ $workers->count() }}</span>
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

        @if($errors->any())
            <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-xs font-bold space-y-1">
                @foreach($errors->all() as $err)
                    <p>• {{ $err }}</p>
                @endforeach
            </div>
        @endif

        <!-- Filter Bar by Department -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-slate-700">تصفية حسب الإدارة الخدمية:</span>
            </div>
            <form method="GET" action="{{ route('ministry.workers.index') }}" class="flex items-center gap-2">
                <select name="department_id" onchange="this.form.submit()" 
                        class="py-2 px-3.5 rounded-xl border border-slate-300 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-red-500 bg-slate-50 min-w-[220px]">
                    <option value="">كل الإدارات الخدمية (الكل)</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ (string)$selectedDepartmentId === (string)$dept->id ? 'selected' : '' }}>
                            {{ $dept->name }} {{ ! $dept->is_active ? '(معطلة)' : '' }}
                        </option>
                    @endforeach
                </select>
                @if($selectedDepartmentId)
                    <a href="{{ route('ministry.workers.index') }}" class="py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold transition">
                        إلغاء التصفية
                    </a>
                @endif
            </form>
        </div>

        <!-- Section 1: Active & Listed Field Workers Table -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-black text-slate-900">الكادر الميداني الحالي</h2>
                    <p class="text-xs text-slate-500 mt-0.5">الموظفون المعتمدون لاستقبال تكليفات البلاغات والمعاينة الميدانية الجغرافية.</p>
                </div>
                <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-red-50 text-red-700 border border-red-100 font-mono">
                    {{ $workers->count() }} موظف معتمد
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-right text-xs">
                    <thead>
                        <tr class="bg-slate-50/80 text-slate-500 border-b border-slate-100">
                            <th class="py-3 px-4 font-bold">الموظف الميداني</th>
                            <th class="py-3 px-4 font-bold">الإدارة الخدمية التابع لها</th>
                            <th class="py-3 px-4 font-bold">بيانات الاتصال</th>
                            <th class="py-3 px-4 font-bold">الرقم الوطني</th>
                            <th class="py-3 px-4 font-bold text-center">حالة الحساب</th>
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
                                            <a href="{{ route('ministry.workers.show', $worker->id) }}" class="font-bold text-slate-900 hover:text-red-600 transition block">
                                                {{ $worker->name }}
                                            </a>
                                            <p class="text-[11px] text-slate-400">انضم: {{ $worker->created_at?->format('Y/m/d') }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                        {{ $worker->department?->name ?? 'غير محدد' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <p class="font-mono text-slate-800 font-semibold">{{ $worker->phone }}</p>
                                    <p class="text-[11px] text-slate-400 font-mono">{{ $worker->email }}</p>
                                </td>
                                <td class="py-3.5 px-4 font-mono text-slate-700">
                                    {{ $worker->national_id ?? '-' }}
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold {{ $worker->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                        {{ $worker->is_active ? 'نشط' : 'معطل' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if($worker->active_tasks_count > 0)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 font-mono">
                                            {{ $worker->active_tasks_count }} نشطة
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600 font-mono">
                                            0
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 font-mono">
                                        {{ $worker->completed_tasks_count }} مكتملة
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <!-- View Details -->
                                        <a href="{{ route('ministry.workers.show', $worker->id) }}" 
                                           class="px-2.5 py-1.5 rounded-lg text-[11px] font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 border border-slate-200 transition"
                                           title="عرض ملف الموظف وسجل المهام">
                                            تفاصيل
                                        </a>

                                        <!-- Edit -->
                                        <button type="button"
                                                @click="editWorkerId = {{ $worker->id }};
                                                        editWorkerName = '{{ addslashes($worker->name) }}';
                                                        editWorkerEmail = '{{ addslashes($worker->email) }}';
                                                        editWorkerPhone = '{{ addslashes($worker->phone ?? '') }}';
                                                        editWorkerNationalId = '{{ addslashes($worker->national_id ?? '') }}';
                                                        editWorkerDeptId = '{{ $worker->department_id }}';
                                                        editWorkerActiveTasks = {{ (int) $worker->active_tasks_count }};
                                                        editActionUrl = '{{ route('ministry.workers.update', $worker->id) }}';
                                                        editModalOpen = true;"
                                                class="px-2.5 py-1.5 rounded-lg text-[11px] font-bold text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-200 transition"
                                                title="تعديل بيانات الموظف">
                                            تعديل
                                        </button>

                                        <!-- Toggle Active -->
                                        <form method="POST" action="{{ route('ministry.workers.toggle_active', $worker->id) }}" class="inline"
                                              onsubmit="return confirm('{{ $worker->is_active ? 'هل تريد تعطيل حساب الموظف؟ سيتم فوراً إبطال جميع جلساته ورموزه النشطة ومنعه من استلام مهام جديدة.' : 'هل تريد إعادة تفعيل حساب الموظف؟' }}');">
                                            @csrf
                                            <button type="submit" 
                                                    class="px-2.5 py-1.5 rounded-lg text-[11px] font-bold border transition {{ $worker->is_active ? 'text-amber-800 bg-amber-50 hover:bg-amber-100 border-amber-200' : 'text-emerald-800 bg-emerald-50 hover:bg-emerald-100 border-emerald-200' }}">
                                                {{ $worker->is_active ? 'تعطيل' : 'تفعيل' }}
                                            </button>
                                        </form>

                                        <!-- Demote or Delete -->
                                        @if($worker->active_tasks_count > 0)
                                            <span class="text-[10px] text-amber-700 bg-amber-50 px-2 py-1 rounded border border-amber-200 cursor-help" title="لا يمكن إلغاء التعيين أو الحذف لوجود مهام نشطة قيد التنفيذ">
                                                مهام نشطة
                                            </span>
                                        @else
                                            <form method="POST" action="{{ route('ministry.workers.demote', $worker->id) }}" class="inline"
                                                  onsubmit="return confirm('هل تريد إلغاء تعيين الموظف وإعادته لحساب مواطن؟');">
                                                @csrf
                                                <button type="submit" class="px-2 py-1.5 rounded-lg text-[11px] font-bold text-slate-600 hover:text-red-600 hover:bg-red-50 border border-slate-200 transition" title="إلغاء التعيين وإعادته كمواطن">
                                                    إلغاء صفة
                                                </button>
                                            </form>

                                            <form method="POST" action="{{ route('ministry.workers.destroy', $worker->id) }}" class="inline"
                                                  onsubmit="return confirm('هل تريد حذف حساب الموظف نهائياً؟ تنبيه: لا يُسمح بالحذف إلا إذا كان حسابه خالياً من أي مهام سابقة.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-2 py-1.5 rounded-lg text-[11px] font-bold text-red-600 hover:text-white hover:bg-red-600 border border-red-200 transition" title="حذف نهائي (مشروط)">
                                                    حذف
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-8 text-center text-slate-400">
                                    لا يوجد موظفون ميدانيون مسجلون في هذا النطاق حالياً. يمكنك إضافة موظف جديد أو ترقية مواطن أدناه.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Section 2: Search & Promote Citizens -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden"
             x-data="{ quickCitizenId: '', quickCitizenName: '', promoDeptId: '{{ $departments->where('is_active', true)->first()?->id }}' }"
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
                      @submit="if(!confirm('هل تريد ترقية المواطن [' + (quickCitizenName || 'المحدد') + '] إلى موظف ميداني تابع للوزارة؟')) $event.preventDefault()"
                      class="flex flex-col sm:flex-row items-end gap-3 max-w-4xl">
                    @csrf
                    <div class="flex-1 w-full">
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">البحث التفاعلي اللحظي عن مواطن (بالاسم، الهاتف، أو الرقم الوطني)</label>
                        <x-user-search-picker 
                            name="selected_citizen_id" 
                            scope="citizens_for_promotion" 
                            placeholder="اكتب اسم المواطن، رقم الهاتف أو الرقم الوطني للبحث الفوري..." 
                            :required="false" />
                    </div>

                    <div class="w-full sm:w-64">
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">الإدارة الخدمية المسند إليها</label>
                        <select name="department_id" x-model="promoDeptId" required class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs font-bold bg-white focus:ring-2 focus:ring-red-500">
                            @foreach($departments->where('is_active', true) as $d)
                                <option value="{{ $d->id }}">{{ $d->name }}</option>
                            @endforeach
                        </select>
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
                    @if($selectedDepartmentId)
                        <input type="hidden" name="department_id" value="{{ $selectedDepartmentId }}">
                    @endif
                    <input type="text" name="search" value="{{ $search }}" placeholder="بحث بالاسم، الهاتف، الرقم الوطني..." 
                           class="py-2 px-3.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500 w-64">
                    <button type="submit" class="py-2 px-4 rounded-xl bg-black text-white text-xs font-bold hover:bg-zinc-800 transition">
                        تصفية
                    </button>
                    @if($search)
                        <a href="{{ route('ministry.workers.index', ['department_id' => $selectedDepartmentId]) }}" class="py-2 px-3 rounded-xl bg-slate-100 text-slate-600 text-xs font-bold hover:bg-slate-200 transition">
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

        <!-- Create Worker Modal -->
        <div x-show="createModalOpen" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto bg-black/70 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl border border-slate-200 p-6 max-w-lg w-full shadow-2xl relative"
                 @click.away="createModalOpen = false">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                    <h3 class="text-sm font-black text-slate-900 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-red-600"></span>
                        إنشاء حساب موظف ميداني رسمي
                    </h3>
                    <button type="button" @click="createModalOpen = false" class="text-slate-400 hover:text-slate-700 text-xs font-bold">✕</button>
                </div>

                <form method="POST" action="{{ route('ministry.workers.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">الاسم الرباعي للموظف <span class="text-red-500">*</span></label>
                        <input type="text" name="name" required placeholder="مثال: م. علي صالح الأحمدي"
                               class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500 transition">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">البريد الإلكتروني الرسمي <span class="text-red-500">*</span></label>
                            <input type="email" name="email" required placeholder="worker@ministry.gov"
                                   class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500 transition font-mono">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">رقم الهاتف <span class="text-red-500">*</span></label>
                            <input type="text" name="phone" required placeholder="777123456"
                                   class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500 transition font-mono">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">الرقم الوطني (الهوية)</label>
                            <input type="text" name="national_id" placeholder="01010000000"
                                   class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500 transition font-mono">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">الإدارة الخدمية <span class="text-red-500">*</span></label>
                            <select name="department_id" required class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 text-xs font-bold focus:ring-2 focus:ring-red-500 bg-white">
                                <option value="">اختر الإدارة الخدمية...</option>
                                @foreach($departments->where('is_active', true) as $d)
                                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">كلمة المرور الأولية (لا تقل عن 8 أحرف) <span class="text-red-500">*</span></label>
                        <input type="password" name="password" required minlength="8" placeholder="••••••••"
                               class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500 transition">
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="createModalOpen = false"
                                class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold hover:bg-slate-200 transition">
                            إلغاء
                        </button>
                        <button type="submit"
                                class="px-5 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white font-black text-xs transition shadow-sm">
                            + حفظ وإنشاء الموظف
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Worker Modal -->
        <div x-show="editModalOpen" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto bg-black/70 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl border border-slate-200 p-6 max-w-lg w-full shadow-2xl relative"
                 @click.away="editModalOpen = false">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                    <h3 class="text-sm font-black text-slate-900 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                        تعديل بيانات الموظف الميداني
                    </h3>
                    <button type="button" @click="editModalOpen = false" class="text-slate-400 hover:text-slate-700 text-xs font-bold">✕</button>
                </div>

                <form method="POST" :action="editActionUrl" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">الاسم الرباعي</label>
                        <input type="text" name="name" x-model="editWorkerName" required
                               class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500 transition">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">البريد الإلكتروني</label>
                            <input type="email" name="email" x-model="editWorkerEmail" required
                                   class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500 transition font-mono">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">رقم الهاتف</label>
                            <input type="text" name="phone" x-model="editWorkerPhone" required
                                   class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500 transition font-mono">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">الرقم الوطني</label>
                            <input type="text" name="national_id" x-model="editWorkerNationalId"
                                   class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500 transition font-mono">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">الإدارة الخدمية المسند إليها</label>
                            <select name="department_id" x-model="editWorkerDeptId" required class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 text-xs font-bold focus:ring-2 focus:ring-red-500 bg-white">
                                @foreach($departments->where('is_active', true) as $d)
                                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                                @endforeach
                            </select>
                            <template x-if="editWorkerActiveTasks > 0">
                                <p class="text-[10px] text-amber-600 font-bold mt-1">
                                    تنبيه: لدى الموظف مهام نشطة قيد التنفيذ، لذا لا يمكن تغيير إدارته الخدمية حتى إنهاء المهام.
                                </p>
                            </template>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">تغيير كلمة المرور (اترك الحقل فارغاً إذا كنت لا ترغب في التغيير)</label>
                        <input type="password" name="password" minlength="8" placeholder="كلمة مرور جديدة (اختياري)..."
                               class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500 transition">
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="editModalOpen = false"
                                class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold hover:bg-slate-200 transition">
                            إلغاء
                        </button>
                        <button type="submit"
                                class="px-5 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-black text-xs transition shadow-sm">
                            حفظ التعديلات
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-ministry-layout>

<x-admin-layout title="المستخدمين والصلاحيات" headerTitle="إدارة المستخدمين والأدوار والكوادر الحكومية">
    <!-- Filter and Add Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        <!-- Add User Form -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs h-fit">
            <h3 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-red-600"></span>
                إضافة مستخدم أو كادر حكومي جديد
            </h3>
            <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">الاسم الكامل</label>
                    <input type="text" name="name" required placeholder="الاسم الرباعي..." 
                           class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">البريد الإلكتروني</label>
                    <input type="email" name="email" required placeholder="user@example.test" 
                           class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">رقم الهاتف</label>
                        <input type="text" name="phone" required placeholder="770000000" 
                               class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">الرقم الوطني</label>
                        <input type="text" name="national_id" required placeholder="1000000000" 
                               class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">الدور والصلاحية</label>
                    <select name="role_id" required class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                        <option value="">اختر الدور...</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">الجهة التابعة (للإداريين والميدانيين)</label>
                    <select name="department_id" class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                        <option value="">غير تابع لجهة (للمواطنين والمدير العام)</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->ministry?->name }} - {{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">كلمة المرور المؤقتة</label>
                    <input type="password" name="password" required placeholder="8 أحرف على الأقل..." 
                           class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                </div>

                <button type="submit" class="w-full py-2.5 rounded-xl bg-black text-white font-bold text-xs hover:bg-zinc-800 transition">
                    + تسجيل الحساب
                </button>
            </form>
        </div>

        <!-- Users Filter & Table -->
        <div class="lg:col-span-2 space-y-4">
            <!-- Filter Bar -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs">
                <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap items-center gap-3">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="بحث بالاسم، البريد، أو الهاتف..." 
                           class="flex-1 min-w-[180px] py-1.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">

                    <select name="role" class="py-1.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                        <option value="">كافة الأدوار</option>
                        @foreach($roles as $r)
                            <option value="{{ $r->name }}" {{ request('role') === $r->name ? 'selected' : '' }}>
                                {{ $r->name }}
                            </option>
                        @endforeach
                    </select>

                    <button type="submit" class="py-1.5 px-4 rounded-xl bg-red-600 text-white font-bold text-xs hover:bg-red-700 transition">
                        تصفية
                    </button>
                    @if(request()->hasAny(['search', 'role']))
                        <a href="{{ route('admin.users.index') }}" class="py-1.5 px-3 rounded-xl bg-slate-100 text-slate-600 text-xs font-bold">
                            إلغاء
                        </a>
                    @endif
                </form>
            </div>

            <!-- Users Table -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <!-- Desktop Table View -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-bold border-b border-slate-200/80">
                            <tr>
                                <th class="py-3.5 px-6">المستخدم</th>
                                <th class="py-3.5 px-6">الدور</th>
                                <th class="py-3.5 px-6">الجهة التابعة</th>
                                <th class="py-3.5 px-6">الحالة</th>
                                <th class="py-3.5 px-6 text-center">إجراء</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @forelse($users as $u)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-4 px-6 whitespace-nowrap">
                                        <p class="font-bold text-slate-900">{{ $u->name }}</p>
                                        <p class="text-[10px] text-slate-400 font-mono">{{ $u->email }} | {{ $u->phone }}</p>
                                    </td>
                                    <td class="py-4 px-6 whitespace-nowrap">
                                        @foreach($u->roles as $role)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold 
                                                {{ $role->name === 'Super Admin' ? 'bg-red-100 text-red-800' : ($role->name === 'Ministry Admin' ? 'bg-amber-100 text-amber-800' : ($role->name === 'Field Worker' ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-700')) }}">
                                                {{ match($role->name) {
                                                    'Super Admin' => 'مدير عام المنظومة',
                                                    'Ministry Admin' => 'مشرف جهة حكومية',
                                                    'Field Worker' => 'موظف ميداني',
                                                    'Citizen' => 'مواطن',
                                                    default => $role->name
                                                } }}
                                            </span>
                                        @endforeach
                                    </td>
                                    <td class="py-4 px-6 text-slate-600 whitespace-nowrap">
                                        {{ $u->department?->name ?? 'غير محدد' }}
                                    </td>
                                    <td class="py-4 px-6 whitespace-nowrap">
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $u->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-red-50 text-red-700 border border-red-200' }}">
                                            {{ $u->is_active ? 'نشط' : 'معطل' }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-center whitespace-nowrap">
                                        <form method="POST" action="{{ route('admin.users.toggle_active', $u->id) }}">
                                            @csrf
                                            <button type="submit" class="px-3 py-1 rounded-lg border {{ $u->is_active ? 'border-red-200 text-red-600 hover:bg-red-50' : 'border-emerald-200 text-emerald-600 hover:bg-emerald-50' }} text-[11px] font-bold transition">
                                                {{ $u->is_active ? 'تعطيل الحساب' : 'تنشيط الحساب' }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-8 text-slate-400">لا يوجد مستخدمين مطابقين.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card View -->
                <div class="block md:hidden divide-y divide-slate-100 p-3.5 space-y-3">
                    @forelse($users as $u)
                        <div class="p-4 rounded-2xl bg-slate-50/90 border border-slate-200/80 space-y-2.5">
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-bold text-xs text-slate-900">{{ $u->name }}</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $u->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $u->is_active ? 'نشط' : 'معطل' }}
                                </span>
                            </div>

                            <div class="space-y-1 text-xs text-slate-500">
                                <p class="font-mono text-[11px]">{{ $u->email }}</p>
                                <p class="font-mono text-[11px]">{{ $u->phone }}</p>
                                <div class="flex items-center justify-between pt-1">
                                    <span>الدور: 
                                        <span class="font-bold text-slate-800">
                                            {{ $u->roles->pluck('name')->first() ?? 'مستخدم' }}
                                        </span>
                                    </span>
                                    <span>{{ $u->department?->name ?? 'مستقل' }}</span>
                                </div>
                            </div>

                            <form method="POST" action="{{ route('admin.users.toggle_active', $u->id) }}">
                                @csrf
                                <button type="submit" class="w-full py-2 rounded-xl border {{ $u->is_active ? 'border-red-300 text-red-700 bg-red-50/50' : 'border-emerald-300 text-emerald-700 bg-emerald-50/50' }} text-xs font-bold transition text-center">
                                    {{ $u->is_active ? 'تعطيل الحساب' : 'تنشيط الحساب' }}
                                </button>
                            </form>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 text-center py-6">لا يوجد مستخدمين مطابقين.</p>
                    @endforelse
                </div>

                <div class="p-4 border-t border-slate-100">
                    {{ $users->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>

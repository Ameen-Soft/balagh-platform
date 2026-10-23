<x-admin-layout title="المشاريع التنموية والرقابة المجتمعية" headerTitle="إدارة المشاريع التنموية الوطنية والرقابة">
    <div x-data="{
        // Edit Project State
        editModalOpen: false,
        editProject: {
            id: null,
            title: '',
            department_id: '',
            target_amount: '',
            current_amount: '',
            status: 'in_progress',
            latitude: 15.3694,
            longitude: 44.1910,
            description: '',
            image_url: null,
            update_url: ''
        },
        openEditProject(project) {
            this.editProject = {
                id: project.id,
                title: project.title,
                department_id: project.department_id,
                target_amount: project.target_amount,
                current_amount: project.current_amount,
                status: project.status,
                latitude: project.latitude,
                longitude: project.longitude,
                description: project.description,
                image_url: project.image_url,
                update_url: project.update_url
            };
            this.editModalOpen = true;
        },
        closeEditProject() {
            this.editModalOpen = false;
        },

        // Delete Project State
        deleteModalOpen: false,
        deleteProjectData: {
            id: null,
            title: '',
            delete_url: ''
        },
        openDeleteProject(project) {
            this.deleteProjectData = {
                id: project.id,
                title: project.title,
                delete_url: project.delete_url
            };
            this.deleteModalOpen = true;
        },
        closeDeleteProject() {
            this.deleteModalOpen = false;
        },

        // Executive Phases State
        phasesModalOpen: false,
        activeProject: null,
        phases: [],
        phaseStoreUrl: '',
        editingPhase: null,
        phaseForm: {
            id: null,
            name: '',
            completion_percentage: 0,
            start_date: '',
            end_date: '',
            status: 'pending',
            description: '',
            update_url: ''
        },
        openPhasesModal(project) {
            this.activeProject = project;
            this.phases = project.phases || [];
            this.phaseStoreUrl = project.phase_store_url;
            this.resetPhaseForm();
            this.phasesModalOpen = true;
        },
        closePhasesModal() {
            this.phasesModalOpen = false;
            this.resetPhaseForm();
        },
        startEditPhase(phase) {
            this.editingPhase = phase;
            this.phaseForm = {
                id: phase.id,
                name: phase.name,
                completion_percentage: phase.completion_percentage,
                start_date: phase.start_date,
                end_date: phase.end_date,
                status: phase.status,
                description: phase.description || '',
                update_url: phase.update_url
            };
        },
        resetPhaseForm() {
            this.editingPhase = null;
            this.phaseForm = {
                id: null,
                name: '',
                completion_percentage: 0,
                start_date: '',
                end_date: '',
                status: 'pending',
                description: '',
                update_url: ''
            };
        }
    }">

        <!-- Validation Errors Banner -->
        @if ($errors->any())
            <div class="mb-6 p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 text-xs shadow-xs">
                <div class="font-bold mb-2 flex items-center gap-2 text-sm text-red-900">
                    <svg class="w-5 h-5 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>يرجى مراجعة الأخطاء التالية:</span>
                </div>
                <ul class="list-disc list-inside space-y-1 text-slate-700 mr-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            <!-- Add Project Form for Super Admin -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs h-fit">
                <h3 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-red-600"></span>
                    إضافة مشروع تنموي وطني جديد
                </h3>
                <form method="POST" action="{{ route('admin.projects.store') }}" enctype="multipart/form-data" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">اسم المشروع التنموي</label>
                        <input type="text" name="name" required placeholder="مثال: مشروع تأهيل شبكة المياه في صنعاء القديمة" 
                               value="{{ old('name') }}"
                               class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">الجهة المسؤولة والوزارة</label>
                        <select name="department_id" required class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->ministry?->name ?? 'الوزارة' }} - {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">الميزانية المستهدفة (ر.ي)</label>
                            <input type="number" name="budget" step="0.01" placeholder="ريال يمني" required
                                   value="{{ old('budget') }}"
                                   class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">الحالة الأولية</label>
                            <select name="status" required class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                                <option value="planning" {{ old('status') === 'planning' ? 'selected' : '' }}>تخطيط (Planning)</option>
                                <option value="in_progress" {{ old('status', 'in_progress') === 'in_progress' ? 'selected' : '' }}>قيد التنفيذ (In Progress)</option>
                                <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>نشط ومتاح (Active)</option>
                                <option value="completed" {{ old('status') === 'completed' ? 'selected' : '' }}>مكتمل (Completed)</option>
                                <option value="suspended" {{ old('status') === 'suspended' ? 'selected' : '' }}>معلق (Suspended)</option>
                                <option value="halted" {{ old('status') === 'halted' ? 'selected' : '' }}>متوقف (Halted)</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">خط العرض (Lat)</label>
                            <input type="number" step="0.0000001" name="latitude" required value="{{ old('latitude', '15.3694') }}" 
                                   class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">خط الطول (Lng)</label>
                            <input type="number" step="0.0000001" name="longitude" required value="{{ old('longitude', '44.1910') }}" 
                                   class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">صورة المشروع (اختياري)</label>
                        <input type="file" name="image" accept="image/*" 
                               class="w-full py-1.5 px-3 rounded-xl border border-slate-300 text-xs text-slate-600 focus:ring-2 focus:ring-red-500 file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
                        <p class="text-[10px] text-slate-400 mt-1">يمكنك رفع صورة تعريفية للمشروع (JPEG, PNG, WebP بحد أقصى 5 ميجابايت).</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">وصف المشروع ومستهدفاته</label>
                        <textarea name="description" rows="3" required placeholder="أهداف المشروع ومخرجاته المتوقعة..." 
                                  class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">{{ old('description') }}</textarea>
                    </div>

                    <button type="submit" class="w-full py-2.5 rounded-xl bg-red-600 text-white font-bold text-xs hover:bg-red-700 transition shadow-xs">
                        + إطلاق المشروع التنموي
                    </button>
                </form>
            </div>

            <!-- Projects Table -->
            <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between text-xs text-slate-500 font-bold">
                    <span>المشاريع الوطنية المسجلة: {{ $projects->total() }}</span>
                    <span>الرقابة التنموية والمراحل</span>
                </div>

                <!-- Desktop Table View -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-bold border-b border-slate-200/80">
                            <tr>
                                <th class="py-3.5 px-4">المشروع</th>
                                <th class="py-3.5 px-4">الجهة والوزارة</th>
                                <th class="py-3.5 px-4">الحالة</th>
                                <th class="py-3.5 px-4">الميزانية</th>
                                <th class="py-3.5 px-4 text-center">المراحل التنفيذية</th>
                                <th class="py-3.5 px-4 text-center">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @forelse($projects as $p)
                                @php
                                    $projectPhasesData = $p->phases->map(function($phase) use ($p) {
                                        return [
                                            'id' => $phase->id,
                                            'project_id' => $phase->project_id,
                                            'name' => $phase->name,
                                            'description' => $phase->description ?? '',
                                            'completion_percentage' => (int) $phase->completion_percentage,
                                            'start_date' => $phase->start_date ? $phase->start_date->format('Y-m-d') : '',
                                            'end_date' => $phase->end_date ? $phase->end_date->format('Y-m-d') : '',
                                            'status' => $phase->status,
                                            'update_url' => route('admin.projects.phases.update', [$p->id, $phase->id]),
                                            'delete_url' => route('admin.projects.phases.destroy', [$p->id, $phase->id]),
                                        ];
                                    });

                                    $projectPayload = [
                                        'id' => $p->id,
                                        'title' => $p->title,
                                        'department_id' => $p->department_id,
                                        'description' => $p->description,
                                        'target_amount' => (float) $p->target_amount,
                                        'current_amount' => (float) ($p->current_amount ?? 0),
                                        'status' => $p->status,
                                        'latitude' => (float) $p->latitude,
                                        'longitude' => (float) $p->longitude,
                                        'image_url' => $p->image_url,
                                        'update_url' => route('admin.projects.update', $p->id),
                                        'delete_url' => route('admin.projects.destroy', $p->id),
                                        'phase_store_url' => route('admin.projects.phases.store', $p->id),
                                        'phases' => $projectPhasesData,
                                    ];
                                @endphp
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-4 px-4">
                                        <div class="flex items-center gap-3">
                                            @if($p->image_url)
                                                <img src="{{ $p->image_url }}" alt="{{ $p->title }}" class="w-10 h-10 rounded-lg object-cover shrink-0 border border-slate-200">
                                            @else
                                                <div class="w-10 h-10 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center shrink-0 text-slate-400">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                </div>
                                            @endif
                                            <div>
                                                <p class="font-bold text-slate-900">{{ $p->title }}</p>
                                                <p class="text-[10px] text-slate-400">#PRJ-{{ $p->id }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4 text-slate-600 whitespace-nowrap">
                                        <div>
                                            <p class="font-bold text-slate-800">{{ $p->department?->name ?? 'غير محدد' }}</p>
                                            <p class="text-[10px] text-slate-400">{{ $p->department?->ministry?->name ?? 'وزارة غير محددة' }}</p>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4 whitespace-nowrap">
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $p->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : ($p->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800') }}">
                                            {{ match($p->status) {
                                                'planning' => 'تخطيط',
                                                'in_progress' => 'قيد التنفيذ',
                                                'completed' => 'مكتمل',
                                                'active' => 'نشط ومتاح',
                                                'suspended' => 'معلق',
                                                'halted' => 'متوقف',
                                                default => $p->status
                                            } }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 text-slate-600 whitespace-nowrap font-mono">
                                        {{ $p->target_amount ? number_format($p->target_amount) . ' ر.ي' : '-' }}
                                    </td>
                                    <td class="py-4 px-4 text-center whitespace-nowrap">
                                        <button type="button"
                                                @click='openPhasesModal(@json($projectPayload))'
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold transition border border-slate-200">
                                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                                            <span>المراحل</span>
                                            <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-red-600 text-white">{{ $p->phases->count() }}</span>
                                        </button>
                                    </td>
                                    <td class="py-4 px-4 text-center whitespace-nowrap">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <button type="button" 
                                                    @click='openEditProject(@json($projectPayload))'
                                                    title="تعديل تفاصيل المشروع"
                                                    class="p-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 transition border border-amber-200">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </button>
                                            <button type="button" 
                                                    @click='openDeleteProject(@json($projectPayload))'
                                                    title="حذف المشروع"
                                                    class="p-1.5 rounded-lg bg-red-50 hover:bg-red-100 text-red-700 transition border border-red-200">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-8 text-slate-400">لا توجد مشاريع مضافة.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card View -->
                <div class="block md:hidden divide-y divide-slate-100 p-3.5 space-y-3">
                    @forelse($projects as $p)
                        @php
                            $projectPhasesData = $p->phases->map(function($phase) use ($p) {
                                return [
                                    'id' => $phase->id,
                                    'project_id' => $phase->project_id,
                                    'name' => $phase->name,
                                    'description' => $phase->description ?? '',
                                    'completion_percentage' => (int) $phase->completion_percentage,
                                    'start_date' => $phase->start_date ? $phase->start_date->format('Y-m-d') : '',
                                    'end_date' => $phase->end_date ? $phase->end_date->format('Y-m-d') : '',
                                    'status' => $phase->status,
                                    'update_url' => route('admin.projects.phases.update', [$p->id, $phase->id]),
                                    'delete_url' => route('admin.projects.phases.destroy', [$p->id, $phase->id]),
                                ];
                            });

                            $projectPayload = [
                                'id' => $p->id,
                                'title' => $p->title,
                                'department_id' => $p->department_id,
                                'description' => $p->description,
                                'target_amount' => (float) $p->target_amount,
                                'current_amount' => (float) ($p->current_amount ?? 0),
                                'status' => $p->status,
                                'latitude' => (float) $p->latitude,
                                'longitude' => (float) $p->longitude,
                                'image_url' => $p->image_url,
                                'update_url' => route('admin.projects.update', $p->id),
                                'delete_url' => route('admin.projects.destroy', $p->id),
                                'phase_store_url' => route('admin.projects.phases.store', $p->id),
                                'phases' => $projectPhasesData,
                            ];
                        @endphp
                        <div class="p-4 rounded-2xl bg-slate-50/90 border border-slate-200/80 space-y-3">
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-mono text-xs font-bold text-slate-900 bg-white px-2 py-0.5 rounded border border-slate-200">
                                    #PRJ-{{ $p->id }}
                                </span>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $p->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ match($p->status) {
                                        'planning' => 'تخطيط',
                                        'in_progress' => 'قيد التنفيذ',
                                        'completed' => 'مكتمل',
                                        'active' => 'نشط ومتاح',
                                        'halted' => 'متوقف',
                                        default => $p->status
                                    } }}
                                </span>
                            </div>

                            <div class="flex items-center gap-3">
                                @if($p->image_url)
                                    <img src="{{ $p->image_url }}" alt="{{ $p->title }}" class="w-12 h-12 rounded-xl object-cover shrink-0 border border-slate-200">
                                @else
                                    <div class="w-12 h-12 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center shrink-0 text-slate-400">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </div>
                                @endif
                                <div>
                                    <h4 class="text-sm font-bold text-slate-900 leading-snug">{{ $p->title }}</h4>
                                    <p class="text-xs text-slate-500 mt-0.5">{{ $p->department?->name }} ({{ $p->department?->ministry?->name }})</p>
                                    <p class="text-xs font-mono font-bold text-slate-700 mt-1">{{ number_format($p->target_amount) }} ر.ي</p>
                                </div>
                            </div>

                            <div class="pt-2 border-t border-slate-200/60 flex items-center justify-between gap-2">
                                <button type="button" 
                                        @click='openPhasesModal(@json($projectPayload))'
                                        class="flex-1 py-1.5 px-3 rounded-xl bg-slate-800 text-white text-xs font-bold flex items-center justify-center gap-1.5 shadow-2xs">
                                    <span>المراحل التنفيذية</span>
                                    <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-red-600 text-white">{{ $p->phases->count() }}</span>
                                </button>
                                <button type="button" 
                                        @click='openEditProject(@json($projectPayload))'
                                        class="py-1.5 px-3 rounded-xl bg-amber-50 text-amber-700 text-xs font-bold border border-amber-200">
                                    تعديل
                                </button>
                                <button type="button" 
                                        @click='openDeleteProject(@json($projectPayload))'
                                        class="py-1.5 px-3 rounded-xl bg-red-50 text-red-700 text-xs font-bold border border-red-200">
                                    حذف
                                </button>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 text-center py-6">لا توجد مشاريع مضافة.</p>
                    @endforelse
                </div>

                <div class="p-4 border-t border-slate-100">
                    {{ $projects->links() }}
                </div>
            </div>
        </div>

        <!-- ================= EDIT PROJECT MODAL ================= -->
        <div x-show="editModalOpen" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto"
             aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="editModalOpen" 
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     @click="closeEditProject()" 
                     class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="editModalOpen" 
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block align-bottom bg-white rounded-2xl text-right overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-200">
                    
                    <div class="p-6">
                        <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                                <h3 class="text-base font-bold text-slate-900">تعديل بيانات المشروع التنموي</h3>
                            </div>
                            <button type="button" @click="closeEditProject()" class="text-slate-400 hover:text-slate-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <form :action="editProject.update_url" method="POST" enctype="multipart/form-data" class="space-y-4">
                            @csrf
                            <input type="hidden" name="_method" value="PUT">

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">اسم المشروع التنموي</label>
                                <input type="text" name="name" x-model="editProject.title" required 
                                       class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">الجهة المسؤولة والوزارة</label>
                                <select name="department_id" x-model="editProject.department_id" required 
                                        class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}">
                                            {{ $dept->ministry?->name ?? 'الوزارة' }} - {{ $dept->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">الميزانية المستهدفة (ر.ي)</label>
                                    <input type="number" name="budget" step="0.01" x-model="editProject.target_amount" required 
                                           class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">المبلغ المحقق حالياً (ر.ي)</label>
                                    <input type="number" name="current_amount" step="0.01" x-model="editProject.current_amount" 
                                           class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">حالة المشروع</label>
                                    <select name="status" x-model="editProject.status" required 
                                            class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                                        <option value="planning">تخطيط (Planning)</option>
                                        <option value="in_progress">قيد التنفيذ (In Progress)</option>
                                        <option value="active">نشط ومتاح (Active)</option>
                                        <option value="completed">مكتمل (Completed)</option>
                                        <option value="suspended">معلق (Suspended)</option>
                                        <option value="halted">متوقف (Halted)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">خط العرض (Lat)</label>
                                    <input type="number" step="0.0000001" name="latitude" x-model="editProject.latitude" required 
                                           class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">خط الطول (Lng)</label>
                                    <input type="number" step="0.0000001" name="longitude" x-model="editProject.longitude" required 
                                           class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">تحديث صورة المشروع (اختياري)</label>
                                <template x-if="editProject.image_url">
                                    <div class="mb-2 flex items-center gap-3 p-2 bg-slate-50 rounded-xl border border-slate-200">
                                        <img :src="editProject.image_url" class="w-12 h-12 rounded-lg object-cover border border-slate-300">
                                        <span class="text-[11px] text-slate-500">الصورة الحالية مستخدمة. يمكنك اختيار صورة جديدة لاستبدالها.</span>
                                    </div>
                                </template>
                                <input type="file" name="image" accept="image/*" 
                                       class="w-full py-1.5 px-3 rounded-xl border border-slate-300 text-xs text-slate-600 focus:ring-2 focus:ring-red-500 file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">وصف ومستهدفات المشروع</label>
                                <textarea name="description" rows="3" x-model="editProject.description" required 
                                          class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500"></textarea>
                            </div>

                            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
                                <button type="button" @click="closeEditProject()" 
                                        class="py-2 px-4 rounded-xl bg-slate-100 text-slate-700 font-bold text-xs hover:bg-slate-200 transition">
                                    إلغاء
                                </button>
                                <button type="submit" 
                                        class="py-2 px-5 rounded-xl bg-red-600 text-white font-bold text-xs hover:bg-red-700 transition shadow-xs">
                                    حفظ التعديلات
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= DELETE PROJECT MODAL ================= -->
        <div x-show="deleteModalOpen" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="deleteModalOpen" 
                     @click="closeDeleteProject()" 
                     class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div x-show="deleteModalOpen" 
                     class="inline-block align-bottom bg-white rounded-2xl text-right overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-200 p-6">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-red-100 text-red-600 flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">تأكيد حذف المشروع التنموي</h3>
                            <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                                هل أنت متأكد من رغبتك في حذف المشروع: 
                                <span class="font-bold text-red-600" x-text="deleteProjectData.title"></span>؟
                                سيتم حذف جميع المراحل التنفيذية والبيانات التابعة له بشكل نهائي.
                            </p>
                        </div>
                    </div>

                    <form :action="deleteProjectData.delete_url" method="POST" class="mt-6 flex items-center justify-end gap-2">
                        @csrf
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="button" @click="closeDeleteProject()" 
                                class="py-2 px-4 rounded-xl bg-slate-100 text-slate-700 font-bold text-xs hover:bg-slate-200 transition">
                            تراجع
                        </button>
                        <button type="submit" 
                                class="py-2 px-5 rounded-xl bg-red-600 text-white font-bold text-xs hover:bg-red-700 transition shadow-xs">
                            نعم، حذف المشروع نهائياً
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- ================= EXECUTIVE PHASES MANAGEMENT MODAL ================= -->
        <div x-show="phasesModalOpen" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="phasesModalOpen" 
                     @click="closePhasesModal()" 
                     class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div x-show="phasesModalOpen" 
                     class="inline-block align-bottom bg-white rounded-2xl text-right overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full border border-slate-200">
                    
                    <!-- Modal Header -->
                    <div class="p-6 pb-4 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-red-600"></span>
                                <h3 class="text-base font-bold text-slate-900">المراحل التنفيذية للمشروع</h3>
                            </div>
                            <p class="text-xs text-slate-500 mt-1" x-text="activeProject ? activeProject.title : ''"></p>
                        </div>
                        <button type="button" @click="closePhasesModal()" class="text-slate-400 hover:text-slate-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Modal Body: 2 Columns Grid -->
                    <div class="p-6 grid grid-cols-1 lg:grid-cols-12 gap-6 max-h-[75vh] overflow-y-auto">
                        <!-- Column 1: Phases List (7 cols) -->
                        <div class="lg:col-span-7 space-y-3">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="text-xs font-bold text-slate-800">قائمة المراحل المسجلة (<span x-text="phases.length"></span>)</h4>
                                <span class="text-[11px] text-slate-400">ترتيب زمني تسلسلي</span>
                            </div>

                            <template x-if="phases.length === 0">
                                <div class="p-8 rounded-2xl bg-slate-50 border border-dashed border-slate-200 text-center space-y-2">
                                    <div class="w-10 h-10 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center mx-auto">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                    </div>
                                    <p class="text-xs font-bold text-slate-700">لا توجد مراحل تنفيذية مضافة لهذا المشروع بعد.</p>
                                    <p class="text-[11px] text-slate-400">استخدم النموذج المقابل لإضافة المرحلة التنفيذية الأولى للمشروع.</p>
                                </div>
                            </template>

                            <template x-for="(phase, index) in phases" :key="phase.id">
                                <div class="p-4 rounded-xl bg-slate-50/90 border border-slate-200 hover:border-slate-300 transition space-y-2.5">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="flex items-center gap-2">
                                            <span class="w-5 h-5 rounded-full bg-slate-200 text-slate-700 text-[10px] font-bold flex items-center justify-center" x-text="index + 1"></span>
                                            <h5 class="text-xs font-bold text-slate-900" x-text="phase.name"></h5>
                                        </div>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                                              :class="{
                                                  'bg-emerald-100 text-emerald-800': phase.status === 'completed',
                                                  'bg-blue-100 text-blue-800': phase.status === 'in_progress',
                                                  'bg-amber-100 text-amber-800': phase.status === 'pending',
                                                  'bg-rose-100 text-rose-800': phase.status === 'delayed'
                                              }"
                                              x-text="phase.status === 'completed' ? 'مكتملة' : (phase.status === 'in_progress' ? 'قيد التنفيذ' : (phase.status === 'delayed' ? 'متأخرة' : 'قيد الانتظار'))">
                                        </span>
                                    </div>

                                    <template x-if="phase.description">
                                        <p class="text-[11px] text-slate-600 leading-relaxed" x-text="phase.description"></p>
                                    </template>

                                    <!-- Progress Bar -->
                                    <div class="space-y-1">
                                        <div class="flex justify-between text-[11px] font-bold">
                                            <span class="text-slate-500">نسبة إنجاز المرحلة</span>
                                            <span class="text-red-600 font-mono" x-text="phase.completion_percentage + '%'"></span>
                                        </div>
                                        <div class="w-full h-1.5 bg-slate-200 rounded-full overflow-hidden">
                                            <div class="h-full bg-red-600 rounded-full transition-all duration-300" 
                                                 :style="'width: ' + phase.completion_percentage + '%'"></div>
                                        </div>
                                    </div>

                                    <div class="pt-2 border-t border-slate-200/60 flex items-center justify-between text-[10px] text-slate-500 font-medium">
                                        <div class="flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            <span x-text="phase.start_date"></span>
                                            <span>←</span>
                                            <span x-text="phase.end_date"></span>
                                        </div>

                                        <div class="flex items-center gap-1">
                                            <button type="button" 
                                                    @click="startEditPhase(phase)"
                                                    class="px-2 py-1 rounded bg-amber-50 hover:bg-amber-100 text-amber-700 font-bold transition">
                                                تعديل
                                            </button>
                                            <form :action="phase.delete_url" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذه المرحلة التنفيذية؟');" class="inline">
                                                @csrf
                                                <input type="hidden" name="_method" value="DELETE">
                                                <button type="submit" class="px-2 py-1 rounded bg-red-50 hover:bg-red-100 text-red-700 font-bold transition">
                                                    حذف
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Column 2: Phase Add/Edit Form (5 cols) -->
                        <div class="lg:col-span-5 bg-slate-50 rounded-2xl p-5 border border-slate-200 h-fit space-y-4">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-bold text-slate-900" x-text="editingPhase ? 'تعديل المرحلة التنفيذية' : 'إضافة مرحلة تنفيذية جديدة'"></h4>
                                <template x-if="editingPhase">
                                    <button type="button" @click="resetPhaseForm()" class="text-[11px] text-red-600 font-bold hover:underline">
                                        إلغاء التعديل
                                    </button>
                                </template>
                            </div>

                            <form :action="editingPhase ? phaseForm.update_url : phaseStoreUrl" method="POST" class="space-y-3">
                                @csrf
                                <template x-if="editingPhase">
                                    <input type="hidden" name="_method" value="PUT">
                                </template>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">اسم المرحلة التنفيذية</label>
                                    <input type="text" name="name" x-model="phaseForm.name" required 
                                           placeholder="مثال: المرحلة الأولى: أعمال الحفر والتجهيز" 
                                           class="w-full py-1.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                                </div>

                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">نسبة الإنجاز (%)</label>
                                        <input type="number" name="completion_percentage" min="0" max="100" x-model="phaseForm.completion_percentage" required 
                                               class="w-full py-1.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500 font-mono">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">حالة المرحلة</label>
                                        <select name="status" x-model="phaseForm.status" required 
                                                class="w-full py-1.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                                            <option value="pending">قيد الانتظار</option>
                                            <option value="in_progress">قيد التنفيذ</option>
                                            <option value="completed">مكتملة</option>
                                            <option value="delayed">متأخرة</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">تاريخ البدء</label>
                                        <input type="date" name="start_date" x-model="phaseForm.start_date" required 
                                               class="w-full py-1.5 px-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">تاريخ الانتهاء</label>
                                        <input type="date" name="end_date" x-model="phaseForm.end_date" required 
                                               class="w-full py-1.5 px-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">وصف المرحلة ومخرجاتها</label>
                                    <textarea name="description" rows="2" x-model="phaseForm.description" 
                                              placeholder="تفاصيل الأعمال التنفيذية المستهدفة في هذه المرحلة..." 
                                              class="w-full py-1.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500"></textarea>
                                </div>

                                <button type="submit" 
                                        class="w-full py-2 rounded-xl bg-red-600 text-white font-bold text-xs hover:bg-red-700 transition shadow-xs"
                                        x-text="editingPhase ? 'حفظ تعديل المرحلة' : '+ حفظ المرحلة التنفيذية'">
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-admin-layout>

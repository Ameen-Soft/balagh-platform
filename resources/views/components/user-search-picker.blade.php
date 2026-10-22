@props([
    'name' => 'user_id',
    'scope' => 'admin_users',
    'placeholder' => 'ابحث بالاسم، البريد، أو رقم الهاتف...',
    'selectedId' => null,
    'selectedName' => '',
    'selectedSub' => '',
    'required' => false,
    'role' => null,
    'departmentId' => null,
    'label' => null,
    'hint' => null,
])

<div x-data="userSearchPicker({
        searchUrl: '{{ route('internal.search.users') }}',
        scope: '{{ $scope }}',
        role: '{{ $role ?? '' }}',
        departmentId: '{{ $departmentId ?? '' }}',
        selectedId: '{{ old($name, $selectedId) }}',
        selectedName: '{{ addslashes($selectedName) }}',
        selectedSub: '{{ addslashes($selectedSub) }}',
        required: {{ $required ? 'true' : 'false' }}
    })"
    class="relative w-full"
    @click.outside="open = false">

    @if($label)
        <label class="block text-xs font-bold text-slate-700 mb-1.5 flex items-center justify-between">
            <span>{{ $label }}</span>
            @if($required)
                <span class="text-red-500 font-bold">*</span>
            @endif
        </label>
    @endif

    <!-- Hidden input to submit with the standard HTML form -->
    <input type="hidden" 
           name="{{ $name }}" 
           :value="selected.id" 
           @if($required) required @endif>

    <!-- Selected State View -->
    <div x-show="selected.id" x-cloak class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-200/90 shadow-2xs">
        <div class="flex items-center gap-3 overflow-hidden">
            <div class="w-9 h-9 rounded-xl bg-slate-900 text-amber-400 font-black flex items-center justify-center text-xs shrink-0 shadow-xs">
                <span x-text="selected.name ? selected.name.charAt(0) : '؟'"></span>
            </div>
            <div class="truncate">
                <p class="text-xs font-bold text-slate-900 truncate" x-text="selected.name"></p>
                <p class="text-[11px] text-slate-500 font-mono truncate" x-text="selected.sub"></p>
            </div>
        </div>
        <button type="button" 
                @click="clearSelection()"
                title="إلغاء وتغيير الحساب المختار"
                class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- Live Search Input View -->
    <div x-show="!selected.id" class="relative">
        <div class="relative">
            <input type="text"
                   x-ref="searchInput"
                   x-model="query"
                   @input.debounce.300ms="performSearch()"
                   @keydown.arrow-down.prevent="navigateDown()"
                   @keydown.arrow-up.prevent="navigateUp()"
                   @keydown.enter.prevent="selectHighlighted()"
                   @keydown.escape="open = false"
                   @focus="if(query.trim().length >= 1 && results.length) open = true"
                   placeholder="{{ $placeholder }}"
                   class="w-full py-2.5 pr-10 pl-10 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500 focus:border-red-500 bg-white placeholder-slate-400 transition"
                   autocomplete="off">

            <!-- Search Icon right (RTL start) -->
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>

            <!-- Loading Spinner or Clear Button left (RTL end) -->
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center">
                <div x-show="loading" class="animate-spin text-red-600">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                </div>
                <button type="button" 
                        x-show="!loading && query.length > 0" 
                        @click="query = ''; results = []; open = false; $refs.searchInput.focus()"
                        class="text-slate-400 hover:text-slate-600">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Floating Dropdown Results -->
        <div x-show="open" 
             x-cloak
             x-transition:enter="transition ease-out duration-100"
             x-transition:enter-start="opacity-0 translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-75"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-1"
             class="absolute z-50 w-full mt-1.5 bg-white rounded-xl shadow-xl border border-slate-200/90 overflow-hidden max-h-72 overflow-y-auto">

            <!-- Results List -->
            <ul class="divide-y divide-slate-100 text-xs">
                <template x-for="(user, index) in results" :key="user.id">
                    <li @click="selectUser(user)"
                        @mouseenter="highlightedIndex = index"
                        :class="{ 'bg-slate-50 text-red-700': highlightedIndex === index }"
                        class="p-3 cursor-pointer hover:bg-slate-50 transition flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 overflow-hidden">
                            <div class="w-8 h-8 rounded-lg bg-slate-900 text-amber-400 font-bold flex items-center justify-center text-[11px] shrink-0">
                                <span x-text="user.name.charAt(0)"></span>
                            </div>
                            <div class="truncate">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-slate-900" x-text="user.name"></span>
                                    <template x-if="user.roles && user.roles.length">
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600" x-text="user.roles[0]"></span>
                                    </template>
                                </div>
                                <div class="text-[11px] text-slate-500 font-mono flex items-center gap-2 mt-0.5">
                                    <span x-text="user.phone"></span>
                                    <span>•</span>
                                    <span class="truncate" x-text="user.email"></span>
                                </div>
                            </div>
                        </div>

                        <div class="shrink-0 text-left">
                            <template x-if="user.department_name">
                                <span class="text-[10px] font-semibold text-slate-400 block" x-text="user.department_name"></span>
                            </template>
                            <span class="text-[10px] text-red-600 font-bold inline-block opacity-0 group-hover:opacity-100">اختيار</span>
                        </div>
                    </li>
                </template>
            </ul>

            <!-- Empty Search State -->
            <div x-show="!loading && hasSearched && results.length === 0" class="p-4 text-center text-slate-400 text-xs">
                <svg class="w-6 h-6 mx-auto mb-1 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p>لم يتم العثور على أي حساب مطابق للبحث.</p>
            </div>

            <!-- Error State -->
            <div x-show="errorMessage" class="p-3 bg-red-50 text-red-700 text-xs text-center border-t border-red-100" x-text="errorMessage"></div>
        </div>
    </div>

    @if($hint)
        <p class="text-[10px] text-slate-400 mt-1">{{ $hint }}</p>
    @endif
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('userSearchPicker', (config) => ({
        searchUrl: config.searchUrl,
        scope: config.scope,
        role: config.role,
        departmentId: config.departmentId,
        query: '',
        results: [],
        loading: false,
        open: false,
        hasSearched: false,
        highlightedIndex: -1,
        errorMessage: '',
        abortController: null,
        selected: {
            id: config.selectedId || '',
            name: config.selectedName || '',
            sub: config.selectedSub || ''
        },

        async performSearch() {
            const term = this.query.trim();
            if (term.length < 1) {
                this.results = [];
                this.open = false;
                this.hasSearched = false;
                this.errorMessage = '';
                return;
            }

            if (this.abortController) {
                this.abortController.abort();
            }
            this.abortController = new AbortController();

            this.loading = true;
            this.errorMessage = '';

            try {
                const params = new URLSearchParams({
                    query: term,
                    scope: this.scope,
                    limit: 15
                });
                if (this.role) params.append('role', this.role);
                if (this.departmentId) params.append('department_id', this.departmentId);

                const response = await fetch(`${this.searchUrl}?${params.toString()}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    signal: this.abortController.signal
                });

                const data = await response.json();
                if (response.ok && data.success) {
                    this.results = data.data || [];
                    this.hasSearched = true;
                    this.open = true;
                    this.highlightedIndex = -1;
                } else {
                    this.errorMessage = data.message || 'تعذر جلب نتائج البحث.';
                    this.results = [];
                    this.open = true;
                }
            } catch (err) {
                if (err.name !== 'AbortError') {
                    this.errorMessage = 'حدث خطأ في الاتصال بالخادم. تحقق من اتصالك بالإنترنت.';
                    this.results = [];
                    this.open = true;
                }
            } finally {
                this.loading = false;
            }
        },

        selectUser(user) {
            this.selected = {
                id: user.id,
                name: user.name,
                sub: `${user.phone} • ${user.email}`
            };
            this.query = '';
            this.results = [];
            this.open = false;
            this.hasSearched = false;
            this.highlightedIndex = -1;
            this.$dispatch('user-selected', { id: user.id, user: user });
        },

        clearSelection() {
            this.selected = { id: '', name: '', sub: '' };
            this.query = '';
            this.results = [];
            this.open = false;
            this.hasSearched = false;
            this.$dispatch('user-cleared');
            this.$nextTick(() => {
                if (this.$refs.searchInput) {
                    this.$refs.searchInput.focus();
                }
            });
        },

        navigateDown() {
            if (!this.open || !this.results.length) return;
            this.highlightedIndex = (this.highlightedIndex + 1) % this.results.length;
        },

        navigateUp() {
            if (!this.open || !this.results.length) return;
            this.highlightedIndex = (this.highlightedIndex - 1 + this.results.length) % this.results.length;
        },

        selectHighlighted() {
            if (this.highlightedIndex >= 0 && this.highlightedIndex < this.results.length) {
                this.selectUser(this.results[this.highlightedIndex]);
            }
        }
    }));
});
</script>

<x-admin-layout title="إعدادات المنصة والهوية" headerTitle="إدارة إعدادات المنصة وهوية وسوم Head">
    <div x-data="{ activeTab: 'branding' }" class="space-y-6">

        <!-- Notification Alerts -->
        @if (session('success'))
            <div class="bg-emerald-50 border border-emerald-200/80 rounded-2xl p-4 flex items-center gap-3 text-emerald-800 text-xs font-bold shadow-xs">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200/80 rounded-2xl p-4 text-red-800 text-xs font-bold shadow-xs">
                <div class="flex items-center gap-2 mb-2 text-red-900">
                    <svg class="w-5 h-5 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>يرجى تصحيح الأخطاء التالية قبل الحفظ:</span>
                </div>
                <ul class="list-disc list-inside space-y-1 text-red-700 pr-4">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Form Container -->
        <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
            @csrf

            <!-- Navigation Tabs Bar -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-2 shadow-xs mb-6">
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" 
                            @click="activeTab = 'branding'"
                            :class="activeTab === 'branding' ? 'bg-[#CE1126] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'"
                            class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>الهوية والشعارات والأيقونات</span>
                    </button>

                    <button type="button" 
                            @click="activeTab = 'seo'"
                            :class="activeTab === 'seo' ? 'bg-[#CE1126] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'"
                            class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <span>وسوم السيو ومحركات البحث (SEO)</span>
                    </button>

                    <button type="button" 
                            @click="activeTab = 'social'"
                            :class="activeTab === 'social' ? 'bg-[#CE1126] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'"
                            class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                        </svg>
                        <span>بطاقات التواصل الاجتماعي (OG & Twitter)</span>
                    </button>

                    <button type="button" 
                            @click="activeTab = 'scripts'"
                            :class="activeTab === 'scripts' ? 'bg-[#CE1126] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'"
                            class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                        <span>أكواد التحقق والتتبع (Analytics & Scripts)</span>
                    </button>

                    <button type="button" 
                            @click="activeTab = 'contact'"
                            :class="activeTab === 'contact' ? 'bg-[#CE1126] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'"
                            class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                        <span>الدعم وأرقام التواصل الرسمي</span>
                    </button>
                </div>
            </div>

            <!-- Tab 1: Branding & Identity -->
            <div x-show="activeTab === 'branding'" class="space-y-6">
                <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs">
                    <h3 class="text-sm font-bold text-slate-900 mb-4 pb-3 border-b border-slate-100 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-red-600"></span>
                        بيانات الهوية والعلامة التجارية
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">اسم المنصة بالعربية <span class="text-red-500">*</span></label>
                            <input type="text" name="site_name" value="{{ old('site_name', $settings->get('site_name')) }}" required
                                   class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                            <p class="text-[11px] text-slate-500 mt-1">يظهر في وسم Title وشريط الهيدر وجميع صفحات المنصة.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">اسم المنصة بالإنجليزية (اختياري)</label>
                            <input type="text" name="site_name_en" value="{{ old('site_name_en', $settings->get('site_name_en')) }}"
                                   class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                            <p class="text-[11px] text-slate-500 mt-1">يستخدم في وسوم الميتا باللغات الأجنبية (مثل Bader Platform).</p>
                        </div>
                    </div>
                </div>

                <!-- Media & Icons Upload -->
                <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs">
                    <h3 class="text-sm font-bold text-slate-900 mb-4 pb-3 border-b border-slate-100 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-red-600"></span>
                        الأيقونات والشعارات البصرية
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Favicon -->
                        <div class="border border-slate-200 rounded-xl p-4 bg-slate-50/50 flex flex-col justify-between">
                            <div>
                                <span class="block text-xs font-bold text-slate-800 mb-1">أيقونة الموقع (Favicon)</span>
                                <p class="text-[10px] text-slate-500 mb-3">تظهر في تبويب المتصفح (.ico, .png, .svg)</p>
                                
                                <div class="w-16 h-16 bg-white border border-slate-200 rounded-xl flex items-center justify-center mb-3 mx-auto shadow-2xs">
                                    @if ($settings->get('site_favicon'))
                                        <img src="{{ asset('storage/' . $settings->get('site_favicon')) }}" alt="Favicon" class="max-w-12 max-h-12 object-contain">
                                    @else
                                        <span class="text-[10px] text-slate-400 font-bold">افتراضي</span>
                                    @endif
                                </div>
                            </div>
                            <div>
                                <input type="file" name="site_favicon" accept=".ico,.png,.svg" class="text-xs file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:font-bold file:bg-red-50 file:text-red-700 hover:file:bg-red-100 w-full text-slate-500">
                            </div>
                        </div>

                        <!-- Apple Touch Icon -->
                        <div class="border border-slate-200 rounded-xl p-4 bg-slate-50/50 flex flex-col justify-between">
                            <div>
                                <span class="block text-xs font-bold text-slate-800 mb-1">أيقونة أبل (Apple Touch)</span>
                                <p class="text-[10px] text-slate-500 mb-3">للشاشات الرئيسية في iPhone و iPad (180x180)</p>
                                
                                <div class="w-16 h-16 bg-white border border-slate-200 rounded-xl flex items-center justify-center mb-3 mx-auto shadow-2xs">
                                    @if ($settings->get('site_apple_icon'))
                                        <img src="{{ asset('storage/' . $settings->get('site_apple_icon')) }}" alt="Apple Icon" class="max-w-12 max-h-12 object-contain">
                                    @else
                                        <span class="text-[10px] text-slate-400 font-bold">افتراضي</span>
                                    @endif
                                </div>
                            </div>
                            <div>
                                <input type="file" name="site_apple_icon" accept=".png,.jpg,.jpeg" class="text-xs file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:font-bold file:bg-red-50 file:text-red-700 hover:file:bg-red-100 w-full text-slate-500">
                            </div>
                        </div>

                        <!-- Main Site Logo -->
                        <div class="border border-slate-200 rounded-xl p-4 bg-slate-50/50 flex flex-col justify-between">
                            <div>
                                <span class="block text-xs font-bold text-slate-800 mb-1">الشعار الرسمي (Logo)</span>
                                <p class="text-[10px] text-slate-500 mb-3">يظهر في الهيدر والصفحات</p>
                                
                                <div class="w-full h-16 bg-white border border-slate-200 rounded-xl flex items-center justify-center mb-3 px-2 shadow-2xs">
                                    @if ($settings->get('site_logo'))
                                        <img src="{{ asset('storage/' . $settings->get('site_logo')) }}" alt="Site Logo" class="max-h-12 object-contain">
                                    @else
                                        <span class="text-[10px] text-slate-400 font-bold">الشعار الافتراضي</span>
                                    @endif
                                </div>
                            </div>
                            <div>
                                <input type="file" name="site_logo" accept=".png,.jpg,.jpeg,.svg,.webp" class="text-xs file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:font-bold file:bg-red-50 file:text-red-700 hover:file:bg-red-100 w-full text-slate-500">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Android APK File Upload (With Real-Time Progress Bar & 100MB Support) -->
                <div x-data="apkUploadManager()" class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs space-y-5">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 flex-wrap gap-2">
                        <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span>
                            <span>تطبيق الهاتف (Android APK)</span>
                        </h3>
                    </div>

                    <!-- Dynamic Alerts for APK Upload -->
                    <div x-show="errorMessage" x-cloak class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-xs font-bold flex items-center justify-between gap-3 shadow-xs">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span x-text="errorMessage"></span>
                        </div>
                        <button type="button" @click="errorMessage = ''" class="text-red-500 hover:text-red-800">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div x-show="successMessage" x-cloak class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center justify-between gap-3 shadow-xs">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span x-text="successMessage"></span>
                        </div>
                        <button type="button" @click="successMessage = ''" class="text-emerald-500 hover:text-emerald-800">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Current APK Status Card -->
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 p-4 rounded-xl border border-slate-200 bg-slate-50/70">
                        <div class="flex items-center gap-3.5 w-full sm:w-auto">
                            <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0 shadow-2xs">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-slate-800">حالة التطبيق على المنصة</h4>
                                <div class="mt-1.5 flex items-center gap-2 flex-wrap">
                                    <template x-if="hasApk">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-100 text-emerald-800 text-[10px] font-bold">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                                التطبيق مرفوع ومتوفر للتحميل للجمهور
                                            </span>
                                            <a :href="apkUrl" download class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 hover:text-emerald-900 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 px-2.5 py-1 rounded-lg transition">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                                تحميل النسخة الحالية
                                            </a>
                                            <button type="button" @click="deleteApk()" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-red-100 text-red-700 hover:bg-red-200 text-[10px] font-bold transition cursor-pointer">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                <span>حذف التطبيق</span>
                                            </button>
                                        </div>
                                    </template>
                                    <template x-if="!hasApk">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-amber-100 text-amber-800 text-[10px] font-bold">
                                            لم يتم رفع ملف APK حتى الآن (رابط التنزيل معطل في الصفحة الرئيسية وصفحة تسجيل الدخول)
                                        </span>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Upload Dropzone & File Selector -->
                    <div class="space-y-4">
                        <input type="file" 
                               x-ref="apkFileInput" 
                               accept=".apk" 
                               @change="handleFileSelect($event)" 
                               class="hidden">

                        <!-- Dropzone Area -->
                        <div @dragover.prevent="isDragging = true"
                             @dragleave.prevent="isDragging = false"
                             @drop.prevent="handleDrop($event)"
                             @click="if(!isUploading) $refs.apkFileInput.click()"
                             :class="{
                                 'border-emerald-500 bg-emerald-50/50 scale-[1.005]': isDragging,
                                 'border-slate-300 hover:border-emerald-500 hover:bg-emerald-50/20': !isDragging && !isUploading,
                                 'opacity-60 cursor-not-allowed': isUploading
                             }"
                             class="border-2 border-dashed rounded-2xl p-6 text-center cursor-pointer transition-all duration-200">
                            
                            <div class="flex flex-col items-center justify-center gap-2">
                                <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>
                                </div>
                                <span class="text-xs font-bold text-slate-800">
                                    اسحب وأفلت ملف تطبيق الأندرويد (.apk) هنا أو <span class="text-emerald-600 underline">تصفح من جهازك</span>
                                </span>
                                <span class="text-[11px] text-slate-500">
                                    الحد الأقصى لحجم الملف: <strong class="text-slate-700">100 ميجابايت (100MB)</strong> — الصيغة المدعومة: <strong class="text-slate-700">.apk</strong>
                                </span>
                            </div>
                        </div>

                        <!-- Selected File Preview & Start Upload Button -->
                        <div x-show="selectedFile && !isUploading" x-cloak class="p-4 rounded-xl border border-emerald-200 bg-emerald-50/40 flex flex-col sm:flex-row items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-slate-900" x-text="fileName"></span>
                                        <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold" x-text="formattedSize"></span>
                                    </div>
                                    <span class="text-[11px] text-slate-500">جاهز للرفع والتثبيت على السيرفر</span>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                                <button type="button" 
                                        @click="clearSelectedFile()" 
                                        class="px-3 py-2 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-100 text-xs font-bold transition">
                                    إلغاء
                                </button>
                                <button type="button" 
                                        @click="startUpload()" 
                                        class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition flex items-center gap-2 shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                    </svg>
                                    <span>بدء رفع التطبيق الآن</span>
                                </button>
                            </div>
                        </div>

                        <!-- Real-Time Upload Progress Bar -->
                        <div x-show="isUploading" x-cloak class="p-5 rounded-xl border border-emerald-300 bg-white shadow-sm space-y-3">
                            <div class="flex items-center justify-between gap-4 text-xs">
                                <div class="flex items-center gap-2 font-bold text-slate-800">
                                    <svg class="w-4 h-4 text-emerald-600 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    <span x-text="statusText"></span>
                                </div>
                                <div class="flex items-center gap-3 shrink-0">
                                    <span class="font-extrabold text-sm text-emerald-600" x-text="uploadProgress + '%'"></span>
                                    <button type="button" 
                                            @click="cancelUpload()" 
                                            class="text-[11px] font-bold text-red-600 hover:text-red-800 underline">
                                        إلغاء الرفع
                                    </button>
                                </div>
                            </div>

                            <!-- Progress Track & Bar -->
                            <div class="w-full h-3 bg-slate-100 rounded-full overflow-hidden border border-slate-200">
                                <div class="h-full bg-gradient-to-r from-emerald-500 to-teal-500 rounded-full transition-all duration-150 ease-out" 
                                     :style="'width: ' + uploadProgress + '%'"></div>
                            </div>

                            <div class="flex items-center justify-between text-[11px] text-slate-500 font-mono">
                                <span>تم نقل: <strong class="text-slate-800 font-bold" x-text="uploadedMb"></strong> MB من أصل <strong class="text-slate-800 font-bold" x-text="totalMb"></strong> MB</span>
                                <span class="text-slate-400">يرجى عدم إغلاق الصفحة أثناء الرفع</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 2: SEO & Meta Tags -->
            <div x-show="activeTab === 'seo'" class="space-y-6">
                <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs">
                    <h3 class="text-sm font-bold text-slate-900 mb-4 pb-3 border-b border-slate-100 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-red-600"></span>
                        بيانات السيو والأرشفة في محركات البحث
                    </h3>

                    <div class="space-y-5">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">عنوان الميتا الافتراضي (Meta Title)</label>
                            <input type="text" name="meta_title" value="{{ old('meta_title', $settings->get('meta_title')) }}"
                                   class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                            <p class="text-[11px] text-slate-500 mt-1">العنوان الكامل الذي تظهره محركات البحث (Google) لصفحة المنصة الرئيسية.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">الوصف التعريفي (Meta Description)</label>
                            <textarea name="meta_description" rows="3"
                                      class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">{{ old('meta_description', $settings->get('meta_description')) }}</textarea>
                            <p class="text-[11px] text-slate-500 mt-1">النبذة الموجزة (150-160 حرفاً) التي تظهر أسفل الرابط في نتائج البحث.</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">الكلمات المفتاحية (Meta Keywords)</label>
                                <input type="text" name="meta_keywords" value="{{ old('meta_keywords', $settings->get('meta_keywords')) }}"
                                       class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                                <p class="text-[11px] text-slate-500 mt-1">كلمات مفتاحية تفصلها فواصل (مثال: بادر, بلاغات, اليمن).</p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">المؤلف والمالك (Meta Author)</label>
                                <input type="text" name="meta_author" value="{{ old('meta_author', $settings->get('meta_author')) }}"
                                       class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                                <p class="text-[11px] text-slate-500 mt-1">الجهة الحكومية أو الرسمية المالكة للنظام.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">توجيهات روبوتات الفهرسة (Meta Robots)</label>
                                <select name="meta_robots" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                                    <option value="index, follow" {{ old('meta_robots', $settings->get('meta_robots')) === 'index, follow' ? 'selected' : '' }}>
                                        السماح بالفهرسة وتتبع الروابط (index, follow) - موصى به
                                    </option>
                                    <option value="noindex, follow" {{ old('meta_robots', $settings->get('meta_robots')) === 'noindex, follow' ? 'selected' : '' }}>
                                        عدم الفهرسة مع تتبع الروابط (noindex, follow)
                                    </option>
                                    <option value="noindex, nofollow" {{ old('meta_robots', $settings->get('meta_robots')) === 'noindex, nofollow' ? 'selected' : '' }}>
                                        حظر محركات البحث تماماً (noindex, nofollow) - لوضع الصيانة
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">الرابط الدائم الكانونيكال (Canonical URL)</label>
                                <input type="url" name="canonical_url" value="{{ old('canonical_url', $settings->get('canonical_url')) }}"
                                       placeholder="{{ url('/') }}"
                                       class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 text-xs font-mono focus:ring-2 focus:ring-red-500">
                                <p class="text-[11px] text-slate-500 mt-1">اتركه فارغاً للاعتماد التلقائي على الرابط الحالي.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 3: Social & Cards -->
            <div x-show="activeTab === 'social'" class="space-y-6">
                <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs">
                    <h3 class="text-sm font-bold text-slate-900 mb-4 pb-3 border-b border-slate-100 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-red-600"></span>
                        وسوم المشاركة في شبكات التواصل الاجتماعي (Open Graph & X/Twitter)
                    </h3>

                    <div class="space-y-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">عنوان المشاركة الاجتماعية (OG Title)</label>
                                <input type="text" name="og_title" value="{{ old('og_title', $settings->get('og_title')) }}"
                                       class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                                <p class="text-[11px] text-slate-500 mt-1">يظهر في بطاقة المعاينة عند إرسال الرابط عبر واتساب وفيسبوك.</p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">نوع بطاقة إكس/تويتر (Twitter Card)</label>
                                <select name="twitter_card" class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                                    <option value="summary_large_image" {{ old('twitter_card', $settings->get('twitter_card')) === 'summary_large_image' ? 'selected' : '' }}>
                                        صورة عريضة كبيرة (summary_large_image)
                                    </option>
                                    <option value="summary" {{ old('twitter_card', $settings->get('twitter_card')) === 'summary' ? 'selected' : '' }}>
                                        بطاقة مصغرة مربعة (summary)
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">وصف المشاركة الاجتماعية (OG Description)</label>
                            <textarea name="og_description" rows="2"
                                      class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">{{ old('og_description', $settings->get('og_description')) }}</textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">حساب المنصة على تويتر (Twitter Site Handle)</label>
                            <input type="text" name="twitter_site" value="{{ old('twitter_site', $settings->get('twitter_site')) }}"
                                   placeholder="@BaderPlatform"
                                   class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 text-xs font-mono focus:ring-2 focus:ring-red-500">
                        </div>

                        <!-- Social Share Image -->
                        <div class="border border-slate-200 rounded-xl p-4 bg-slate-50/50">
                            <label class="block text-xs font-bold text-slate-800 mb-1">صورة المعاينة الاجتماعية (Social Share Banner)</label>
                            <p class="text-[11px] text-slate-500 mb-3">الصورة التي تظهر في فيسبوك، واتساب، ولينكدإن. المقاس الموصى به: 1200 × 630 بكسل.</p>
                            
                            <div class="flex flex-col sm:flex-row items-center gap-4">
                                <div class="w-48 h-24 bg-white border border-slate-200 rounded-xl flex items-center justify-center overflow-hidden shadow-2xs shrink-0">
                                    @if ($settings->get('og_image'))
                                        <img src="{{ asset('storage/' . $settings->get('og_image')) }}" alt="Social Share Banner" class="w-full h-full object-cover">
                                    @else
                                        <span class="text-[10px] text-slate-400 font-bold">لا توجد صورة مخصصة</span>
                                    @endif
                                </div>
                                <div class="flex-1 w-full">
                                    <input type="file" name="og_image" accept=".png,.jpg,.jpeg,.webp" class="text-xs file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:font-bold file:bg-red-50 file:text-red-700 hover:file:bg-red-100 w-full text-slate-500">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 4: Scripts & Tracking -->
            <div x-show="activeTab === 'scripts'" class="space-y-6">
                <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs">
                    <h3 class="text-sm font-bold text-slate-900 mb-4 pb-3 border-b border-slate-100 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-red-600"></span>
                        أدوات التحقق والتحليلات والأكواد البرمجية
                    </h3>

                    <div class="space-y-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">رمز التحقق لـ Google Search Console</label>
                                <input type="text" name="google_site_verification" value="{{ old('google_site_verification', $settings->get('google_site_verification')) }}"
                                       placeholder="مثال: abcdef1234567890..."
                                       class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 text-xs font-mono focus:ring-2 focus:ring-red-500">
                                <p class="text-[11px] text-slate-500 mt-1">يُحقن تلقائياً في وسم &lt;meta name="google-site-verification"&gt;.</p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">معرّف تحليلات جوجل (Google Analytics 4 ID)</label>
                                <input type="text" name="google_analytics_id" value="{{ old('google_analytics_id', $settings->get('google_analytics_id')) }}"
                                       placeholder="G-XXXXXXXXXX"
                                       class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 text-xs font-mono focus:ring-2 focus:ring-red-500">
                                <p class="text-[11px] text-slate-500 mt-1">يتم تضمين سكريبت gtag.js تلقائياً بمجرد إدخال هذا المعرف.</p>
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-bold text-slate-700">أكواد مخصصة داخل وسوم &lt;head&gt; (Custom Head Scripts)</label>
                            </div>
                            <textarea name="custom_head_scripts" rows="4" dir="ltr"
                                      placeholder="<!-- ضع أكواد CSS أو بكسل التتبع أو الخطوط هنا -->"
                                      class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 text-xs font-mono text-left focus:ring-2 focus:ring-red-500">{{ old('custom_head_scripts', $settings->get('custom_head_scripts')) }}</textarea>
                            <p class="text-[11px] text-slate-500 mt-1">يتم حقن هذه الأكواد في نهاية وسم &lt;head&gt; في جميع صفحات الموقع.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">أكواد مخصصة قبل إغلاق &lt;/body&gt; (Custom Footer Scripts)</label>
                            <textarea name="custom_footer_scripts" rows="3" dir="ltr"
                                      placeholder="<!-- ضع ودجات المحادثة المباشرة أو سكريبتات الفوتر هنا -->"
                                      class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 text-xs font-mono text-left focus:ring-2 focus:ring-red-500">{{ old('custom_footer_scripts', $settings->get('custom_footer_scripts')) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 5: Contact & Support -->
            <div x-show="activeTab === 'contact'" class="space-y-6">
                <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs">
                    <h3 class="text-sm font-bold text-slate-900 mb-4 pb-3 border-b border-slate-100 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-red-600"></span>
                        بيانات الدعم الفني وأرقام الطوارئ والتواصل
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-5">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">الرقم المجاني / الخط الساخن للبلاغات</label>
                            <input type="text" name="support_hotline" value="{{ old('support_hotline', $settings->get('support_hotline')) }}"
                                   placeholder="8000888"
                                   class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 text-xs font-mono focus:ring-2 focus:ring-red-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">البريد الإلكتروني الرسمي للدعم</label>
                            <input type="email" name="support_email" value="{{ old('support_email', $settings->get('support_email')) }}"
                                   placeholder="support@balagh.gov.ye"
                                   class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 text-xs font-mono focus:ring-2 focus:ring-red-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">العنوان أو المقر الرسمي</label>
                            <input type="text" name="support_address" value="{{ old('support_address', $settings->get('support_address')) }}"
                                   placeholder="صنعاء، الجمهورية اليمنية"
                                   class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-red-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">رابط صفحة فيسبوك الرسمية</label>
                            <input type="url" name="social_facebook" value="{{ old('social_facebook', $settings->get('social_facebook')) }}"
                                   placeholder="https://facebook.com/..."
                                   class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 text-xs font-mono focus:ring-2 focus:ring-red-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">رابط حساب إكس/تويتر الرسمي</label>
                            <input type="url" name="social_twitter" value="{{ old('social_twitter', $settings->get('social_twitter')) }}"
                                   placeholder="https://twitter.com/..."
                                   class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 text-xs font-mono focus:ring-2 focus:ring-red-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">رابط قناة تيليجرام للبلاغات</label>
                            <input type="url" name="social_telegram" value="{{ old('social_telegram', $settings->get('social_telegram')) }}"
                                   placeholder="https://t.me/..."
                                   class="w-full py-2.5 px-3.5 rounded-xl border border-slate-300 text-xs font-mono focus:ring-2 focus:ring-red-500">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sticky Save Bar at Bottom -->
                <div class="flex items-center gap-3 pt-6">
                    <button type="submit" 
                            class="px-6 py-2.5 rounded-xl bg-[#CE1126] text-white font-bold text-xs hover:bg-red-700 shadow-md shadow-red-600/20 hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>حفظ التعديلات وتحديث وسوم Head</span>
                    </button>
                </div>
        </form>

        <!-- Separate Delete Form for APK (triggered via button form attribute) -->
        <form id="delete-apk-form" method="POST" action="{{ route('admin.settings.delete_apk') }}" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    </div>

    @push('scripts')
    <script>
        (function() {
            function apkUploadManager() {
                return {
                    hasApk: {{ $settings->get('app_apk_file') ? 'true' : 'false' }},
                    apkUrl: '{{ $settings->get('app_apk_file') ? asset('storage/' . ltrim($settings->get('app_apk_file'), '/')) : '' }}',
                    selectedFile: null,
                    fileName: '',
                    formattedSize: '',
                    isDragging: false,
                    isUploading: false,
                    uploadProgress: 0,
                    uploadedMb: '0.0',
                    totalMb: '0.0',
                    statusText: '',
                    errorMessage: '',
                    successMessage: '',
                    xhr: null,

                    handleFileSelect(event) {
                        const file = event.target.files ? event.target.files[0] : null;
                        this.validateAndSetFile(file);
                    },

                    handleDrop(event) {
                        this.isDragging = false;
                        const file = event.dataTransfer.files ? event.dataTransfer.files[0] : null;
                        this.validateAndSetFile(file);
                    },

                    validateAndSetFile(file) {
                        this.errorMessage = '';
                        this.successMessage = '';
                        if (!file) {
                            this.clearSelectedFile();
                            return;
                        }

                        // Check file extension
                        const ext = file.name.split('.').pop().toLowerCase();
                        if (ext !== 'apk') {
                            this.errorMessage = 'الملف المحدد غير صالح. يجب اختيار ملف بصيغة أندرويد (.apk) فقط.';
                            this.clearSelectedFile();
                            return;
                        }

                        // Check file size (100MB = 100 * 1024 * 1024 bytes)
                        const maxBytes = 100 * 1024 * 1024;
                        if (file.size > maxBytes) {
                            const sizeMb = (file.size / (1024 * 1024)).toFixed(1);
                            this.errorMessage = `حجم الملف (${sizeMb} ميجابايت) يتجاوز الحد الأقصى المسموح به (100 ميجابايت).`;
                            this.clearSelectedFile();
                            return;
                        }

                        this.selectedFile = file;
                        this.fileName = file.name;
                        this.formattedSize = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
                        this.totalMb = (file.size / (1024 * 1024)).toFixed(1);
                        this.uploadedMb = '0.0';
                    },

                    clearSelectedFile() {
                        this.selectedFile = null;
                        this.fileName = '';
                        this.formattedSize = '';
                        this.uploadProgress = 0;
                        this.statusText = '';
                        if (this.$refs.apkFileInput) {
                            this.$refs.apkFileInput.value = '';
                        }
                    },

                    cancelUpload() {
                        if (this.xhr) {
                            this.xhr.abort();
                            this.xhr = null;
                        }
                        this.isUploading = false;
                        this.uploadProgress = 0;
                        this.statusText = '';
                        this.errorMessage = 'تم إلغاء عملية الرفع.';
                    },

                    startUpload() {
                        if (!this.selectedFile || this.isUploading) return;

                        this.isUploading = true;
                        this.uploadProgress = 0;
                        this.errorMessage = '';
                        this.successMessage = '';
                        this.statusText = 'بدء نقل الملف إلى السيرفر...';

                        const formData = new FormData();
                        formData.append('app_apk_file', this.selectedFile);

                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
                                          || '{{ csrf_token() }}';

                        this.xhr = new XMLHttpRequest();
                        this.xhr.open('POST', '{{ route('admin.settings.upload_apk') }}', true);
                        this.xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
                        this.xhr.setRequestHeader('Accept', 'application/json');

                        this.xhr.upload.onprogress = (e) => {
                            if (e.lengthComputable) {
                                const percent = Math.round((e.loaded / e.total) * 100);
                                this.uploadProgress = Math.min(percent, 99);
                                this.uploadedMb = (e.loaded / (1024 * 1024)).toFixed(1);
                                this.totalMb = (e.total / (1024 * 1024)).toFixed(1);

                                if (this.uploadProgress >= 99) {
                                    this.statusText = 'اكتمل نقل الملف، جاري التحقق وتثبيت الحزمة على الخادم...';
                                } else {
                                    this.statusText = `جاري الرفع... ${this.uploadedMb} MB من أصل ${this.totalMb} MB (${this.uploadProgress}%)`;
                                }
                            }
                        };

                        this.xhr.onload = () => {
                            this.isUploading = false;
                            const status = this.xhr.status;

                            try {
                                const response = JSON.parse(this.xhr.responseText);

                                if (status >= 200 && status < 300 && response.success) {
                                    this.uploadProgress = 100;
                                    this.statusText = 'اكتمل بنجاح!';
                                    this.successMessage = response.message || 'تم رفع وتثبيت تطبيق الأندرويد بنجاح.';
                                    this.hasApk = true;
                                    this.apkUrl = response.file_url;
                                    this.clearSelectedFile();
                                } else {
                                    this.uploadProgress = 0;
                                    if (response.errors && response.errors.app_apk_file) {
                                        this.errorMessage = response.errors.app_apk_file[0];
                                    } else {
                                        this.errorMessage = response.message || 'فشل رفع الملف. يرجى المحاولة لاحقاً.';
                                    }
                                }
                            } catch (e) {
                                this.uploadProgress = 0;
                                if (status === 413) {
                                    this.errorMessage = 'حجم الملف يتجاوز الحد المسموح به في إعدادات خادم الويب (Nginx client_max_body_size أو PHP post_max_size). يرجى التأكد من ضبط السيرفر للسماح برفع 100MB.';
                                } else if (status === 419) {
                                    this.errorMessage = 'انتهت صلاحية الجلسة (CSRF Token)، يرجى تحديث الصفحة والمحاولة مجدداً.';
                                } else {
                                    this.errorMessage = `حدث خطأ أثناء معالجة الملف في الخادم (رمز الاستجابة: ${status}).`;
                                }
                            }
                            this.xhr = null;
                        };

                        this.xhr.onerror = () => {
                            this.isUploading = false;
                            this.uploadProgress = 0;
                            this.errorMessage = 'فشل الاتصال بالخادم. يرجى التحقق من اتصال الإنترنت وإعدادات السيرفر.';
                            this.xhr = null;
                        };

                        this.xhr.ontimeout = () => {
                            this.isUploading = false;
                            this.uploadProgress = 0;
                            this.errorMessage = 'انتهت مهلة الطلب (Timeout). استغرقت عملية الرفع وقتاً أطول من المتوقع.';
                            this.xhr = null;
                        };

                        this.xhr.timeout = 15 * 60 * 1000; // 15 minutes timeout
                        this.xhr.send(formData);
                    },

                    deleteApk() {
                        if (!confirm('هل أنت متأكد من رغبتك في حذف ملف تطبيق الأندرويد نهائياً من الموقع؟')) {
                            return;
                        }
                        this.errorMessage = '';
                        this.successMessage = '';

                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
                                          || '{{ csrf_token() }}';

                        fetch('{{ route('admin.settings.delete_apk') }}', {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                            }
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                this.hasApk = false;
                                this.apkUrl = '';
                                this.successMessage = data.message || 'تم حذف ملف التطبيق بنجاح.';
                            } else {
                                this.errorMessage = data.message || 'فشل حذف الملف.';
                            }
                        })
                        .catch(() => {
                            document.getElementById('delete-apk-form').submit();
                        });
                    }
                };
            }

            window.apkUploadManager = apkUploadManager;

            if (window.Alpine) {
                window.Alpine.data('apkUploadManager', apkUploadManager);
            } else {
                document.addEventListener('alpine:init', () => {
                    window.Alpine.data('apkUploadManager', apkUploadManager);
                });
            }
        })();
    </script>
    @endpush
</x-admin-layout>

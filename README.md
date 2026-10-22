# منصة بادر (Bader Platform)
> البوابة الوطنية لإدارة البلاغات والشكاوى وتتبعها جغرافياً ودعم المشاريع التنموية.

---

## الفهرس
* [نبذة عن المشروع](#نبذة-عن-المشروع)
* [الأهداف الرئيسية](#الأهداف-الرئيسية)
* [أدوار النظام ومصفوفة الصلاحيات (RBAC)](#أدوار-النظام-ومصفوفة-الصلاحيات-rbac)
* [الوحدات الوظيفية للنظام](#الوحدات-الوظيفية-للنظام)
* [بوابات الويب ولوحات التحكم](#بوابات-الويب-ولوحات-التحكم)
* [المعمارية التقنية وحزمة البرمجيات](#المعمارية-التقنية-وحزمة-البرمجيات)
* [المعايير التقنية والموثوقية](#المعايير-التقنية-والموثوقية)
* [هيكلية المستودع](#هيكلية-المستودع)
* [التثبيت والتشغيل المحلي](#التثبيت-والتشغيل-المحلي)
* [بيئة العرض المباشرة](#بيئة-العرض-المباشرة)
* [حسابات الاختبار الافتراضية](#حسابات-الاختبار-الافتراضية)
* [مرجع واجهات البرمجة (REST APIs v1)](#مرجع-واجهات-البرمجة-rest-apis-v1)
* [سجل مراحل التطوير (Roadmap)](#سجل-مراحل-التطوير-roadmap)

---

## نبذة عن المشروع
**منصة بادر** هي منظومة برمجية متكاملة للربط الرقمي بين المواطنين والجهات الحكومية (الوزارات والإدارات الخدمية). يتكون النظام من تطبيق للهواتف الذكية (Flutter) مخصص للمواطنين والفرق الميدانية، وبوابات ويب ولوحات تحكم مركزية (Laravel & Livewire) للمشرفين والقيادة الإدارية والرقابة العامة.

تتيح المنصة للمواطنين تقديم الشكاوى بتصنيف متسلسل وتوثيق بصري ومكاني إلزامي (GPS)، مع دعم التحويل البيني بين الجهات الحكومية دون إلغاء الطلب، والتفاعل المجتمعي مع البلاغات العامة بتأييدها (Community Upvoting)، واستعراض المشاريع التنموية مع محاكاة التمويل الجماعي لتعزيز الشفافية وتسريع الاستجابة الميدانية.

---

## الأهداف الرئيسية
1. **أتمتة قنوات البلاغات:** توحيد استقبال الشكاوى لكافة القطاعات والوزارات ضمن منصة مركزية واحدة.
2. **التحقق المكاني والبصري:** إلزامية التوثيق الميداني المباشر لحظة تقديم البلاغ للحد من البلاغات غير الدقيقة أو المكررة.
3. **تسريع المعالجة والتحويل:** توجيه البلاغات تلقائياً لجهة الاختصاص مع إتاحة التحويل البيني بين الوزارات دون فقدان المعاملة.
4. **التفاعل والتأييد المجتمعي:** تمكين المواطنين من تصفح البلاغات العامة المؤكدة وتأييدها لإبراز القضايا الأكثر إلحاحاً دون المساس بمعايير الخطورة الرسمية.
5. **شفافية المشاريع التنموية:** إشراك المجتمع والقطاع الخاص في متابعة مسار تنفيذ المشاريع التنموية ومحاكاة دعمها مالياً.

---

## أدوار النظام ومصفوفة الصلاحيات (RBAC)

* **المواطن (Citizen):**
  * تقديم البلاغات مع التوثيق بالصور المباشرة وإحداثيات الموقع (GPS)، مع خيار صريح لتحديد البلاغ كعام أو خاص.
  * متابعة مسار معالجة البلاغ والخط الزمني التدقيقي له من التقديم حتى الإغلاق.
  * تصفح البلاغات العامة وتأييدها بالإعجاب.
  * استعراض المشاريع التنموية وتسجيل المساهمات المالية الافتراضية.

* **الموظف الميداني (Field Worker):**
  * استعراض المهام الميدانية المسندة إليه وإدارتها (تبدأ المهمة بحالة قيد التنفيذ مباشرة دون الحاجة لخطوة قبول منفصلة).
  * التحقق اللحظي من الموقع الجغرافي ومطابقته مع إحداثيات البلاغ ضمن النطاق المسموح به (Geofencing).
  * رفع صورة إثبات الإنجاز المباشرة (ما بعد الإصلاح) وكتابة تقرير المعالجة لإتمام المهمة وحل البلاغ.

* **مشرف الوزارة (Ministry Admin):**
  * إدارة الشكاوى الخاصة بنطاق وزارته واتخاذ القرارات الإجرائية المباشرة (إسناد المهمة للموظف الميداني أو رفض البلاغ مع سبب إلزامي).
  * إدارة الكادر الميداني للوزارة: استعراض الموظفين وترقية حسابات المواطنين إلى موظفين ميدانيين أو إلغاء تعيينهم عند خلو المهام النشطة.
  * إسناد البلاغات للفرق الميدانية أو تحويل البلاغات الواردة بالخطأ لوزارات أخرى مع تدوين السبب.
  * متابعة التنبيهات الإدارية للبلاغات المتصدرة مجتمعياً (Trending Alerts).

* **المدير العام للنظام (Super Admin):**
  * الرقابة الإدارية الشاملة على كافة الوزارات والإدارات والمستخدمين.
  * إدارة شجرة التصنيفات الخدمية والقطاعية للنظام.
  * مراجعة المشاريع التنموية واعتمادها ومتابعة مؤشرات الأداء العامة (KPIs).

---

## الوحدات الوظيفية للنظام

### 1. وحدة إدارة الشكاوى (Complaint Management)
* **التصنيف الهرمي:** اختيار الوزارة، ثم القسم المختص، والتصنيف الرئيسي والفرعي، مع حقل للوصف المخصص.
* **التوثيق المكاني والبصري:** إلزامية التقاط الصورة عبر كاميرا التطبيق مباشرة مع قفل الموقع الجغرافي وحظر الرفع من المعرض لضمان صحة الإحداثيات.
* **الخصوصية كإعداد افتراضي (Privacy by Default):** تُحفظ البلاغات كخاصة افتراضياً، مع مفتاح اختياري صريح لمشاركة البلاغ في مجتمع بادر وعرضه على الخريطة العامة.

### 2. وحدة التوجيه والتحويل البيني (Routing & Forwarding)
* **التوجيه التلقائي:** إحالة الشكوى فور إنشائها إلى القسم والوزارة المختصة استناداً للتصنيف المحدد.
* **التحويل البيني:** إمكانية إعادة توجيه الشكوى بين الإدارات والوزارات مع توثيق سبب التحويل وتحديث مسار الطلب وإشعار المواطن آلياً.

### 3. وحدة المشاريع ومحاكاة التمويل (Projects & Simulated Crowdfunding)
* استعراض المشاريع التنموية والمتعثرة والمخطط لها مع الميزانية التقديرية ونسب الإنجاز والجهة المنفذة.
* تسجيل مساهمات مجتمعية افتراضية مع تحديث شريط التقدم المالي باستخدام حسابات دقيقة (BCMath).

### 4. وحدة الخرائط الجغرافية (GIS & Interactive Mapping)
* خريطة تفاعلية مبنية بمحرك Flutter Map ومدعومة بخوادم CARTO السحابية (CARTO CDN) بنمطي Positron وصور الأقمار الصناعية.
* قائمة تحكم دائرية منبثقة للتبديل بين طبقات الخريطة متوافقة مع اتجاه RTL.
* دبابيس جغرافية مصنفة لونياً (أحمر: جديد/عاجل، برتقالي: قيد التنفيذ، أخضر: منجز، أسود: مشاريع).
* بطاقات معاينة سفلية سريعة مع إمكانية التفاعل بالإعجاب مباشرة من الخريطة، وفلاتر علوية حسب النوع والحالة، وأدوات GPS وتوسيط الإطار (Fit Bounds).

### 5. وحدة الإشعارات (In-App Notifications)
* مركز إشعارات مخصص مع فرز (الكل / غير المقروء)، وتأشير القراءة الفردي والجماعي.
* توجيه عميق (Deep Linking) ينقل المستخدم مباشرة إلى تفاصيل الشكوى أو المشروع أو المهمة الميدانية.
* أيقونة جرس تفاعلية في الشريط العلوي تعرض عدد الإشعارات غير المقروءة لحظياً مع دعم السحب للتحديث.

### 6. وحدة التحقق من البريد واستعادة كلمة المرور (Auth Recovery & Verification)
* روابط تحقق مؤقتة وموقّعة مشفرة (Signed URLs) مع دعم الروابط العميقة (`bader://verify-email`).
* استجابات موحدة في استعادة كلمة المرور لمنع استكشاف الحسابات (Anti-Enumeration).
* تقييد معدل الطلبات (Rate Limiting) على مسارات المصادقة مع مؤقت تهدئة تفاعلي (60 ثانية).
* دعم تسجيل الدخول بحساب Google دون فرض خطوات تحقق إضافية للبريد الموثق.

### 7. وحدة التفاعل المجتمعي والبلاغات العامة (Community Interaction & Public Feed)
* خلاصة عامة للبلاغات المصرح بنشرها (`is_public = true`) مع حجب وتشفير بيانات المواطن الشخصية (PII Redaction).
* تسجيل وإلغاء الإعجاب بتحديث تفاؤلي فوري في الواجهة (Optimistic UI) مع تراجع آلي (Rollback) عند فشل الاتصال.
* قفل ذري للسجلات (`lockForUpdate`) وقيد مركب فريد `UNIQUE(complaint_id, user_id)` لمنع تكرار الإعجاب وضمان عدم نزول العداد للقيم السالبة.
* فصل الأهمية المجتمعية عن الأولوية التشغيلية الرسمية، مع تنبيه المشرف آلياً وتدوين حدث بالخط الزمني عند وصول الإعجابات إلى 15 إعجاباً.
* واجهة خفيفة لاسترجاع دبابيس الخريطة مع دعم فلترة النطاق المرئي (Bounding Box - `bbox`).
* أمر Artisan مجدول لتسوية العدادات ومزامنتها دورياً (`complaints:reconcile-likes`).

---

## بوابات الويب ولوحات التحكم

تعتمد لوحات التحكم على قوالب **Laravel Blade** مدمجة مع **Livewire 3** و **Tailwind CSS** بنمط داكن (Obsidian Theme) ودعم كامل لاتجاه RTL:

| البوابة / الواجهة | المسار (Route) | الوظيفة الأساسية | مستوى الوصول |
| :--- | :--- | :--- | :--- |
| **البوابة الوطنية العامة** | `/` (`home`) | إحصائيات عامة، استعراض أحدث البلاغات المنجزة، والمشاريع التنموية | عام (Public) |
| **سجل الشفافية العامة** | `/public/complaints` | دليل الشكاوى العامة مع حجب البيانات الشخصية للمواطنين | عام (Public) |
| **ملف الشفافية للبلاغ** | `/public/complaints/{id}` | استعراض الخط الزمني ومراحل معالجة البلاغ العام | عام (Public) |
| **دليل المشاريع التنموية** | `/public/projects` | استعراض المشاريع ومتابعة مؤشرات الإنجاز المالي والزمني | عام (Public) |
| **لوحة الإدارة العامة** | `/admin/dashboard` | رقابة مركزية، مؤشرات الأداء، إدارة الوزارات والمستخدمين والمشاريع | محمي بـ Middleware (`super_admin`) |
| **بوابة مشرف الوزارة** | `/ministry/dashboard` | فرز الشكاوى، إسناد المهام الميدانية، والتحويل بين الجهات | محمي بـ Middleware (`ministry_admin`) |

---

## المعمارية التقنية وحزمة البرمجيات

* **الواجهة الخلفية (Backend):**
  * إطار العمل: Laravel 13 مع PHP 8.3+.
  * إدارة المصادقة والجلسات: Laravel Jetstream و Laravel Sanctum.
  * طبقة واجهات البرمجة: RESTful APIs تحت البادئة `/api/v1`.
  * المعاملات والسياسات: إدارة العمليات المتزامنة عبر `DB::transaction` وسياسات التفويض (Policies).
* **قواعد البيانات (Database):**
  * نظام إدارة قواعد البيانات: MySQL 8+.
  * الهيكلية: 18 جدولاً مترابطاً مع قيود المفاتيح الأجنبية والفهارس المركبة وحذف البيانات التتابعي (Cascade).
* **بوابات الويب (Web UI):**
  * المكونات: Laravel Blade، Livewire 3، Alpine.js، و Tailwind CSS.
  * التصميم: واجهات متجاوبة (شريط جانبي للشاشات الكبيرة ودرج منزلق للجوالات) مع دعم RTL.
* **تطبيق الهاتف المحمول (Mobile App):**
  * إطار العمل: Flutter (Dart SDK `^3.13.1`).
  * البنية المعمارية: المعمارية النظيفة (Clean Architecture) مقسمة حسب الخصائص.
  * إدارة الحالة: Riverpod 3.x بنمط `Notifier<T>` / `AsyncNotifier<T>`.
  * الاتصال بالشبكة: عميل Dio مع Interceptors لحقن توكن Sanctum ومعالجة الأخطاء.
  * التخزين الآمن: مكتبة `flutter_secure_storage` لتخزين التوكن في Keystore / Keychain.
  * التوجيه: GoRouter مع معالجة التوجيه حسب حالة المصادقة واستعادة الجلسة بدون وميض.
  * الخرائط والموقع: مكتبات `flutter_map ^8.3.2`، `latlong2`، و `geolocator` مع خوادم CARTO CDN.

---

## المعايير التقنية والموثوقية
* **سلامة البيانات والتتبع:** قفل إحداثيات الموقع عند التصوير، والتحقق الجغرافي للموظف الميداني (Geofencing) قبل إغلاق المهام، وتدوين كل حركة في جدول الخط الزمني (`complaint_timeline`).
* **إدارة التزامن:** استخدام معاملات قاعدة البيانات مع قفل السجلات (`lockForUpdate`) والفهارس الفريدة لمنع التكرار وسباق البيانات.
* **حماية الخصوصية:** الخصوصية كإعداد افتراضي للبلاغات، وحجب بيانات الاتصال والهوية الشخصية في الموارد العامة.
* **الأمان وتحديد المعدل:** حماية مسارات المصادقة عبر قيود المعدل (Throttle)، وروابط تحقق موقّعة مشفرة، واستجابات موحدة تمنع استكشاف الحسابات.

---

## هيكلية المستودع

```text
├── backend/          # تطبيق Laravel (الواجهات البرمجية وبوابات الويب ولوحات التحكم)
├── mobile/           # تطبيق Flutter (تطبيق الهاتف للمواطنين وفرق العمل الميدانية)
└── docs/             # وثائق التحليل والتصميم المعماري ومخططات قواعد البيانات (ERD)
```

> [!TIP]
> للاطلاع على وثيقة التحليل الفني ومخططات قاعدة البيانات (ERD):  
> راجع ملف: **[وثيقة تحليل وتصميم النظام](docs/system_analysis_and_design.md)**.

---

## التثبيت والتشغيل المحلي

### 1. إعداد الواجهة الخلفية (Backend)
```bash
cd backend
composer install
cp .env.example .env     # ضبط إعدادات الاتصال بقاعدة البيانات في ملف .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

### 2. إعداد تطبيق الهاتف (Mobile)
```bash
cd mobile
flutter pub get
flutter run
```

---

## بيئة العرض المباشرة

تم ربط تطبيق الهاتف المحمول (Flutter) افتراضياً بالخادم المرفوع على الاستضافة:
```text
https://bader.ameen-dev.com/api/v1
```
لتجربة التطبيق محلياً عبر خادم التطوير المحلي، يمكن تعديل المتغير `baseUrl` داخل الملف:
`mobile/lib/core/network/api_endpoints.dart` ليعود إلى عنوان IP الجهاز المحلي، ثم تشغيل خادم Laravel عبر:
```bash
php artisan serve --host=0.0.0.0
```

---

## حسابات الاختبار الافتراضية

يوفر مهيئ البيانات (Seeders) حسابات اختبارية قياسية لجميع أدوار النظام:

| الدور (Role) | البريد الإلكتروني (Email) | كلمة المرور (Password) | نطاق المسؤولية |
| :--- | :--- | :--- | :--- |
| **المدير العام (Super Admin)** | `admin@example.test` | `password` | إدارة النظام الشاملة، الوزارات، الإعدادات، والمشاريع |
| **مشرف الوزارة (Ministry Admin)** | `ministry@example.test` | `password` | مراجعة الشكاوى، التحويل البيني، وإسناد المهام |
| **الموظف الميداني (Field Worker)** | `worker@example.test` | `password` | استقبال المهام، التحقق الجغرافي، وإثبات الإنجاز |
| **المواطن (Citizen)** | `citizen@example.test` | `password` | تقديم الشكاوى، المتابعة، والتفاعل المجتمعي |

> [!NOTE]
> كلمة المرور `password` مخصصة لبيئات التطوير والاختبار المحلي فقط.

---

## مرجع واجهات البرمجة (REST APIs v1)

تتوفر واجهات البرمجة تحت المسار الموحد `/api/v1` ومحمية بواسطة توكنات Sanctum:

| الميثود | المسار | الوصف والوظيفة | الصلاحية / المصادقة |
| :--- | :--- | :--- | :--- |
| `POST` | `/api/v1/auth/register` | إنشاء حساب مواطن جديد وإصدار توكن المصادقة | عام (Public) |
| `POST` | `/api/v1/auth/login` | تسجيل الدخول للأنظمة واستلام توكن المصادقة | عام (Public) |
| `POST` | `/api/v1/auth/google` | تسجيل الدخول باستخدام حساب Google عبر (ID Token) | عام (Public) |
| `GET` | `/api/v1/auth/me` | استعراض بيانات الحساب والأدوار والصلاحيات | مصادقة (`auth:sanctum`) |
| `PUT` | `/api/v1/auth/me` | تعديل الملف الشخصي (الاسم، الهاتف، الرقم الوطني) دون تغيير الأدوار | مصادقة (`auth:sanctum`) |
| `POST` | `/api/v1/auth/logout` | إبطال التوكن الحالي وتسجيل الخروج | مصادقة (`auth:sanctum`) |
| `POST` | `/api/v1/auth/forgot-password` | إرسال رابط استعادة كلمة المرور مع منع استكشاف الحسابات | عام (محدد المعدل) |
| `POST` | `/api/v1/auth/reset-password` | إعادة تعيين كلمة المرور عبر الرمز والبيانات الجديدة | عام (محدد المعدل) |
| `GET` | `/api/v1/auth/email/verify/{id}/{hash}` | التحقق من صحة البريد عبر رابط موقع ومشفر مع توجيه ذكي | عام (Signed URL) |
| `POST` | `/api/v1/auth/email/resend` | إعادة إرسال رسالة التحقق للبريد المحدد لغير المسجلين | عام (محدد المعدل) |
| `POST` | `/api/v1/auth/email/verification-notification` | إعادة إرسال رابط التحقق للمستخدم المسجل الحالي | مصادقة (`auth:sanctum`) |
| `GET` | `/api/v1/auth/email/status` | التحقق من حالة توثيق البريد الإلكتروني للمستخدم الحالي | مصادقة (`auth:sanctum`) |
| `GET` | `/api/v1/ministries` | استعراض قائمة الوزارات الحكومية النشطة وأقسامها | عام (Public) |
| `GET` | `/api/v1/ministries/{id}` | استعراض تفاصيل وزارة معينة وأقسامها | عام (Public) |
| `GET` | `/api/v1/categories` | استعراض التصنيفات المتسلسلة مع دعم الفلترة | عام (Public) |
| `GET` | `/api/v1/categories/{id}` | استعراض تفاصيل تصنيف معين وتصنيفاته الفرعية | عام (Public) |
| `GET` | `/api/v1/notifications` | استعراض إشعارات المستخدم مع الترقيم والفلترة | مصادقة (`auth:sanctum`) |
| `PATCH` | `/api/v1/notifications/{id}/read` | تأشير إشعار معين كمقروء للمستخدم | مصادقة (`auth:sanctum`) |
| `POST` | `/api/v1/notifications/read-all` | تأشير كافة إشعارات المستخدم كمقروءة دفعة واحدة | مصادقة (`auth:sanctum`) |
| `GET` | `/api/v1/complaints` | استعراض قائمة الشكاوى مع الترقيم والفلترة بحسب الصلاحية | مصادقة (`auth:sanctum`) |
| `POST` | `/api/v1/complaints` | تقديم بلاغ جديد مع التحقق الإجرائي والتوجيه التلقائي | مصادقة (`auth:sanctum`) |
| `GET` | `/api/v1/complaints/{id}` | استعراض تفاصيل البلاغ والمرفقات والخط الزمني | مصادقة (`auth:sanctum`) |
| `PATCH` | `/api/v1/complaints/{id}/status` | تحديث حالة الشكوى مع تدوين الحدث بالخط الزمني | مصادقة (`auth:sanctum`) |
| `POST` | `/api/v1/complaints/{id}/transfer` | تحويل البلاغ بين الإدارات أو الوزارات مع حفظ السبب | موظف الوزارة / المشرف |
| `POST` | `/api/v1/complaints/{id}/assign` | إسناد الشكوى إلى باحث ميداني وتحديث حالة المهمة | موظف الوزارة / المشرف |
| `GET` | `/api/v1/complaints/public` | استعراض خلاصة البلاغات العامة المعتمدة مع الترقيم وحجب الهوية | عام (Public) |
| `GET` | `/api/v1/complaints/public/{id}` | استعراض تفاصيل البلاغ العام مع حالة التأييد الشخصي وحجب الهوية | عام (Public) |
| `GET` | `/api/v1/complaints/map` | استرجاع دبابيس البلاغات العامة للخريطة ضمن النطاق المكاني (`bbox`) | عام (Public) |
| `POST` | `/api/v1/complaints/{id}/like` | تأييد البلاغ العام بالإعجاب مع معالجة التزامن بقفل السجلات | مصادقة (`auth:sanctum`) |
| `DELETE` | `/api/v1/complaints/{id}/like` | إلغاء التأييد من البلاغ العام مع ضمان عدم النزول تحت الصفر | مصادقة (`auth:sanctum`) |
| `GET` | `/api/v1/field-assignments` | استعراض قائمة المهام الميدانية المسندة للموظف | موظف ميداني / مشرف |
| `PATCH` | `/api/v1/field-assignments/{id}/accept` | قبول المهمة الميدانية والانتقال لحالة `accepted` | الباحث الميداني المكلف |
| `POST` | `/api/v1/field-assignments/{id}/start` | تسجيل بدء تنفيذ المعاينة الميدانية وتحديث حالة البلاغ | الباحث الميداني المكلف |
| `POST` | `/api/v1/field-assignments/{id}/verify-location` | التحقق الجغرافي اللحظي لموقع الباحث الميداني مقارنة بالبلاغ | الباحث الميداني المكلف |
| `POST` | `/api/v1/field-assignments/{id}/complete` | إتمام المهمة، رفع أدلة الإنجاز، والتحقق الجغرافي الصارم | الباحث الميداني المكلف |
| `GET` | `/api/v1/projects` | استعراض المشاريع التنموية ونسب الإنجاز والمبالغ المحصلة | عام (Public) |
| `GET` | `/api/v1/projects/{id}` | استعراض تفاصيل المشروع ومراحله والمساهمات المسجلة | عام (Public) |
| `POST` | `/api/v1/projects/{id}/contribute` | تسجيل مساهمة مجتمعية افتراضية بمشروع بحسابات دقيقة | مصادقة (`auth:sanctum`) |

---

## سجل مراحل التطوير (Roadmap)

- [x] تهيئة مستودع المشروع وهيكلته (Monorepo Setup).
- [x] بناء البنية التحتية للخادم (Laravel 13 + Jetstream + Sanctum + Livewire 3).
- [x] تهيئة مشروع تطبيق الهاتف المحمول (Flutter).
- [x] تصميم قاعدة البيانات والـ ERD في [وثيقة التحليل الفني](docs/system_analysis_and_design.md).
- [x] إنشاء وتطبيق ملفات تهجير قاعدة البيانات (Migrations) لجميع الجداول.
- [x] إنشاء نماذج البيانات (18 Eloquent Model) والعلاقات وبذور البيانات (Seeders).
- [x] بناء طبقة الخدمات ومحرك البلاغات والمعاملات الذرية (`DB::transaction`).
- [x] إنشاء واجهات برمجة التطبيقات الكاملة (REST APIs v1) تحت المسار `/api/v1`.
- [x] تطبيق سياسات الأمان والتفويض ونماذج التحقق (Policies & FormRequests).
- [x] بناء طبقة الاستعلامات عالية الأداء (`ComplaintQueryService` و `StatisticsQueryService`).
- [x] تطبيق حواجز المصادقة والتفويض الصارمة عبر Middleware وحظر الوصول غير المصرح به (403 Forbidden).
- [x] بناء بوابة الشفافية المجتمعية والرقابة العامة (`/` و `/public/complaints` و `/public/projects`).
- [x] لوحة الإدارة المركزية والرقابة الوطنية للمدير العام (`/admin/*`).
- [x] محطة العمل التشغيلية للوزارات والجهات الحكومية (`/ministry/*`).
- [x] بناء واجهات الويب بنمط داكن متجاوب مع الهواتف وشريط جانبي ودعم كامل لـ RTL.
- [x] جناح الاختبارات الآلية للواجهة الخلفية (100 اختبار Backend بنجاح 100% و 467 توكيداً).
- [x] شاشات الترحيب والتعريف بالنظام وتخزين الحالة الأولى (Flutter Onboarding Flow).
- [x] نظام المصادقة لتطبيق الهاتف (تسجيل الدخول / التسجيل / تسجيل الخروج، وحراس GoRouter مع Riverpod).
- [x] إثراء واجهات برمجة التطبيقات (Phase 01): الوزارات النشطة، التصنيفات المتسلسلة، قبول المهام، والتحقق الجغرافي.
- [x] طبقة البيانات والنماذج الأساسية لتطبيق الهاتف (Phase 02): الكيانات وعقود المستودعات لخصائص الشكاوى والمشاريع والإشعارات.
- [x] تتبع الشكاوى والخط الزمني والمرفقات (Phase 04): استعراض البلاغات، عارض المرفقات البصرية مع التكبير، والخط الزمني التدقيقي.
- [x] إدارة المهام الميدانية والتحقق الجغرافي الصارم (Phase 05): استعراض المهام، قبولها، التحقق من نطاق 500 متر عبر GPS، ورفع أدلة الإنجاز المباشرة.
- [x] استعراض المشاريع التنموية والمساهمة المجتمعية (Phase 06): دليل المشاريع المصنف، تفاصيل مراحل التنفيذ، ونافذة المساهمة الافتراضية.
- [x] التكامل مع تسجيل الدخول بحساب Google عبر Laravel Socialite وحزمة `google_sign_in`.
- [x] الخريطة التفاعلية الشاملة (Phase 07): محرك Flutter Map مع CARTO CDN، تبديل الطبقات، دبابيس البلاغات والمشاريع، وفلاتر الحالة والنوع.
- [x] مركز الإشعارات والتنبيهات (Phase 08): فرز المقروء وغير المقروء، التأشير الفردي والجماعي، التوجيه العميق، وأيقونة العداد اللحظي.
- [x] إدارة الملف الشخصي (Phase 09): استعراض وتعديل الاسم ورقم الهاتف والرقم الوطني عبر `PUT /api/v1/auth/me` مع ثبات الصلاحيات، وتحسين حالات القوائم الفارغة والأخطاء.
- [x] حزمة التحقق من البريد واستعادة كلمة المرور (Phase 10): واجهات التحقق واستعادة كلمة المرور، الروابط الموقعة المشفرة، حظر استكشاف الحسابات، وتقييد معدل الطلبات.
- [x] البنية التحتية الخلفية للتفاعل المجتمعي (Phase 11): جدول `complaint_likes`، حجب البيانات الشخصية، مسارات الإعجاب والتغذية العامة والخريطة، وأمر التسوية `complaints:reconcile-likes`.
- [x] تطبيق الهاتف للتفاعل المجتمعي وبلاغات مجتمع بادر (Phase 12): صفحة البلاغات العامة، الترقيم اللحظي، الفلاتر والفرز، التحديث التفاؤلي الفوري للإعجاب مع التراجع الآلي، مفتاح الخصوصية في إنشاء البلاغ، ودبابيس الخريطة العامة (105 اختبارات Flutter بنجاح 100%).

---
---

# Bader Platform
> A National Portal for Public Complaints Management, Geospatial Tracking, and Developmental Projects Support.

---

## Table of Contents
* [Project Overview](#project-overview)
* [Core Objectives](#core-objectives)
* [Target Roles & Access Control (RBAC)](#target-roles--access-control-rbac)
* [Functional Modules](#functional-modules)
* [Web Dashboards & Portals](#web-dashboards--portals)
* [Technical Architecture & Stack](#technical-architecture--stack)
* [Technical Standards & Reliability](#technical-standards--reliability)
* [Repository Structure](#repository-structure)
* [Installation & Local Setup](#installation--local-setup)
* [Live Demo Environment](#live-demo-environment)
* [Default Test Accounts](#default-test-accounts)
* [REST APIs v1 Reference](#rest-apis-v1-reference)
* [Project Roadmap](#project-roadmap)

---

## Project Overview
**Bader Platform** is a centralized software ecosystem connecting citizens with governmental ministries and public service departments. The platform comprises a cross-platform mobile application (Flutter) for citizens and field response teams, alongside web portals and administrative dashboards (Laravel & Livewire) for ministerial supervisors, system administrators, and public transparency.

Citizens can submit complaints through a cascading classification taxonomy with mandatory real-time camera capture and verified GPS coordinates. The platform supports inter-departmental ticket transfers without cancellation, community upvoting on verified public complaints, and an interactive showcase of developmental projects featuring simulated crowdfunding to maximize transparency and field response efficiency.

---

## Core Objectives
1. **Unified Complaint Intake:** Consolidate public grievances from disparate government sectors into a single digital channel.
2. **Visual & Geospatial Verification:** Mandate on-site photo capture and live GPS logging upon submission to prevent fraudulent or duplicate reports.
3. **Accelerated Routing & Processing:** Automatically direct tickets to the competent department with seamless inter-agency forwarding without ticket loss.
4. **Community Engagement & Upvoting:** Allow citizens to explore verified public grievances and express civic support via upvotes to highlight pressing local priorities without distorting official crisis priority classifications.
5. **Developmental Project Transparency:** Involve the community and private sector in tracking infrastructure projects with simulated funding pledges.

---

## Target Roles & Access Control (RBAC)

* **Citizen:**
  * File complaints with mandatory camera photos and verified GPS metadata, with an explicit toggle for public or private visibility.
  * Track ticket progression through an audit timeline from submission to resolution.
  * Browse verified public complaints and express civic support through upvotes.
  * Explore developmental projects and register simulated financial pledges.

* **Field Worker:**
  * View and manage assigned field tasks (assignments transition directly to `in_progress` without requiring a separate acceptance step).
  * Verify on-site physical presence and match incident coordinates within the allowed perimeter (Geofencing).
  * Capture mandatory post-repair evidence photos directly via camera and submit resolution reports to complete assignments and resolve the complaint.

* **Ministry Admin:**
  * Manage complaints within their ministry's scope and execute direct operational decisions (assigning tasks directly to field workers or rejecting complaints with a mandatory reason).
  * Manage ministry field workforce: browse workers, promote registered citizen accounts to field workers, or demote them when free of active assignments.
  * Dispatch complaints to field teams or re-route misdirected tickets to other ministries with logged transfer rationale.
  * Monitor administrative alerts triggered by trending community complaints (Trending Alerts).

* **Super Admin:**
  * Maintain comprehensive oversight across all ministries, departments, and user accounts.
  * Manage platform service taxonomies, categories, and system-wide configurations.
  * Review and approve developmental project listings and monitor nationwide performance indicators (KPIs).

---

## Functional Modules

### 1. Complaint Management
* **Cascading Taxonomy:** Hierarchical selection starting from ministry, department, primary category, and subcategory, with an optional custom description.
* **Geospatial & Visual Verification:** Direct-from-camera photo capture requirement with disabled gallery uploads to guarantee immediate, untampered GPS geocoding.
* **Privacy by Default:** Complaints default to private, with an explicit opt-in switch allowing citizens to share their reports publicly in the community feed.

### 2. Routing & Forwarding
* **Automated Dispatching:** Immediate routing of validated complaints to the appropriate ministry database based on category selection.
* **Inter-Agency Forwarding:** Reassignment between departments and ministries without request cancellation, retaining full ticket audit history and sending automated notifications to the citizen.

### 3. Developmental Projects & Simulated Crowdfunding
* Display of active, planned, and delayed infrastructure projects with budget requirements, progress percentages, and beneficiary entities.
* Interactive simulation of citizen and organizational pledges with real-time progress calculations using high-precision arithmetic (BCMath).

### 4. Interactive GIS Mapping
* Comprehensive interactive map built with Flutter Map and powered by CARTO CDN basemaps with dual Positron and Satellite styles.
* Animated circular pop-out layer control menu with full RTL alignment.
* Color-coded custom teardrop markers (Red: New/Urgent, Amber: In-Progress, Green: Resolved, Black: Projects).
* Interactive bottom preview cards with in-map upvoting, type and status filters, live GPS location tracking, and viewport auto-fitting (Fit Bounds).

### 5. In-App Notifications
* Notification center with reactive state filtering (All vs. Unread), single and batch "Mark all as read" execution.
* Deep linking directly guiding the user to the relevant complaint, project, or field assignment view.
* Live reactive bell icon in the app bar showing unread alert count with pull-to-refresh synchronization.

### 6. Email Verification & Password Recovery
* Cryptographically signed URLs for email verification coupled with mobile deep links (`bader://verify-email`).
* Anti-enumeration security in password recovery responses preventing account reconnaissance.
* API rate limiting (Throttle) with an interactive 60-second cooldown timer.
* Direct email verification bypass for authenticated Google OAuth accounts.

### 7. Community Interaction & Public Feed
* Curated public complaints stream (`is_public = true`) with strict redaction of citizen personally identifiable information (PII).
* Instant optimistic upvoting (Optimistic UI) with automatic state rollback upon network interruptions.
* Row-level locking (`lockForUpdate`) and composite unique constraint `UNIQUE(complaint_id, user_id)` preventing duplicate votes and negative counters.
* Decoupled community prioritization from official emergency triage, with automatic administrative notifications upon reaching 15 likes.
* Lightweight map pins endpoint with Bounding Box (`bbox`) viewport filtering.
* Scheduled Artisan reconciliation command (`complaints:reconcile-likes`) for periodic counter integrity verification.

---

## Web Dashboards & Portals

Web interfaces are built using **Laravel Blade**, **Livewire 3**, and **Tailwind CSS** in an Obsidian Dark Theme with full RTL layout support:

| Interface / Portal | Route | Primary Purpose | Authorization Level |
| :--- | :--- | :--- | :--- |
| **National Public Portal** | `/` (`home`) | Public landing page with live statistics, platform pillars, and recent resolutions | Public (No auth required) |
| **Public Transparency Registry** | `/public/complaints` | Directory of public complaints with citizen identity masked | Public (No auth required) |
| **Complaint Transparency Dossier** | `/public/complaints/{id}` | Detailed public audit timeline, proof of work, and resolution status | Public (No auth required) |
| **Development Projects Directory** | `/public/projects` | Directory of public infrastructure projects with progress metrics | Public (No auth required) |
| **Super Admin Global Dashboard** | `/admin/dashboard` | Nationwide KPI monitoring, ministry oversight, user and category management | Strict Middleware (`super_admin`) |
| **Ministry Admin Workstation** | `/ministry/dashboard` | Department-scoped ticket processing, field worker dispatching, and transfers | Strict Middleware (`ministry_admin`) |

---

## Technical Architecture & Stack

* **Backend & API Architecture:**
  * Framework: Laravel 13 running on PHP 8.3+.
  * Authentication: Laravel Jetstream and Laravel Sanctum token management.
  * API Layer: RESTful APIs versioned under `/api/v1`.
  * Transactions & Authorization: Atomic operations via `DB::transaction` and granular Policy authorization.
* **Database Management System:**
  * Database: MySQL 8+.
  * Schema: 18 normalized relational tables with foreign keys, composite indexes, and cascade deletion rules.
* **Web Dashboards:**
  * Components: Laravel Blade, Livewire 3, Alpine.js, and Tailwind CSS.
  * Layout: Responsive desktop sidebar / mobile touch drawer with full RTL support.
* **Mobile Application:**
  * Framework: Flutter (Dart SDK `^3.13.1`).
  * Architecture: Clean Architecture modularized by feature.
  * State Management: Riverpod 3.x using `Notifier<T>` / `AsyncNotifier<T>` patterns.
  * Networking: Dio HTTP client with interceptors for Sanctum Bearer token injection and structured exception handling.
  * Secure Storage: `flutter_secure_storage` storing credentials in Android Keystore / iOS Keychain.
  * Routing: GoRouter with reactive authentication-driven route guards and zero-flicker session restoration.
  * Geospatial & GIS: `flutter_map ^8.3.2`, `latlong2`, and `geolocator` with CARTO CDN basemaps.

---

## Technical Standards & Reliability
* **Data Integrity & Auditability:** Camera-locked geocoding prevents spatial spoofing, field worker GPS verification (Geofencing) is enforced prior to completion, and all status transitions are logged in `complaint_timeline`.
* **Concurrency Management:** Database transactions with row-level locks (`lockForUpdate`) and unique constraints eliminate race conditions and counter discrepancies.
* **Privacy by Default:** Public publishing requires explicit citizen consent, and public feeds strictly redact personal phone numbers, names, and identifiers.
* **Security & Rate Limiting:** Rate limiting (Throttle) guards sensitive auth routes, signed URLs secure email tokens, and generic error messages prevent user enumeration.

---

## Repository Structure

```text
├── backend/          # Laravel application (RESTful APIs, web portals, and admin consoles)
├── mobile/           # Flutter application (Cross-platform mobile client for citizens and field workers)
└── docs/             # Technical specifications, system analysis, and ERD diagrams
```

> [!TIP]
> For detailed technical specifications and database ERD schemas:  
> Refer to: **[System Analysis & Design Document](docs/system_analysis_and_design.md)**.

---

## Installation & Local Setup

### 1. Backend Setup
```bash
cd backend
composer install
cp .env.example .env     # Configure database credentials in .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

### 2. Mobile App Setup
```bash
cd mobile
flutter pub get
flutter run
```

---

## Live Demo Environment

The mobile application is configured by default to connect to the deployed production API:
```text
https://bader.ameen-dev.com/api/v1
```
To run the mobile app against a local development server, update `baseUrl` in:
`mobile/lib/core/network/api_endpoints.dart` to your local machine IP address, and start Laravel using:
```bash
php artisan serve --host=0.0.0.0
```

---

## Default Test Accounts

Standard seeded accounts are provided for development and testing across all system roles:

| Role | Email | Password | Administrative Scope |
| :--- | :--- | :--- | :--- |
| **Super Admin** | `admin@example.test` | `password` | Complete system management, ministries, settings, and projects |
| **Ministry Admin** | `ministry@example.test` | `password` | Ticket reviews, inter-departmental transfers, and field assignments |
| **Field Worker** | `worker@example.test` | `password` | Task reception, proximity verification, and post-repair proof |
| **Citizen** | `citizen@example.test` | `password` | Submitting grievances, progress tracking, and community engagement |

> [!NOTE]
> The password `password` is strictly configured for local testing and development environments.

---

## REST APIs v1 Reference

All platform endpoints are versioned under `/api/v1` and authenticated using Sanctum personal access tokens:

| Method | Endpoint | Description & Purpose | Access Level |
| :--- | :--- | :--- | :--- |
| `POST` | `/api/v1/auth/register` | Register citizen account and issue Sanctum token | Public |
| `POST` | `/api/v1/auth/login` | Authenticate user credentials and return access token | Public |
| `POST` | `/api/v1/auth/google` | Authenticate using Google ID Token | Public |
| `GET` | `/api/v1/auth/me` | Retrieve profile data, roles, and permissions | Authenticated (`auth:sanctum`) |
| `PUT` | `/api/v1/auth/me` | Update profile information (name, phone, national ID) without modifying roles | Authenticated (`auth:sanctum`) |
| `POST` | `/api/v1/auth/logout` | Revoke current token and terminate session | Authenticated (`auth:sanctum`) |
| `POST` | `/api/v1/auth/forgot-password` | Request password reset instructions with anti-enumeration protection | Public (Rate Limited) |
| `POST` | `/api/v1/auth/reset-password` | Reset password using valid reset token | Public (Rate Limited) |
| `GET` | `/api/v1/auth/email/verify/{id}/{hash}` | Verify email via signed URL with deep-link redirection | Public (Signed URL) |
| `POST` | `/api/v1/auth/email/resend` | Resend verification email to specified address | Public (Rate Limited) |
| `POST` | `/api/v1/auth/email/verification-notification` | Resend verification email for authenticated session | Authenticated (`auth:sanctum`) |
| `GET` | `/api/v1/auth/email/status` | Retrieve current email verification status | Authenticated (`auth:sanctum`) |
| `GET` | `/api/v1/ministries` | Browse active ministries and departments | Public |
| `GET` | `/api/v1/ministries/{id}` | Retrieve specific ministry details and departments | Public |
| `GET` | `/api/v1/categories` | Browse taxonomy categories with cascading filters | Public |
| `GET` | `/api/v1/categories/{id}` | Retrieve specific category and child subcategories | Public |
| `GET` | `/api/v1/notifications` | Paginated user notifications with unread filtering | Authenticated (`auth:sanctum`) |
| `PATCH` | `/api/v1/notifications/{id}/read` | Mark individual notification as read | Authenticated (`auth:sanctum`) |
| `POST` | `/api/v1/notifications/read-all` | Batch mark all user notifications as read | Authenticated (`auth:sanctum`) |
| `GET` | `/api/v1/complaints` | Paginated complaints list scoped by user role | Authenticated (`auth:sanctum`) |
| `POST` | `/api/v1/complaints` | Submit new complaint with auto-routing and attachments | Authenticated (`auth:sanctum`) |
| `GET` | `/api/v1/complaints/{id}` | Retrieve complaint dossier, attachments, and timeline | Authenticated (`auth:sanctum`) |
| `PATCH` | `/api/v1/complaints/{id}/status` | Update complaint status and log audit timeline event | Authenticated (`auth:sanctum`) |
| `POST` | `/api/v1/complaints/{id}/transfer` | Re-route complaint between departments or ministries | Ministry Admin / Staff |
| `POST` | `/api/v1/complaints/{id}/assign` | Assign complaint to verified field worker | Ministry Admin / Staff |
| `GET` | `/api/v1/complaints/public` | Paginated public complaints feed with PII redaction | Public |
| `GET` | `/api/v1/complaints/public/{id}` | Public complaint details with personal upvote state | Public |
| `GET` | `/api/v1/complaints/map` | Lightweight public complaint map markers with viewport filter (`bbox`) | Public |
| `POST` | `/api/v1/complaints/{id}/like` | Upvote public complaint with row-level locking | Authenticated (`auth:sanctum`) |
| `DELETE` | `/api/v1/complaints/{id}/like` | Remove upvote with non-negative counter guarantee | Authenticated (`auth:sanctum`) |
| `GET` | `/api/v1/field-assignments` | List field assignments assigned to current worker | Field Worker / Admin |
| `PATCH` | `/api/v1/field-assignments/{id}/accept` | Accept field assignment transitioning to `accepted` | Assigned Field Worker |
| `POST` | `/api/v1/field-assignments/{id}/start` | Log on-site arrival and start assignment execution | Assigned Field Worker |
| `POST` | `/api/v1/field-assignments/{id}/verify-location` | Live GPS proximity check against incident coordinates | Assigned Field Worker |
| `POST` | `/api/v1/field-assignments/{id}/complete` | Submit resolution report and evidence with strict geofencing | Assigned Field Worker |
| `GET` | `/api/v1/projects` | Browse active developmental projects and progress | Public |
| `GET` | `/api/v1/projects/{id}` | View detailed project breakdown and contributions | Public |
| `POST` | `/api/v1/projects/{id}/contribute` | Register simulated project contribution with precise arithmetic | Authenticated (`auth:sanctum`) |

---

## Project Roadmap

- [x] Monorepo repository setup & directory structuring.
- [x] Backend infrastructure setup (Laravel 13 + Jetstream + Sanctum + Livewire 3).
- [x] Mobile application project initialization (Flutter).
- [x] Database schema design and ERD documented in [System Analysis & Design](docs/system_analysis_and_design.md).
- [x] Database migrations implemented for all platform tables.
- [x] Eloquent models (18 models), relationships, and reproducible seeders.
- [x] Service layer and complaint processing engine with atomic transactions (`DB::transaction`).
- [x] Complete RESTful APIs v1 deployed under `/api/v1`.
- [x] Hierarchical authorization policies and form requests (Policies & FormRequests).
- [x] High-performance read query services (`ComplaintQueryService` & `StatisticsQueryService`).
- [x] Role-based authorization middleware enforcing access control (403 Forbidden).
- [x] Public transparency portal (`/`, `/public/complaints`, `/public/projects`).
- [x] Super Admin global oversight dashboard (`/admin/*`).
- [x] Ministry Admin operational processing workstation (`/ministry/*`).
- [x] Responsive web interface with dark theme, desktop sidebar, mobile drawer, and RTL support.
- [x] Backend automated test suite (100 tests passing with 467 assertions).
- [x] Mobile onboarding flow and first-launch persistence (Flutter Onboarding Flow).
- [x] Mobile authentication system (Login, registration, logout, and GoRouter auth guards with Riverpod).
- [x] API enrichment (Phase 01): Active ministries, cascading taxonomy categories, assignment acceptance, and GPS verification.
- [x] Core mobile data layer (Phase 02): Entities, models, and repository contracts for complaints, projects, and notifications.
- [x] Complaint tracking and timeline (Phase 04): Complaints directory, zoomable visual evidence viewer, and audit timeline.
- [x] Field worker operations (Phase 05): Task management, acceptance, 500m GPS proximity verification, and post-repair proof uploads.
- [x] Developmental projects showcase (Phase 06): Categorized project directory, phase breakdowns, and simulated contribution dialog.
- [x] Google authentication integration via Laravel Socialite and `google_sign_in`.
- [x] Interactive GIS map (Phase 07): Flutter Map with CARTO CDN, layer switcher, complaint and project markers, and status filters.
- [x] In-app notification center (Phase 08): Read/unread filtering, single and batch read updates, deep linking, and reactive bell badge.
- [x] User profile management (Phase 09): Profile viewing and updates via `PUT /api/v1/auth/me` with immutable roles, and standardized error/empty states.
- [x] Email verification & password recovery suite (Phase 10): Verification and password reset screens, signed URLs, anti-enumeration, and rate limiting.
- [x] Community engagement backend infrastructure (Phase 11): `complaint_likes` table, PII redaction, public feed/map endpoints, and reconciliation command `complaints:reconcile-likes`.
- [x] Mobile community engagement & public feed (Phase 12): Community complaints page, infinite pagination, filtering/sorting, optimistic upvoting with auto-rollback, privacy switch, and public map pins (105 Flutter tests passing 100%).

# منصة بــــادر - البوابة الوطنية الذكية لإدارة البلاغات والشكاوى التنموية وتتبعها جغرافياً

---

## الفهرس
* [نبذة عن المشروع](#نبذة-عن-المشروع)
* [الرؤية والأهداف الاستراتيجية](#الرؤية-والأهداف-الاستراتيجية)
* [الفئات المستهدفة وأدوار النظام](#الفئات-المستهدفة-وأدوار-النظام-rbac)
* [الوحدات الوظيفية والميزات الأساسية](#الوحدات-الوظيفية-والميزات-الأساسية)
* [بوابات الويب واللوحات الرقابية والشفافية](#بوابات-الويب-ولوحات-التحكم-الرقابية-والشفافية-العامة)
* [المعمارية التقنية المعتمدة](#المعمارية-التقنية-المعتمدة)
* [المتطلبات غير الوظيفية ومعايير الموثوقية](#المتطلبات-غير-الوظيفية-ومعايير-الموثوقية)
* [هيكلية المشروع والتوثيق](#هيكلية-المشروع-والتوثيق)
* [التشغيل السريع](#التشغيل-السريع)
* [حسابات الاختبار والتطوير](#حسابات-الاختبار-والتطوير)
* [توثيق واجهات البرمجة](#توثيق-واجهات-البرمجة)
* [حالة التقدم في المشروع](#حالة-التقدم-في-المشروع)

---

## نبذة عن المشروع
**منصة بــــادر** هي منظومة وطنية برمجية متكاملة تهدف إلى بناء حلقة وصل رقمية فعالة ومباشرة بين المواطنين ومختلف الجهات الحكومية (الوزارات والمؤسسات الرسمية). يتكون النظام من تطبيق للهواتف الذكية يخدم المواطنين والفرق الميدانية، إلى جانب بوابات ويب ولوحات تحكم مركزية متخصصة ومستقلة لكل وزارة وإدارة عليا وللشفافية العامة.

يتيح النظام للمواطنين إرسال وتوجيه الشكاوى بدقة عالية عبر نظام تصنيف متسلسل، مدعوماً بالتوثيق البصري الإلزامي من كاميرا التطبيق مع التقاط الإحداثيات الجغرافية المباشرة (GPS). كما يتميز النظام بمرونة إدارية تمكن الوزارات من إعادة توجيه البلاغات الخاطئة آلياً إلى جهات الاختصاص دون رفضها أو ضياعها. وبالإضافة إلى إدارة الشكاوى، يتضمن النظام قسماً تفاعلياً لعرض المشاريع الحكومية المخطط لها أو المتعثرة، متيحاً للمجتمع والشركات الاطلاع عليها ودعمها ضمن نموذج محاكاة للتمويل الجماعي، مما يرفع من مستوى الشفافية، يسرع الاستجابة الميدانية، ويعزز كفاءة إدارة الموارد والخدمات العامة.

---

## الرؤية والأهداف الاستراتيجية

### الرؤية
خلق بيئة حضرية وخدمية ذكية وشفافة تعتمد على المشاركة المجتمعية الفعالة (التعهيد الجماعي) للارتقاء بجودة البنية التحتية والخدمات العامة.

### الأهداف
1. **أتمتة وتوحيد قنوات البلاغات:** دمج قنوات استقبال الشكاوى لجميع القطاعات والوزارات الحكومية تحت مظلة منصة رقمية موحدة.
2. **رفع موثوقية البلاغات والحد من العشوائية:** إلزامية التوثيق المكاني والبصري المباشر لحظة تقديم الشكوى لضمان الجدية والمصداقية.
3. **تسريع الاستجابة والمعالجة الميدانية:** أتمتة توجيه البلاغات لجهة الاختصاص مع إتاحة التحويل البيني المرن بين الوزارات.
4. **تعزيز الشفافية والمشاركة المجتمعية:** إشراك أفراد المجتمع ومؤسسات القطاع الخاص في استعراض ومحاكاة تمويل المشاريع التنموية والمتعثرة.

---

## الفئات المستهدفة وأدوار النظام (RBAC)

يعتمد النظام على هيكلية صلاحيات هرمية صارمة لإدارة الوصول تضمن سرية وأمان البيانات:

* **المواطن (Citizen):**
  * تقديم البلاغات والشكاوى مع التوثيق بالصور والموقع الجغرافي المباشر.
  * تتبع مسار الشكوى وحالتها التشغيلية بشفافية من الإرسال وحتى الإنجاز.
  * تصفح قائمة المشاريع التنموية والمساهمة في دعمها (كنموذج محاكاة للتمويل).

* **الموظف الميداني (Field Worker):**
  * استلام المهام الميدانية الموجهة إليه وفق موقعه وتخصصه عبر التطبيق.
  * الانتقال إلى الموقع الجغرافي المحدد للبلاغ.
  * رفع توثيق بصري (صورة ما بعد الإصلاح) متطابق مع إحداثيات الموقع لإغلاق الطلب.

* **مشرف الوزارة (Ministry Admin):**
  * إدارة ومتابعة لوحة التحكم الخاصة بوزارته.
  * مراجعة وتدقيق البلاغات الواردة وإسنادها إلى فرق العمل الميدانية.
  * تحويل البلاغات التي وردت بالخطأ إلى وزارات أخرى بضغطة زر مع تحديث مسارها للمواطن.

* **المدير العام للنظام (Super Admin):**
  * الإشراف العام والشامل على المنصة وإدارة حسابات الوزارات ومسؤوليها.
  * إضافة وتعديل التصنيفات الرئيسية والفرعية للخدمات.
  * اعتماد ونشر المشاريع التنموية ومتابعة المؤشرات الإحصائية العامة للأداء الحكومي.

---

## الوحدات الوظيفية والميزات الأساسية

### 1. وحدة إدارة الشكاوى الديناميكية (Dynamic Complaint Module)
* **التصنيف المتسلسل:** تبدأ عملية تقديم البلاغ باختيار الوزارة، ثم تنبثق التصنيفات الرئيسية والفرعية التابعة لها تلقائياً، مع إتاحة خيار "أخرى" لكتابة الوصف يدوياً.
* **التوثيق المكاني والبصري الصارم:** إلزام المستخدم بالتقاط الصور مباشرة من كاميرا التطبيق الحية مع تعطيل إمكانية الرفع من معرض الصور، لضمان تسجيل إحداثيات الـ GPS الفعلية ومكافحة البلاغات غير الدقيقة.

### 2. وحدة التوجيه والتحويل المرن (Routing & Forwarding Module)
* **التوجيه التلقائي المباشر:** توجه الشكوى لحظياً إلى قاعدة بيانات الوزارة المختصة فور اعتماد إرسالها.
* **التحويل البيني بين الوزارات:** إمكانية تحويل البلاغ من وزارة إلى أخرى في حال الخطأ في التوجيه دون إلغاء الطلب، مع إرسال إشعار فوري للمواطن يوضح الجهة الجديدة التي تتابع شكواه.

### 3. وحدة المشاريع ومحاكاة التمويل الجماعي (Projects & Crowdfunding Module)
* بطاقات رقمية تفاعلية تستعرض تفاصيل المشاريع التنموية والمتعثرة (اسم المشروع، الوصف، التكلفة المقدرة، والجهة المستفيدة).
* شريط تقدم مرئي وحي يتحدث تلقائياً مع كل عملية دعم أو مساهمة افتراضية من قبل المواطنين أو المنظمات والشركات.

### 4. وحدة الخرائط الجغرافية التفاعلية (GIS & Mapping Module)
* تكامل مع أنظمة الخرائط الجغرافية لعرض مواقع البلاغات بدبابيس تفاعلية ملونة تدل على الحالة:
  * اللون الأحمر: بلاغ جديد.
  * اللون الأصفر: قيد المعالجة الميدانية.
  * اللون الأخضر: تم الإنجاز والمعالجة.

---

## بوابات الويب ولوحات التحكم الرقابية والشفافية العامة

توفر المنصة واجهات وبوابات ويب متخصصة مبنية باستخدام **Laravel Blade** و **Livewire** و **Tailwind CSS** بنظام تصميم فخم يعتمد درجات **الأسود الفاحم والأونيكس (Obsidian Black Theme)** مع لمسات وتطعيمات الهوية الوطنية اليمنية (الأحمر والذهبي والأبيض):

| البوابة / الواجهة | المسار (URL / Route) | الميزات والمهام التشغيلية الأساسية | الصلاحية وحاجز الأمان |
| :--- | :--- | :--- | :--- |
| **البوابة الوطنية العامة** | `/` (`home`) | قسم رئيسي تفاعلي مع إحصائيات حية، ركائز المنظومة، أحدث البلاغات المنجزة، والمشاريع التنموية | متاح للجميع (بدون تسجيل دخول) |
| **سجل الشفافية العامة** | `/public/complaints` | دليل رقمي عام للبلاغات مع حجب وتشفير بيانات المواطن الحساسة لحماية الخصوصية | متاح للجميع (بدون تسجيل دخول) |
| **ملف الشفافية للبلاغ** | `/public/complaints/{id}` | خط زمني تدقيقي علني يوثق مراحل المعالجة وإثبات الإنجاز دون كشف بيانات المواطن | متاح للجميع (بدون تسجيل دخول) |
| **دليل المشاريع التنموية** | `/public/projects` | رقابة مجتمعية على المشاريع التنموية ومتابعة مسار التنفيذ والإنجاز الفعلي (%) | متاح للجميع (بدون تسجيل دخول) |
| **لوحة الإدارة العامة (Super Admin)** | `/admin/dashboard` | رقابة وطنية شاملة، مؤشرات الأداء (KPIs)، توزيع الحالات، ومتابعة مركزية لكافة الوزارات والمستخدمين والمشاريع | محمية بـ Middleware صارم (`super_admin`) مع إرجاع 403 للمحاولات غير المصرح بها |
| **بوابة الوزارة والعمليات التشغيلية (Ministry Admin)** | `/ministry/dashboard` | محطة عمل تشغيلية لنطاق الإدارة المعنية: تدقيق البلاغات، إسناد المهام للفرق الميدانية، التحويل البيني بين الجهات، وتحديث المعالجة | محمية بـ Middleware صارم (`ministry_admin`) مع إرجاع 403 للمحاولات غير المصرح بها |

---

## المعمارية التقنية المعتمدة

* **الواجهة الخلفية وتطوير واجهات البرمجة (Backend & APIs):**
  * إطار العمل: Laravel 13 مع لغة PHP 8.2+، لإنشاء واجهات برمجية متينة (RESTful APIs v1) وإدارة المعاملات الذرية (`DB::transaction`) والصلاحيات الهرمية.
* **قواعد البيانات (Database):**
  * نظام MySQL 8+ لإدارة وتخزين العلاقات المترابطة بين الوزارات، الإدارات، المستخدمين، الشكاوى، مسارات التحويل، والتكليفات الميدانية.
* **بوابات الويب ولوحات التحكم الإدارية (Web Dashboards & Portals):**
  * قوالب **Laravel Blade** مدمجة مع **Livewire 3** و **Tailwind CSS** و **Alpine.js**.
  * **نظام التصميم وتجربة المستخدم (UI/UX System):**
    * **الثيم الأسود الفاخر (Obsidian Near-Black Theme):** اعتماد لوحة لونية هادئة وقاتمة (`#08080a`، `#0d0d11`، `zinc-800/900`) خالية تماماً من أي تشبعات زرقاء، مع شريط العلم اليمني وألوان الهوية الوطنية.
    * **الهيدر العائم (Floating Header):** شريط علوي زجاجي شبه شفاف عائم (`fixed top-0 z-40 backdrop-blur-md`) لا يقتطع من مساحة الصفحة الفعلية.
    * **القائمة الجانبية الثابتة (Sticky Sidebar):** شريط جانبي ثابت على أجهزة الكمبيوتر المكتبية والمحمولة، يتحول تلقائياً إلى درج جانبي منزلق (`Slide-over Drawer`) للشاشات اللمسية والجوالات.
    * **دعم وتنسيق الـ RTL:** تصحيح وتنسيق اتجاه النصوص والقوائم المنسدلة (`select`) لتثبيت السهم في الجهة المقابلة للنص العربي دون أي تداخل.
    * **التجاوب الشامل مع الهواتف الذكية:** تحويل تلقائي للجداول والبيانات المعقدة إلى بطاقات تفاعلية ذكية (Mobile Cards Feed) مخصصة للمس بالأصابع.
* **تطبيقات الهواتف المحمولة (Mobile App):**
  * إطار العمل: Flutter بلغة Dart، لبرمجة تطبيق هجين موحد وعالي الأداء يعمل على نظامي Android و iOS مع قفل الموقع الجغرافي والكاميرا الحية.
  * **إدارة الحالة (State Management):** استخدام Riverpod 3.x مع نمط `Notifier<T>` / `AsyncNotifier<T>` الحديث (وليس `StateNotifier` القديم).
  * **الشبكات (Networking):** عميل Dio HTTP مع حقن تلقائي لرمز المرور (Bearer token) عبر الـ interceptors ومعالجة مهيكلة للأخطاء.
  * **التخزين الآمن (Secure Storage):** مكتبة `flutter_secure_storage` لحفظ رمز Sanctum مشفراً في Android Keystore / iOS Keychain.
  * **التوجيه (Routing):** مكتبة GoRouter مرتبطة بـ Riverpod عبر `refreshListenable` لإدارة حراس التوجيه التفاعلية المعتمدة على المصادقة واستعادة الجلسة بسلاسة وبدون وميض (zero-flicker).
  * **البنية المعمارية (Architecture):** معمارية نظيفة (Clean Architecture) مقسمة حسب الميزة (`domain/entities`, `domain/repositories`, `data/models`, `data/datasources`, `data/repositories`, `application/`, `presentation/`).
* **خدمات نظم المعلومات الجغرافية (GIS):**
  * مكتبة Leaflet وواجهة Google Maps API لعرض وإدارة الخرائط والتثبيت الجغرافي والتحقق المحيطي (Geofencing).

---

## المتطلبات غير الوظيفية ومعايير الموثوقية

* **موثوقية التدفق وضمان عدم الضياع:** ضمان عدم إهمال أو ضياع أي شكوى بفضل مسار التحويل البيني بدلاً من الرفض العشوائي.
* **نزاهة وصحة البيانات:** منع التلاعب الجغرافي بقفل إحداثيات الموقع وقت التصوير، ومطابقة موقع الموظف الميداني مع موقع البلاغ وقت إتمام المعالجة.
* **قابلية التوسع الهيكلي:** بناء قواعد البيانات والخدمات البرمجية بطريقة تتيح إضافة قطاعات ومؤسسات ووزارات جديدة دون الحاجة لإعادة هيكلة النظام البرمجي.

---

## هيكلية المشروع والتوثيق

```text
├── backend/          # خادم الويب ولوحات التحكم ومخرجات الـ APIs (Laravel 13)
├── mobile/           # تطبيق الهاتف الذكي للمواطنين والفرق الميدانية (Flutter)
└── docs/             # وثائق التحليل والتصميم المعماري ومخططات قواعد البيانات (ERD)
```

> [!TIP]
> **للاطلاع على وثيقة التحليل الفني الشامل والمخططات المعمارية (ERD & Class Diagrams):**  
> تفضل بمراجعة وثيقة: **[تحليل وتصميم النظام ومخططات قاعدة البيانات](docs/system_analysis_and_design.md)**.

---

## التشغيل السريع

### 1. الواجهة الخلفية (Backend - Laravel)
```bash
cd backend
composer install
cp .env.example .env     # ثم ضبط إعدادات قاعدة البيانات في ملف .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

### 2. تطبيق الهاتف المحمول (Mobile - Flutter)
```bash
cd mobile
flutter pub get
flutter run
```

> [!IMPORTANT]
> **الاختبار على الأجهزة الحقيقية (نقطة اتصال من الهاتف → اللابتوب):**  
> عند الاختبار على هاتف حقيقي متصل بنقطة اتصال (Hotspot) مع اللابتوب، يجب تشغيل خادم Laravel ليستمع على جميع الواجهات:  
> ```bash
> php artisan serve --host=0.0.0.0 --port=8000
> ```  
> قم بتحديث `serverIp` في ملف `mobile/lib/core/network/api_endpoints.dart` ليطابق عنوان الـ IP الخاص باللابتوب (مثال: `192.168.43.172`).  
> في نظام Windows، اسمح للمنفذ 8000 بالمرور عبر جدار الحماية (Firewall):
> ```powershell
> New-NetFirewallRule -DisplayName "Laravel Dev" -Direction Inbound -LocalPort 8000 -Protocol TCP -Action Allow
> ```

---

## حسابات الاختبار والتطوير

تم تزويد النظام بحسابات اختبارية قياسية عبر الـ Seeders لتسهيل التجربة وفحص الصلاحيات والـ APIs:

| الدور (Role) | البريد الإلكتروني (Email) | كلمة المرور (Password) | الصلاحيات والمسؤوليات |
| :--- | :--- | :--- | :--- |
| **المدير العام (Super Admin)** | `admin@example.test` | `password` | إشراف شامل على المنصة، إدارة الوزارات، وتعديل الإعدادات |
| **مشرف الوزارة (Ministry Admin)** | `ministry@example.test` | `password` | مراجعة البلاغات، التحويل بين الوزارات، وإسناد المهام الميدانية |
| **الموظف الميداني (Field Worker)** | `worker@example.test` | `password` | استلام المهام الميدانية ورفع التوثيق البصري بعد الإصلاح |
| **المواطن (Citizen)** | `citizen@example.test` | `password` | تقديم الشكاوى، تتبع مسار المعالجة، ودعم المشاريع التنموية |

> [!NOTE]
> كلمة المرور `password` مخصصة لبيئة التطوير والاختبار المحلي فقط (Development Only).

---

## توثيق واجهات البرمجة

تتوفر جميع واجهات البرمجة الخاصة بالمنصة تحت البادئة الموحدة `/api/v1` ومحمية بواسطة Sanctum Tokens:

| الميثود (Method) | المسار (Endpoint) | الوصف والوظيفة | الصلاحية / المصادقة |
| :--- | :--- | :--- | :--- |
| `POST` | `/api/v1/auth/register` | إنشاء حساب مواطن جديد وإصدار توكن Sanctum تلقائياً | متاح للجميع (Public) |
| `POST` | `/api/v1/auth/login` | تسجيل الدخول للأنظمة والحصول على توكن الصلاحيات | متاح للجميع (Public) |
| `GET` | `/api/v1/auth/me` | استعراض بيانات الملف الشخصي والأدوار والصلاحيات للمستخدم الحالي | مصادقة (`auth:sanctum`) |
| `POST` | `/api/v1/auth/logout` | إبطال التوكن الحالي وتسجيل الخروج بأمان | مصادقة (`auth:sanctum`) |
| `GET` | `/api/v1/ministries` | استعراض قائمة الوزارات الحكومية النشطة وأقسامها | متاح للجميع (Public) |
| `GET` | `/api/v1/ministries/{id}` | استعراض تفاصيل وزارة معينة وأقسامها التابعة | متاح للجميع (Public) |
| `GET` | `/api/v1/categories` | استعراض التصنيفات المتسلسلة مع دعم الفلترة بحسب الوزارة والقسم والمستوى | متاح للجميع (Public) |
| `GET` | `/api/v1/categories/{id}` | استعراض تفاصيل تصنيف معين وتصنيفاته الفرعية | متاح للجميع (Public) |
| `GET` | `/api/v1/notifications` | استعراض إشعارات المستخدم مع الترقيم والفلترة بحسب غير المقروء | مصادقة (`auth:sanctum`) |
| `PATCH` | `/api/v1/notifications/{id}/read` | تأشير إشعار معين كمقروء للمستخدم | مصادقة (`auth:sanctum`) |
| `POST` | `/api/v1/notifications/read-all` | تأشير جميع إشعارات المستخدم كمقروءة دفعة واحدة | مصادقة (`auth:sanctum`) |
| `GET` | `/api/v1/complaints` | استعراض قائمة البلاغات مع الفلترة والبحث وتحديد النطاق بحسب الدور | مصادقة (`auth:sanctum`) |
| `POST` | `/api/v1/complaints` | تقديم بلاغ جديد مع التحقق الإجرائي، كشف التكرار، والتوجيه التلقائي | مصادقة (`auth:sanctum`) |
| `GET` | `/api/v1/complaints/{id}` | عرض تفاصيل البلاغ الشاملة (المرفقات، الخط الزمني، والتحويلات) | مصادقة (`auth:sanctum`) |
| `PATCH` | `/api/v1/complaints/{id}/status` | تحديث حالة البلاغ (بما في ذلك `reopened` و `assigned`) مع تدوين الخط الزمني | مصادقة (`auth:sanctum`) |
| `POST` | `/api/v1/complaints/{id}/transfer` | تحويل البلاغ بين الإدارات أو الوزارات مع حفظ سبب وتاريخ التحويل | موظف الوزارة / المشرف |
| `POST` | `/api/v1/complaints/{id}/assign` | إسناد البلاغ إلى باحث ميداني وتحديث حالة المهمة | موظف الوزارة / المشرف |
| `GET` | `/api/v1/field-assignments` | استعراض قائمة المهام الميدانية المسندة للموظف أو الإدارة | موظف ميداني / مشرف |
| `PATCH` | `/api/v1/field-assignments/{id}/accept` | قبول المهمة الميدانية والانتقال لحالة مقبولة (`accepted`) | الباحث الميداني المكلف |
| `POST` | `/api/v1/field-assignments/{id}/start` | تسجيل بدء تنفيذ المعاينة الميدانية وتحديث حالة البلاغ | الباحث الميداني المكلف |
| `POST` | `/api/v1/field-assignments/{id}/verify-location` | التحقق الجغرافي اللحظي لموقع الباحث الميداني مقارنة بإحداثيات البلاغ | الباحث الميداني المكلف |
| `POST` | `/api/v1/field-assignments/{id}/complete` | إتمام المهمة، رفع أدلة الإنجاز (`after`)، والتحقق الجغرافي الصارم (Geofencing) | الباحث الميداني المكلف |
| `GET` | `/api/v1/projects` | استعراض المشاريع التنموية ونسب الإنجاز والمبالغ المجمعة | متاح للجميع (Public) |
| `GET` | `/api/v1/projects/{id}` | استعراض تفاصيل المشروع ومراحله والمساهمات المسجلة | متاح للجميع (Public) |
| `POST` | `/api/v1/projects/{id}/contribute` | تسجيل مساهمة مجتمعية بمشروع بدقة مالية متناهية (BCMath) | مصادقة (`auth:sanctum`) |

---

## حالة التقدم في المشروع

- [x] تهيئة مستودع المشروع وهيكلة المجلدات (Monorepo Setup).
- [x] إعداد البنية التحتية للواجهة الخلفية (Laravel 13 + Jetstream + Sanctum + Livewire 3).
- [x] إعداد مشروع تطبيق الهاتف (Flutter).
- [x] توثيق وتصميم قاعدة البيانات والـ ERD كاملاً في [docs/system_analysis_and_design.md](docs/system_analysis_and_design.md).
- [x] إنشاء وتطبيق ملفات تهجير قاعدة البيانات (Migrations) لجميع الجداول.
- [x] إنشاء نماذج البيانات (17 Eloquent Models) والعلاقات الكاملة وبذور البيانات (Seeders).
- [x] بناء طبقة الخدمات ومحرك البلاغات والمعاملات الذرية (Services & Business Logic Layer).
- [x] إنشاء واجهات برمجة التطبيقات الكاملة (REST APIs v1) تحت مسار `/api/v1`.
- [x] تطبيق سياسات الأمان والتفويض الهرمي (Policies & FormRequests).
- [x] بناء طبقة الاستعلامات عالية الأداء (`ComplaintQueryService` و `StatisticsQueryService`).
- [x] تطبيق حواجز المصادقة والتفويض الصارمة عبر الـ Middleware وحظر الوصول غير المصرح به (403 Forbidden).
- [x] بناء بوابة الشفافية المجتمعية والرقابة العامة (`/` و `/public/complaints` و `/public/projects`).
- [x] لوحة الإدارة المركزية والرقابة الوطنية للمدير العام (`/admin/*`).
- [x] محطة العمل التشغيلية للوزارات والجهات الحكومية (`/ministry/*`).
- [x] نظام التصميم الفاخر (Obsidian Near-Black Theme `#08080a`، هيدر عائم، قائمة جانبية ثابتة، وواجهات متجاوبة بالكامل مع الجوالات).
- [x] جناح الاختبارات الآلية الشاملة (80 اختباراً بنجاح 100%، و 376 توكيداً).
- [x] تطوير شاشات الترحيب والتعريف بالنظام وتخزين الحالة الأولى (Flutter Onboarding Flow & SharedPreferences).
- [x] نظام المصادقة لتطبيق الهاتف: دورة كاملة لتسجيل الدخول / التسجيل / تسجيل الخروج متكاملة مع واجهات Laravel Sanctum، إدارة الحالة باستخدام Riverpod `Notifier<AuthState>`، عميل الشبكة Dio، التخزين الآمن `flutter_secure_storage`، وحراس التوجيه GoRouter مع استعادة الجلسة بدون وميض، مبنية على بنية Clean Architecture (بنجاح 3 اختبارات للمصادقة).
- [x] إثراء وتوسيع واجهات برمجة التطبيقات (Backend API Enrichment - Phase 01): إضافة واجهات الوزارات النشطة والتصنيفات المتسلسلة، نظام الإشعارات المتكامل، قبول المهام الميدانية، والتحقق الجغرافي اللحظي للموقع، ودعم حالتي `reopened` و `assigned` ومطابقة معايير المرفقات (`before` / `after`).
- [ ] المرحلة القادمة: الميزات الأساسية لتطبيق الهاتف — تقديم الشكاوى للمواطنين مع التوثيق بالكاميرا و GPS، التتبع في الوقت الفعلي، وتنفيذ المهام للفرق الميدانية.

---
---

# Bader Platform - A Smart National Portal for Public Complaints Management, Developmental Projects Support, and Geographic Tracking

---

## Table of Contents
* [Project Overview](#project-overview)
* [Vision & Strategic Objectives](#vision--strategic-objectives)
* [Target Roles & Access Control](#target-roles--access-control-rbac)
* [Functional Modules & Key Features](#functional-modules--key-features)
* [Web Dashboards & Public Transparency Architecture](#web-dashboards--public-transparency-architecture)
* [Technical Architecture & Stack](#technical-architecture--stack)
* [Non-Functional Requirements & System Reliability](#non-functional-requirements--system-reliability)
* [Project Structure & Documentation](#project-structure--documentation)
* [Quick Start & Installation](#quick-start--installation)
* [Default Test Accounts](#default-test-accounts)
* [REST APIs v1 Reference](#rest-apis-v1-reference)
* [Project Roadmap](#project-roadmap)

---

## Project Overview
**Bader Platform** is a comprehensive, centralized software ecosystem engineered to bridge communication between citizens and government bodies, including ministries and public authorities. The platform couples a cross-platform mobile application for citizens and field response teams with specialized, secure web dashboards for ministerial administrators and executive leadership, as well as an open public transparency portal.

Through the mobile application, citizens submit precisely categorized complaints accompanied by mandatory real-time camera captures and verified GPS coordinates. To eliminate ticket drop-off and bureaucratic dead ends, the portal features inter-ministerial forwarding, enabling agencies to re-route misdirected requests directly to the competent authority without cancellation. In addition to incident handling, the portal hosts an interactive development portal showcasing delayed and planned infrastructure initiatives, empowering the public and corporate entities to view details and participate through a simulated crowdfunding model to maximize transparency, resource utilization, and civic trust.

---

## Vision & Strategic Objectives

### Vision
To establish an intelligent, transparent urban and governmental service ecosystem driven by civic engagement and crowdsourcing to elevate public infrastructure and service delivery standards.

### Strategic Objectives
1. **Unified Grievance Ingestion:** Consolidate public complaints from disparate departmental silos into a single integrated digital pipeline.
2. **Mitigation of False Reports:** Enforce authentic on-site visual and spatial logging to eliminate fraudulent or duplicate submissions.
3. **Accelerated Field Resolution:** Automate direct incident dispatching to competent units with flexible inter-agency ticket routing.
4. **Transparent Civic Participation:** Involve community members and private organizations in tracking and simulating funding for vital infrastructure and stalled development projects.

---

## Target Roles & Access Control (RBAC)

The platform enforces a granular, hierarchical Role-Based Access Control architecture:

* **Citizen:**
  * Submits verified complaints with mandatory photo and GPS metadata.
  * Monitors real-time progression from submission through operational resolution.
  * Explores development projects and participates via simulated financial pledges.

* **Field Worker:**
  * Receives prioritized work orders through the field mobile application.
  * Navigates to verified geolocations of reported issues.
  * Submits mandatory post-resolution photographic proof with matching coordinates to officially close tickets.

* **Ministry Admin:**
  * Operates the designated ministerial management web console.
  * Assesses incoming tickets, verifies authenticity, and dispatches field teams.
  * Routes misclassified tickets to peer ministries with automated notifications sent to the reporting citizen.

* **Super Admin:**
  * Exercises holistic administrative oversight across all ministries and institutional accounts.
  * Manages global service hierarchies, classifications, and system configurations.
  * Reviews and approves development project listings and monitors national performance indicators and analytics.

---

## Functional Modules & Key Features

### 1. Dynamic Complaint Management Module
* **Cascading Selection Architecture:** Progressive categorization flow starting from ministry selection to primary and secondary categories, with a fallback custom description option.
* **Strict Visual & Spatial Verification:** Direct-from-camera photo capture requirement with disabled gallery uploads to guarantee immediate, untampered GPS geocoding.

### 2. Intelligent Routing & Forwarding Module
* **Automated Direct Dispatching:** Instantaneous routing of validated submissions into the target ministry's data repository upon creation.
* **Seamless Inter-Ministerial Transfer:** One-click reassignment between governmental departments to retain ticket history and avoid re-submission, automatically notifying the citizen of transfer details.

### 3. Projects & Crowdfunding Simulation Module
* Interactive project display cards presenting planned and stalled infrastructure projects, detailed budget requirements, and beneficiary sectors.
* Dynamic, real-time progress indicators reflecting collective public and corporate simulated pledges.

### 4. GIS & Interactive Mapping Module
* Integration with geographic mapping systems to project regional issue distribution using color-coded status pins:
  * Red: Newly registered incident.
  * Yellow: Field intervention currently in progress.
  * Green: Issue resolved and formally closed.

---

## Web Dashboards & Public Transparency Architecture

The platform provides dedicated, role-specific web interfaces built with **Laravel Blade**, **Livewire 3**, and **Tailwind CSS** styled in an **Obsidian Black theme** (`#08080a`) with subtle Yemeni national identity accents (Red, White, Black, Gold):

| Interface / Portal | URL / Route | Key Features & Responsibilities | Authorization / Security Barrier |
| :--- | :--- | :--- | :--- |
| **National Public Portal** | `/` (`home`) | Hero section with live statistics, platform pillars, recently resolved complaints, and national projects preview | Public (No auth required) |
| **Public Transparency Registry** | `/public/complaints` | Public directory of complaints with masked citizen identity for complete privacy protection | Public (No auth required) |
| **Complaint Transparency Dossier** | `/public/complaints/{id}` | Detailed public audit timeline, proof of work, and resolution status with citizen data masked | Public (No auth required) |
| **Development Projects Directory** | `/public/projects` | Community oversight of public developmental projects with progress tracking (%) | Public (No auth required) |
| **Super Admin Global Dashboard** | `/admin/dashboard` | National oversight KPI dashboard, status distributions, nationwide monitoring of complaints, ministries, categories, users, and projects | Strict Middleware (`super_admin`), 403 Forbidden for unauthorized roles |
| **Ministry Admin Operational Workstation** | `/ministry/dashboard` | Department-scoped operational processing: ticket reviews, field worker dispatching, inter-departmental transfers, and resolution tracking | Strict Middleware (`ministry_admin`), 403 Forbidden for unauthorized roles |

---

## Technical Architecture & Stack

* **Backend & API Architecture:**
  * Framework: Laravel 13 (PHP 8.2+) for constructing enterprise-grade RESTful APIs (v1), securing atomic transactional workflows (`DB::transaction`), and role-based policies.
* **Database Management System:**
  * MySQL 8+ relational database configured for normalized entities across ministries, departments, users, tickets, routing histories, field assignments, and developmental initiatives.
* **Web Dashboards & Administrative Portals:**
  * **Laravel Blade** coupled with **Livewire 3**, **Alpine.js**, and **Tailwind CSS**.
  * **UI/UX Design System:**
    * **Obsidian Near-Black Theme:** Curated deep obsidian palette (`#08080a`, `#0d0d11`, `zinc-800/900`) devoid of cold blue tinting, highlighted by Yemeni national identity touches (Red, Gold, White).
    * **Floating Header:** Translucent backdrop-blurred glass header (`fixed top-0 z-40 backdrop-blur-md`) maximizing viewport workspace without vertical content clipping.
    * **Sticky Desktop Sidebar & Mobile Drawer:** Fixed desktop navigation sidebar seamlessly transforming into a slide-over touch drawer on mobile and tablet screens.
    * **Refined RTL Typography & Form Controls:** Custom right-to-left alignment ensuring `select` arrows and action triggers rest opposite text labels without overlapping.
    * **Mobile-Responsive Data Cards:** Dual desktop-table / mobile-card rendering (Mobile Cards Feed) optimized for seamless one-handed touch interaction.
* **Mobile Application Development:**
  * Framework: Flutter (Dart) delivering a unified, high-performance client application across Android and iOS with native hardware integration (Camera and Location Services).
  * **State Management:** Riverpod 3.x with modern `Notifier<T>` / `AsyncNotifier<T>` pattern (not legacy `StateNotifier`).
  * **Networking:** Dio HTTP client with automatic Bearer token injection via interceptors and structured exception handling.
  * **Secure Storage:** `flutter_secure_storage` for encrypted Sanctum token persistence in Android Keystore / iOS Keychain.
  * **Routing:** GoRouter with Riverpod-driven `refreshListenable` for reactive auth-aware route guards and zero-flicker session restoration.
  * **Architecture:** Clean Architecture per feature (`domain/entities`, `domain/repositories`, `data/models`, `data/datasources`, `data/repositories`, `application/`, `presentation/`).
* **Geospatial & Mapping Integration:**
  * Leaflet library and Google Maps API for coordinate plotting, geofencing, and map visualization.

---

## Non-Functional Requirements & System Reliability

* **Zero-Drop Guarantee:** Resilient routing and transfer mechanisms prevent reports from being discarded due to misclassification.
* **Data & Evidence Integrity:** Hardened location coordinates prevent spatial spoofing and require on-site proximity verification for ticket resolution.
* **Architectural Scalability:** Modular relational schema and service layers designed to accommodate new ministries, municipalities, and sectors without refactoring base code.

---

## Project Structure & Documentation

```text
├── backend/          # Web server, admin dashboards, and RESTful APIs (Laravel 13)
├── mobile/           # Cross-platform mobile application for citizens and field teams (Flutter)
└── docs/             # Technical specifications, architecture, and ERD schemas
```

> [!TIP]
> **Comprehensive System Analysis & Architecture Diagrams (ERD & Class Diagrams):**  
> Explore the full technical document here: **[System Analysis & Design Document](docs/system_analysis_and_design.md)**.

---

## Quick Start & Installation

### 1. Backend (Laravel)
```bash
cd backend
composer install
cp .env.example .env     # Configure database credentials in .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

### 2. Mobile App (Flutter)
```bash
cd mobile
flutter pub get
flutter run
```

> [!IMPORTANT]
> **Physical Device Testing (Phone Hotspot → Laptop):**  
> When testing on a real phone connected via mobile hotspot, the Laravel server must listen on all interfaces:  
> ```bash
> php artisan serve --host=0.0.0.0 --port=8000
> ```  
> Update `serverIp` in `mobile/lib/core/network/api_endpoints.dart` to your laptop's Wi-Fi IP (e.g. `192.168.43.172`).  
> On Windows, allow port 8000 through the firewall:
> ```powershell
> New-NetFirewallRule -DisplayName "Laravel Dev" -Direction Inbound -LocalPort 8000 -Protocol TCP -Action Allow
> ```

---

## Default Test Accounts

The platform includes standard seeded accounts for development and API testing across all 4 system roles:

| Role | Email | Password | Responsibilities & Scope |
| :--- | :--- | :--- | :--- |
| **Super Admin** | `admin@example.test` | `password` | Full administrative control across all ministries and system configurations |
| **Ministry Admin** | `ministry@example.test` | `password` | Ticket review, inter-ministerial transfers, and field task dispatching |
| **Field Worker** | `worker@example.test` | `password` | Task execution, geolocation verification, and post-repair proof uploads |
| **Citizen** | `citizen@example.test` | `password` | Submitting grievances, real-time status tracking, and simulated project crowdfunding |

> [!NOTE]
> The password `password` is exclusively configured for local development and test environments.

---

## REST APIs v1 Reference

All platform endpoints are versioned under `/api/v1` and protected via Sanctum personal access tokens:

| Method | Endpoint | Description & Functionality | Access / Auth |
| :--- | :--- | :--- | :--- |
| `POST` | `/api/v1/auth/register` | Register a new citizen account and issue Sanctum token | Public |
| `POST` | `/api/v1/auth/login` | Authenticate user credentials and return access token | Public |
| `GET` | `/api/v1/auth/me` | Fetch authenticated user profile, roles, and permissions | Authenticated (`auth:sanctum`) |
| `POST` | `/api/v1/auth/logout` | Revoke current access token and log out | Authenticated (`auth:sanctum`) |
| `GET` | `/api/v1/ministries` | Browse active governmental ministries and departments | Public |
| `GET` | `/api/v1/ministries/{id}` | Retrieve specific ministry details and active departments | Public |
| `GET` | `/api/v1/categories` | Cascading taxonomy categories with ministry/department/parent filtering | Public |
| `GET` | `/api/v1/categories/{id}` | Retrieve specific category details and child subcategories | Public |
| `GET` | `/api/v1/notifications` | Paginated user notifications with unread filtering support | Authenticated (`auth:sanctum`) |
| `PATCH` | `/api/v1/notifications/{id}/read` | Mark an individual notification as read | Authenticated (`auth:sanctum`) |
| `POST` | `/api/v1/notifications/read-all` | Batch mark all user notifications as read | Authenticated (`auth:sanctum`) |
| `GET` | `/api/v1/complaints` | Paginated complaints list with filtering, search, and role scoping | Authenticated (`auth:sanctum`) |
| `POST` | `/api/v1/complaints` | File complaint with auto-routing, duplicate detection, and attachments | Authenticated (`auth:sanctum`) |
| `GET` | `/api/v1/complaints/{id}` | Retrieve comprehensive complaint details, history, and timeline | Authenticated (`auth:sanctum`) |
| `PATCH` | `/api/v1/complaints/{id}/status` | Update complaint status (including `reopened` & `assigned`) with timeline log | Authenticated (`auth:sanctum`) |
| `POST` | `/api/v1/complaints/{id}/transfer` | Inter-department / Inter-ministerial transfer with reason logging | Ministry Admin / Staff |
| `POST` | `/api/v1/complaints/{id}/assign` | Assign complaint to a verified field worker | Ministry Admin / Staff |
| `GET` | `/api/v1/field-assignments` | List field work assignments scoped to worker or department | Field Worker / Admin |
| `PATCH` | `/api/v1/field-assignments/{id}/accept` | Accept field assignment transitioning to `accepted` status | Assigned Field Worker |
| `POST` | `/api/v1/field-assignments/{id}/start` | Mark field assignment in-progress upon arrival | Assigned Field Worker |
| `POST` | `/api/v1/field-assignments/{id}/verify-location` | Real-time worker geolocation verification against incident coordinates | Assigned Field Worker |
| `POST` | `/api/v1/field-assignments/{id}/complete` | Submit resolution report and evidence (`after`) with strict geofencing | Assigned Field Worker |
| `GET` | `/api/v1/projects` | Browse active developmental projects and progress | Public |
| `GET` | `/api/v1/projects/{id}` | View detailed project breakdown, phases, and contributions | Public |
| `POST` | `/api/v1/projects/{id}/contribute` | Pledged financial contribution with atomic precision (BCMath) | Authenticated (`auth:sanctum`) |

---

## Project Roadmap

- [x] Monorepo repository setup & directory structuring.
- [x] Backend infrastructure setup (Laravel 13 + Jetstream + Sanctum + Livewire 3).
- [x] Mobile application project initialization (Flutter).
- [x] Comprehensive database design & ERD documentation in [docs/system_analysis_and_design.md](docs/system_analysis_and_design.md).
- [x] Complete database migrations implemented for all platform tables.
- [x] Complete Eloquent Models (17 Models), domain relationships, and reproducible seeders.
- [x] Services Layer & Complaint Engine with atomic database transactions (`DB::transaction`).
- [x] Complete RESTful APIs v1 implemented under `/api/v1`.
- [x] Hierarchical authorization & validation layer (Policies & FormRequests).
- [x] Query Layer (`ComplaintQueryService`, `StatisticsQueryService`) for high-performance read models.
- [x] Strict role-based authorization middleware barrier (403 Forbidden on unauthorized access).
- [x] Public Transparency & Community Oversight Portal (`/`, `/public/complaints`, `/public/projects`).
- [x] Super Admin Global Monitoring Dashboard (`/admin/*`).
- [x] Ministry Admin Operational Processing Workstation (`/ministry/*`).
- [x] Modern UI/UX Design System: Obsidian Near-Black Theme (`#08080a`), Floating Header, Sticky Desktop Sidebar / Mobile Drawer, full RTL & Mobile-Responsive Cards.
- [x] Comprehensive automated test suite (80 Tests: 100% Pass, 376 Assertions).
- [x] Mobile Onboarding Experience & First-Launch Persistence (Flutter PageView, SharedPreferences & Clean Architecture).
- [x] Mobile Authentication System: Full login/register/logout flow integrated with Laravel Sanctum API, Riverpod `Notifier<AuthState>`, Dio HTTP client, `flutter_secure_storage`, GoRouter auth guards with zero-flicker session restoration, and Clean Architecture (3 Auth Tests Passed).
- [x] Backend API Enrichment (Phase 01): Implemented endpoints for active ministries, cascading taxonomy categories, notification management, field assignment acceptance, and real-time worker geolocation verification; added `reopened` & `assigned` statuses, and aligned attachment schemas (`before` / `after`).
- [ ] Next Phase: Flutter Mobile App Core Features — Citizen complaint submission with camera/GPS, real-time tracking, and Field Worker task execution.

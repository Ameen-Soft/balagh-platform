# منصة بــــادر - البوابة الوطنية الذكية لإدارة البلاغات والشكاوى التنموية وتتبعها جغرافياً

---

## الفهرس
* [نبذة عن المشروع](#نبذة-عن-المشروع)
* [الرؤية والأهداف الاستراتيجية](#الرؤية-والأهداف-الاستراتيجية)
* [الفئات المستهدفة وأدوار النظام (RBAC)](#الفئات-المستهدفة-وأدوار-النظام-rbac)
* [الوحدات الوظيفية والميزات الأساسية](#الوحدات-الوظيفية-والميزات-الأساسية)
* [بوابات الويب ولوحات التحكم الرقابية والشفافية العامة](#بوابات-الويب-ولوحات-التحكم-الرقابية-والشفافية-العامة)
* [المعمارية التقنية المعتمدة](#المعمارية-التقنية-المعتمدة)
* [المتطلبات غير الوظيفية ومعايير الموثوقية](#المتطلبات-غير-الوظيفية-ومعايير-الموثوقية)
* [هيكلية المشروع والتوثيق](#هيكلية-المشروع-والتوثيق)
* [التشغيل السريع](#التشغيل-السريع)
* [حسابات الاختبار والتطوير](#حسابات-الاختبار-والتطوير)
* [توثيق واجهات البرمجة (REST APIs v1)](#توثيق-واجهات-البرمجة-rest-apis-v1)
* [حالة التقدم في المشروع (Roadmap)](#حالة-التقدم-في-المشروع-roadmap)

---

## نبذة عن المشروع
**منصة بــــادر** هي منظومة وطنية برمجية متكاملة تهدف إلى بناء حلقة وصل رقمية فعالة ومباشرة بين المواطنين ومختلف الجهات الحكومية (الوزارات والمؤسسات الرسمية). يتكون النظام من تطبيق للهواتف الذكية يخدم المواطنين والفرق الميدانية، إلى جانب بوابات ويب ولوحات تحكم مركزية متخصصة ومستقلة لكل وزارة وإدارة عليا وللشفافية العامة.

يتيح النظام للمواطنين إرسال وتوجيه الشكاوى بدقة عالية عبر نظام تصنيف متسلسل، مدعوماً بالتوثيق البصري الإلزامي من كاميرا التطبيق مع التقاط الإحداثيات الجغرافية المباشرة (GPS). كما يتميز النظام بمرونة إدارية تمكن الوزارات من إعادة توجيه البلاغات الخاطئة آلياً إلى جهات الاختصاص دون رفضها أو ضياعها. وبالإضافة إلى إدارة الشكاوى، يتضمن النظام قسماً تفاعلياً لاستعراض البلاغات العامة وتأييدها مجتمعياً (Community Likes & Upvotes) لرفع مستوى الاهتمام بالخدمات، وقسماً لعرض المشاريع الحكومية المخطط لها أو المتعثرة متيحاً للمجتمع والشركات الاطلاع عليها ودعمها ضمن نموذج محاكاة للتمويل الجماعي، مما يرفع من مستوى الشفافية، يسرع الاستجابة الميدانية، ويعزز كفاءة إدارة الموارد والخدمات العامة.

---

## الرؤية والأهداف الاستراتيجية

### الرؤية
خلق بيئة حضرية وخدمية ذكية وشفافة تعتمد على المشاركة المجتمعية الفعالة (التعهيد الجماعي) للارتقاء بجودة البنية التحتية والخدمات العامة.

### الأهداف
1. **أتمتة وتوحيد قنوات البلاغات:** دمج قنوات استقبال الشكاوى لجميع القطاعات والوزارات الحكومية تحت مظلة منصة رقمية موحدة.
2. **رفع موثوقية البلاغات والحد من العشوائية:** إلزامية التوثيق المكاني والبصري المباشر لحظة تقديم الشكوى لضمان الجدية والمصداقية.
3. **تسريع الاستجابة والمعالجة الميدانية:** أتمتة توجيه البلاغات لجهة الاختصاص مع إتاحة التحويل البيني المرن بين الوزارات.
4. **تعزيز الشفافية والمشاركة المجتمعية:** إشراك أفراد المجتمع ومؤسسات القطاع الخاص في استعراض ومحاكاة تمويل المشاريع التنموية والمتعثرة.
5. **تمكين التفاعل والتأييد المجتمعي:** إتاحة استعراض البلاغات العامة المؤكدة والتفاعل معها بالإعجاب لإبراز القضايا الأكثر إلحاحاً مجتمعياً وتنبيه المسؤولين لها دون الإخلال بمعايير الخطورة الرسمية.

---

## الفئات المستهدفة وأدوار النظام (RBAC)

يعتمد النظام على هيكلية صلاحيات هرمية صارمة لإدارة الوصول تضمن سرية وأمان البيانات:

* **المواطن (Citizen):**
  * تقديم البلاغات والشكاوى مع التوثيق بالصور والموقع الجغرافي المباشر وخيار النشر العام الاختياري.
  * تتبع مسار الشكوى وحالتها التشغيلية بشفافية من الإرسال وحتى الإنجاز.
  * استعراض البلاغات العامة والتفاعل معها بالإعجاب/التأييد للمساهمة في إبراز أولويات المجتمع.
  * تصفح قائمة المشاريع التنموية والمساهمة في دعمها (كنموذج محاكاة للتمويل).

* **الموظف الميداني (Field Worker):**
  * استلام المهام الميدانية الموجهة إليه وفق موقعه وتخصصه عبر التطبيق.
  * الانتقال إلى الموقع الجغرافي المحدد للبلاغ والتحقق المكاني اللحظي (Geofencing).
  * رفع توثيق بصري (صورة ما بعد الإصلاح) متطابق مع إحداثيات الموقع لإغلاق الطلب.

* **مشرف الوزارة (Ministry Admin):**
  * إدارة ومتابعة لوحة التحكم الخاصة بوزارته ومراقبة تنبيهات البلاغات المتصدرة مجتمعياً (Trending Alerts).
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
* **محدد النشر العام (Privacy by Default):** خيار صريح ومستقل للمواطن لجعل البلاغ عاماً ومتاحاً لتفاعل المجتمع، مع كونه خاصاً بشكل افتراضي لحماية الخصوصية.

### 2. وحدة التوجيه والتحويل المرن (Routing & Forwarding Module)
* **التوجيه التلقائي المباشر:** توجه الشكوى لحظياً إلى قاعدة بيانات الوزارة المختصة فور اعتماد إرسالها.
* **التحويل البيني بين الوزارات:** إمكانية تحويل البلاغ من وزارة إلى أخرى في حال الخطأ في التوجيه دون إلغاء الطلب، مع إرسال إشعار فوري للمواطن يوضح الجهة الجديدة التي تتابع شكواه.

### 3. وحدة المشاريع ومحاكاة التمويل الجماعي (Projects & Crowdfunding Module)
* بطاقات رقمية تفاعلية تستعرض تفاصيل المشاريع التنموية والمتعثرة (اسم المشروع، الوصف، التكلفة المقدرة، والجهة المستفيدة).
* شريط تقدم مرئي وحي يتحدث تلقائياً مع كل عملية دعم أو مساهمة افتراضية من قبل المواطنين أو المنظمات والشركات.

### 4. وحدة الخرائط الجغرافية التفاعلية (GIS & Mapping Module)
* خريطة تفاعلية شاملة مبنية بمحرك Flutter Map ومدعومة بخوادم CARTO السحابية (CARTO Basemaps CDN) بنمطي "النمط الأبيض الناصع (Positron)" و "صور الأقمار الصناعية (Satellite)".
* قائمة تحكم دائرية منبثقة بأنيميشن سلس (Animated Pop-out Menu) للتبديل الفوري بين أنماط الخرائط متوافقة تماماً مع الاتجاه من اليمين لليسار (RTL).
* تمييز مكاني بدبابيس مخصصة (Custom Teardrop Pins) مع ترميز لوني ذكي ودلالات بصرية واضحة:
  * اللون الأحمر: بلاغ جديد أو عاجل.
  * اللون الأصفر: قيد المعالجة الميدانية والتنفيذ.
  * اللون الأخضر: تم الإنجاز والمعالجة وإغلاق البلاغ.
  * اللون الأسود: مشاريع تنموية وحيوية جارية ومخطط لها.
* كروت معاينة سفلية تفاعلية (Bottom Preview Cards) تنبثق عند لمس أي دبوس لعرض ملخص سريع ونسب الإنجاز مع انتقال مباشر لشاشة التفاصيل.
* شريط فلترة زجاجي علوي متجاوب للتنقل بين (الكل، البلاغات فقط، المشاريع فقط) مع قائمة منسدلة لتصفية البلاغات بحسب الحالة التشغيلية.
* أدوات تحكم جغرافية عائمة تشمل التحديد اللحظي لموقع المستخدم (Live GPS Location)، التوسيط وضبط الإطار لكافة المواقع (Fit Bounds)، وأزرار التكبير والتصغير.

### 5. وحدة الإشعارات والتنبيهات الذكية (Smart In-App Notifications Module)
* **مركز إشعارات متكامل:** واجهة مخصصة لعرض إشعارات المستخدم بتصميم عصري وأنيق يتبع نظام تصميم بادر (Balagh Design System) مع دعم فرز الإشعارات (الكل، غير المقروءة).
* **بطاقات إشعارات تفاعلية:** تمييز لوني وبصري فوري بين الحالات المقروءة وغير المقروءة، ودعم الترقيم التلقائي مع السحب للتحديث (Pull-to-Refresh).
* **أيقونات حركية مصنفة:** دلالات بصرية ملونة بحسب طبيعة الحدث (تسجيل البلاغ، تغيير الحالة، التحويل بين الوزارات، الإنجاز الميداني، إسناد المهام، والمساهمة بالمشاريع، وتنبيهات البلاغات المتصدرة).
* **التوجيه العميق الذكي (Deep Linking):** انتقال سلس ومباشر بمجرد لمس الإشعار إلى شاشة تفاصيل البلاغ أو المشروع التنموي أو المهمة الميدانية المرتبطة به.
* **إدارة القراءة الآمنة:** إمكانية تأشير إشعار محدد كمقروء وتأشير جميع الإشعارات كمقروءة دفعة واحدة مع نافذة تأكيد آمنة.
* **أيقونة جرس حية في الرئيسية:** جرس تنبيهات مزود بعداد رقمي متفاعل (Badge) لعدد الإشعارات غير المقروءة في شريط التطبيق العلوي (AppBar) للشاشة الرئيسية.

### 6. وحدة التحقق من البريد الإلكتروني واستعادة كلمة المرور (Email Verification & Password Recovery Module)
* **واجهات مطابقة للهوية الوطنية:** تصميم واجهتي التحقق من البريد (`EmailVerificationPage`) واستعادة كلمة المرور (`ForgotPasswordPage`) متوافقتين مع الهوية البصرية لبادر وشريط العلم اليمني السفلي المخصص (`AuthCornerDecoration`).
* **روابط التحقق المشفرة والموقعة (Signed URLs):** إرسال روابط تحقق مؤقتة ومشفرة عبر خوادم البريد الرسمية مع صفحة ويب تفاعلية تدعم إعادة التوجيه الفوري للتطبيق عبر الروابط العميقة (`bader://verify-email`).
* **التوجيه العميق (Deep Linking):** دعم مخطط الروابط المخصصة `bader://` في تطبيق Flutter ونظام أندرويد لفتح التطبيق تلقائياً وتحديث حالة الحساب عند النقر على روابط البريد.
* **الأمان ومنع كشف الحسابات (Anti-Enumeration Security):** إرسال رسائل نجاح موحدة وآمنة في استعادة كلمة المرور لا تكشف ما إذا كان البريد الإلكتروني مسجلاً مسبقاً في النظام لحماية خصوصية المستخدمين.
* **تحديد معدل الطلبات (Rate Limiting & Cooldown):** حماية مسارات الـ API من الإساءة والرسائل المتكررة عبر Throttle، مع عداد تنازلي تفاعلي في واجهة المستخدم (60 ثانية) قبل السماح بإعادة الطلب.
* **تكامل سلس مع تسجيل الدخول بواسطة Google:** اعتماد التحقق التلقائي للبريد الموثوق الوارد من مزود Google دون فرض خطوات تحقق إضافية غير ضرورية.

### 7. وحدة التفاعل المجتمعي والبلاغات العامة (Community Interaction & Public Engagement Module)
* **خلاصة البلاغات العامة (Public Complaints Feed):** استعراض البلاغات العامة المصرح بنشرها (`is_public = true`) والتي تم اعتمادها وتدقيقها رسمياً وتمر بحالات نشطة (`under_review`, `assigned`, `in_progress`, `resolved`, `closed`)، مع استبعاد البلاغات المرفوضة والخاصة.
* **الحماية الكاملة للخصوصية والبيانات الشخصية (PII Redaction):** حجب وتشفير كامل لبيانات المواطن الحساسة (الاسم، رقم الهاتف، والبريد الإلكتروني) في واجهات ومخرجات البلاغات العامة عبر مورد مخصص (`PublicComplaintResource`).
* **التفاعل والتأييد المجتمعي (Community Upvoting & Likes):** إمكانية تسجيل وإلغاء الإعجاب بالبلاغات العامة من قبل المواطنين المسجلين لدعم المشاكل المشتركة في أحيائهم ومناطقهم.
* **سلامة التزامن ومنع الأخطاء الحسابية (Atomic Concurrency & Non-Negative Integers):** استخدام قفل السجلات (`lockForUpdate`) داخل المعاملات الذرية وفهرس مركب فريد `UNIQUE(complaint_id, user_id)` لمنع تكرار الإعجاب، مع ضمان عدم هبوط العدادات للقيم السالبة إطلاقاً عبر دوال المقارنة الشرطية.
* **فصل الأهمية المجتمعية عن الأولوية التشغيلية (Decoupled Prioritization):** الاعتماد على الإعجابات في الترتيب وإبراز الاهتمام المجتمعي دون تغيير الأولوية الرسمية للبلاغ (`priority: low, medium, high, urgent`) آلياً، حفاظاً على المعايير الفنية والتشغيلية المعتمدة لإدارة الأزمات.
* **التنبيه الإداري للبلاغات المتصدرة (Trending Complaints Alert):** تنبيه مشرفي الوزارة والمدير العام آلياً وتوثيق حدث بالخط الزمني للبلاغ (`community_trending_alert`) فور وصول الإعجابات إلى الحد المحدد في الإعدادات (15 إعجاباً) لمراجعتها وتدقيقها عاجلاً.
* **خريطة البلاغات العامة الذكية (Geospatial Community Map Pins):** واجهة مخصصة خفيفة وسريعة لاسترجاع دبابيس البلاغات العامة على الخريطة مع دعم الفلترة بالنطاق المكاني المرئي (Bounding Box - `bbox`) وعزل النقاط الصفرية لضمان أداء سلس بسرعة 60 إطاراً في الثانية.
* **أمر التسوية والمزامنة الذري (Artisan Reconciliation Command):** توفير أمر Artisan مخصص (`complaints:reconcile-likes`) لتدقيق ومزامنة العدادات المحسوبة دورياً مع جدول الإعجابات الفعلي ومعالجة أي تباين ناتج عن العمليات المتزامنة.

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
  * نظام MySQL 8+ لإدارة وتخزين العلاقات المترابطة بين الوزارات، الإدارات، المستخدمين، الشكاوى، تفاعلات الإعجاب المجتمعية، مسارات التحويل، والتكليفات الميدانية عبر 18 جدولاً مترابطاً.
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
  * محرك Flutter Map (`flutter_map 7.x`) وحزم `latlong2` و `geolocator`، مع دمج خوادم CARTO السحابية (CARTO Basemaps CDN) بمفتاح API مخصص لتقديم خرائط عالية السرعة والدقة بنمطي Positron والأقمار الصناعية دون علامات مائية.

---

## المتطلبات غير الوظيفية ومعايير الموثوقية

* **موثوقية التدفق وضمان عدم الضياع (Zero-Drop Guarantee):** ضمان عدم إهمال أو ضياع أي شكوى بفضل مسار التحويل البيني بدلاً من الرفض العشوائي.
* **نزاهة وصحة البيانات (Data Integrity):** منع التلاعب الجغرافي بقفل إحداثيات الموقع وقت التصوير، ومطابقة موقع الموظف الميداني مع موقع البلاغ وقت إتمام المعالجة.
* **قابلية التوسع الهيكلي (Architectural Scalability):** بناء قواعد البيانات والخدمات البرمجية بطريقة تتيح إضافة قطاعات ومؤسسات ووزارات جديدة دون الحاجة لإعادة هيكلة النظام البرمجي.
* **سلامة التزامن والعدّاد غير السالب (Concurrency & Non-Negative Counter Integrity):** قفل السجلات الذري (`lockForUpdate`) داخل المعاملات وقيد المفتاح المركب `UNIQUE(complaint_id, user_id)` لمنع سباق البيانات والتكرار مع صيانة العدادات دورياً.
* **الخصوصية كخيار افتراضي (Privacy by Default):** الحفاظ التام على سرية بلاغات المواطنين، وحظر النشر العام إلا برغبة واختيار صريح من صاحب البلاغ، مع حجب كامل للمعلومات الشخصية الحساسة.

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
> **الاتصال وتجربة التطبيق (بيئة الإنتاج / Hostinger):**  
> تم تهيئة تطبيق الهاتف المحمول (Flutter) حالياً للاتصال مباشرة بالخادم الحي المرفوع على استضافة Hostinger عبر الرابط `https://bader.ameen-dev.com/api/v1`. 
> بناءً على ذلك، لست بحاجة إلى إعداد اتصال محلي (Localhost / Hotspot) لتجربة التطبيق على هاتف حقيقي.
> 
> *إذا كنت ترغب في تطوير الواجهة الخلفية محلياً واختبارها، يمكنك تغيير `baseUrl` في ملف `mobile/lib/core/network/api_endpoints.dart` ليعود إلى عنوان الـ IP المحلي الخاص بحاسوبك وتشغيل Laravel بـ `php artisan serve --host=0.0.0.0`.*

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

## توثيق واجهات البرمجة (REST APIs v1)

تتوفر جميع واجهات البرمجة الخاصة بالمنصة تحت البادئة الموحدة `/api/v1` ومحمية بواسطة Sanctum Tokens:

| الميثود (Method) | المسار (Endpoint) | الوصف والوظيفة | الصلاحية / المصادقة |
| :--- | :--- | :--- | :--- |
| `POST` | `/api/v1/auth/register` | إنشاء حساب مواطن جديد وإصدار توكن Sanctum تلقائياً | متاح للجميع (Public) |
| `POST` | `/api/v1/auth/login` | تسجيل الدخول للأنظمة والحصول على توكن الصلاحيات | متاح للجميع (Public) |
| `POST` | `/api/v1/auth/google` | تسجيل الدخول السريع باستخدام حساب جوجل (Google ID Token) | متاح للجميع (Public) |
| `GET` | `/api/v1/auth/me` | استعراض بيانات الملف الشخصي والأدوار والصلاحيات للمستخدم الحالي | مصادقة (`auth:sanctum`) |
| `PUT` | `/api/v1/auth/me` | تعديل بيانات الملف الشخصي (الاسم، الهاتف، الرقم الوطني) مع التحقق الصارم وحظر تعديل الأدوار | مصادقة (`auth:sanctum`) |
| `POST` | `/api/v1/auth/logout` | إبطال التوكن الحالي وتسجيل الخروج بأمان | مصادقة (`auth:sanctum`) |
| `POST` | `/api/v1/auth/forgot-password` | إرسال رابط استعادة كلمة المرور بأمان مع منع استكشاف الحسابات | متاح للجميع (محدد المعدل) |
| `POST` | `/api/v1/auth/reset-password` | إعادة تعيين كلمة المرور عبر رمز التحقق والبيانات الجديدة | متاح للجميع (محدد المعدل) |
| `GET` | `/api/v1/auth/email/verify/{id}/{hash}` | التحقق من صحة البريد الإلكتروني عبر رابط موقع ومشفر مع توجيه ذكي للتطبيق | متاح للجميع (Signed URL) |
| `POST` | `/api/v1/auth/email/resend` | إعادة إرسال رسالة التحقق للبريد المحدد لغير المسجلين | متاح للجميع (محدد المعدل) |
| `POST` | `/api/v1/auth/email/verification-notification` | إعادة إرسال رابط التحقق من البريد للمستخدم المسجل الحالي | مصادقة (`auth:sanctum`) |
| `GET` | `/api/v1/auth/email/status` | التحقق من حالة توثيق البريد الإلكتروني للمستخدم الحالي | مصادقة (`auth:sanctum`) |
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
| `GET` | `/api/v1/complaints/public` | استعراض خلاصة البلاغات العامة المعتمدة مع الترقيم والفلترة وحجب بيانات المواطن | متاح للجميع (Public) |
| `GET` | `/api/v1/complaints/public/{id}` | استعراض تفاصيل البلاغ العام مع حالة الإعجاب الشخصي وحجب بيانات المواطن | متاح للجميع (Public) |
| `GET` | `/api/v1/complaints/map` | استرجاع دبابيس البلاغات العامة الخفيفة على الخريطة ضمن النطاق المكاني المرئي (`bbox`) | متاح للجميع (Public) |
| `POST` | `/api/v1/complaints/{id}/like` | تسجيل إعجاب وتأييد مجتمعي بالبلاغ العام مع معالجة التزامن الذري | مصادقة (`auth:sanctum`) |
| `DELETE` | `/api/v1/complaints/{id}/like` | إلغاء الإعجاب بالبلاغ العام مع ضمان عدم هبوط العداد للقيم السالبة | مصادقة (`auth:sanctum`) |
| `GET` | `/api/v1/field-assignments` | استعراض قائمة المهام الميدانية المسندة للموظف أو الإدارة | موظف ميداني / مشرف |
| `PATCH` | `/api/v1/field-assignments/{id}/accept` | قبول المهمة الميدانية والانتقال لحالة مقبولة (`accepted`) | الباحث الميداني المكلف |
| `POST` | `/api/v1/field-assignments/{id}/start` | تسجيل بدء تنفيذ المعاينة الميدانية وتحديث حالة البلاغ | الباحث الميداني المكلف |
| `POST` | `/api/v1/field-assignments/{id}/verify-location` | التحقق الجغرافي اللحظي لموقع الباحث الميداني مقارنة بإحداثيات البلاغ | الباحث الميداني المكلف |
| `POST` | `/api/v1/field-assignments/{id}/complete` | إتمام المهمة، رفع أدلة الإنجاز (`after`)، والتحقق الجغرافي الصارم (Geofencing) | الباحث الميداني المكلف |
| `GET` | `/api/v1/projects` | استعراض المشاريع التنموية ونسب الإنجاز والمبالغ المجمعة | متاح للجميع (Public) |
| `GET` | `/api/v1/projects/{id}` | استعراض تفاصيل المشروع ومراحله والمساهمات المسجلة | متاح للجميع (Public) |
| `POST` | `/api/v1/projects/{id}/contribute` | تسجيل مساهمة مجتمعية بمشروع بدقة مالية متناهية (BCMath) | مصادقة (`auth:sanctum`) |

---

## حالة التقدم في المشروع (Roadmap)

- [x] تهيئة مستودع المشروع وهيكلة المجلدات (Monorepo Setup).
- [x] إعداد البنية التحتية للواجهة الخلفية (Laravel 13 + Jetstream + Sanctum + Livewire 3).
- [x] إعداد مشروع تطبيق الهاتف (Flutter).
- [x] توثيق وتصميم قاعدة البيانات والـ ERD كاملاً في [docs/system_analysis_and_design.md](docs/system_analysis_and_design.md).
- [x] إنشاء وتطبيق ملفات تهجير قاعدة البيانات (Migrations) لجميع الجداول.
- [x] إنشاء نماذج البيانات (18 Eloquent Model) والعلاقات الكاملة وبذور البيانات (Seeders).
- [x] بناء طبقة الخدمات ومحرك البلاغات والمعاملات الذرية (Services & Business Logic Layer).
- [x] إنشاء واجهات برمجة التطبيقات الكاملة (REST APIs v1) تحت مسار `/api/v1`.
- [x] تطبيق سياسات الأمان والتفويض الهرمي (Policies & FormRequests).
- [x] بناء طبقة الاستعلامات عالية الأداء (`ComplaintQueryService` و `StatisticsQueryService`).
- [x] تطبيق حواجز المصادقة والتفويض الصارمة عبر الـ Middleware وحظر الوصول غير المصرح به (403 Forbidden).
- [x] بناء بوابة الشفافية المجتمعية والرقابة العامة (`/` و `/public/complaints` و `/public/projects`).
- [x] لوحة الإدارة المركزية والرقابة الوطنية للمدير العام (`/admin/*`).
- [x] محطة العمل التشغيلية للوزارات والجهات الحكومية (`/ministry/*`).
- [x] نظام التصميم الفاخر (Obsidian Near-Black Theme `#08080a`، هيدر عائم، قائمة جانبية ثابتة، وواجهات متجاوبة بالكامل مع الجوالات).
- [x] جناح الاختبارات الآلية الشاملة (100 اختبار Backend بنجاح 100% و 467 توكيداً).
- [x] تطوير شاشات الترحيب والتعريف بالنظام وتخزين الحالة الأولى (Flutter Onboarding Flow & SharedPreferences).
- [x] نظام المصادقة لتطبيق الهاتف: دورة كاملة لتسجيل الدخول / التسجيل / تسجيل الخروج متكاملة مع واجهات Laravel Sanctum، إدارة الحالة باستخدام Riverpod `Notifier<AuthState>`، عميل الشبكة Dio، التخزين الآمن `flutter_secure_storage`، وحراس التوجيه GoRouter مع استعادة الجلسة بدون وميض، مبنية على بنية Clean Architecture (بنجاح 3 اختبارات للمصادقة).
- [x] إثراء وتوسيع واجهات برمجة التطبيقات (Backend API Enrichment - Phase 01): إضافة واجهات الوزارات النشطة والتصنيفات المتسلسلة، نظام الإشعارات المتكامل، قبول المهام الميدانية، والتحقق الجغرافي اللحظي للموقع، ودعم حالتي `reopened` و `assigned` ومطابقة معايير المرفقات (`before` / `after`).
- [x] طبقة البيانات والنماذج الأساسية لتطبيق الهاتف (Flutter Core Models & Data Layer - Phase 02): بناء الكيانات والـ Models وعقود الـ Repositories ومصادر البيانات البعيدة لخصائص البلاغات (Complaints)، المشاريع التنموية (Projects)، والإشعارات (Notifications)، مع مطابقة عقود الـ JSON بنسبة 100% والتحقق باختبارات آلية شاملة (19 اختباراً ناجحاً بنسبة 100%).
- [x] تتبع الشكاوى والخط الزمني والمرفقات (Flutter Complaint Tracking - Phase 04): بناء واجهات استعراض "شكاواي" مع الترقيم التلقائي (Pagination) والسحب للتحديث (Pull-to-refresh) والتصفية بحسب الحالة، شاشة تفاصيل البلاغ الشاملة، عارض المرفقات البصرية الذكي بالصور المخزنة مؤقتاً (CachedNetworkImage) والتكبير التفاعلي (InteractiveViewer)، الخط الزمني التدقيقي لمسار البلاغ المعتمد على 11 حدثاً رسمياً، وتحديث الشاشة الرئيسية للربط مع البلاغات بنجاح 45 اختباراً آلياً بنسبة 100%.
- [x] المهام الميدانية والتنفيذ مع التحقق الجغرافي الصارم (Flutter Field Worker - Phase 05): بناء نظام متكامل لمهام الموظف الميداني وفق Clean Architecture، يشمل استعراض المهام مع الفلترة التفاعلية، تفاصيل المهمة والبلاغ والمرفقات، قبول المهمة (`accept`)، التحقق الجغرافي اللحظي لموقع العامل عبر الـ GPS ومطابقته مع السيرفر لنطاق 500 متر (`verify-location`)، منع بدء التنفيذ لغير المتواجدين بالنطاق، بدء التنفيذ (`start`)، التقاط صور إثبات الإنجاز المباشرة بالكاميرا حصراً (`after`)، تقرير الإنجاز الميداني، إتمام المهمة وحل البلاغ ذرياً (`complete`) مع تحديث حالة الشكوى في النظام، وحراسة التوجيه بحسب الدور (Role-based Routing) بنجاح 68 اختباراً آلياً بنسبة 100%.
- [x] استعراض المشاريع التنموية والمساهمة المجتمعية (Flutter Projects - Phase 06): بناء قسم المشاريع التنموية وفق Clean Architecture ونظام تصميم بادر، ويشمل دليل المشاريع المصنف مع البحث وشرائح الفلترة بحسب الحالة، شاشة تفاصيل المشروع الشاملة التي توضح الخط الزمني لمراحل التنفيذ ومؤشرات التقدم المالي، نافذة منبثقة تفاعلية للمساهمة المجتمعية الافتراضية مع التحقق من صحة المدخلات والتحديث اللحظي للحالة، متكاملة مع GoRouter ومدعومة باختبارات آلية ناجحة بنسبة 100%.
- [x] التكامل مع تسجيل الدخول بحساب جوجل (Google Sign-In Integration): دعم تسجيل الدخول الآمن والسريع للمواطنين باستخدام حسابات جوجل عبر إضافة المصادقة الاجتماعية (Laravel Socialite) في الواجهة الخلفية، واستخدام حزمة `google_sign_in` في تطبيق الهاتف، مع تحديث واجهات الاستخدام (UI)، وتطوير وحدة الاختبارات لتجاوز بيئة المحاكاة، وضمان موثوقية العمليات والرفع الفعلي في بيئة الإنتاج على استضافة (Hostinger).
- [x] الخريطة التفاعلية الشاملة وتتبع المواقع جغرافياً (Flutter Interactive Map - Phase 07): بناء شاشة خريطة تفاعلية متكاملة بالاعتماد على محرك Flutter Map وخوادم CARTO السحابية (CARTO Basemaps CDN) بنمطي "النمط الأبيض الناصع (Positron)" و "صور الأقمار الصناعية (Satellite)"، مع قائمة أنماط دائرية منبثقة بتأثيرات حركية (Animated Pop-out Menu) متوافقة مع اتجاه RTL، دبابيس جغرافية مخصصة ومصنفة لونياً للبلاغات والمشاريع التنموية، كروت معاينة سفلية ذكية (Interactive Bottom Preview Cards)، شريط فلترة علوي زجاجي للحالات والتصنيفات، وتحديد الموقع المباشر للمستخدم عبر الـ GPS وضبط الإطار التلقائي لجميع النقاط (Fit Bounds).
- [x] مركز الإشعارات والتنبيهات اللحظية (Flutter Notifications - Phase 08): بناء نظام إشعارات متكامل وفق معمارية Clean Architecture ونظام تصميم بادر الداكن، يشمل شاشة مخصصة لمركز الإشعارات مع الترقيم التلقائي والفلترة بين جميع الإشعارات وغير المقروءة، بطاقات إشعارات تفاعلية تميز بصرياً بين المقروء وغير المقروء مع أيقونات ديناميكية بحسب نوع الإشعار، إمكانية تأشير إشعار معين كمقروء وتأشير جميع الإشعارات كمقروءة دفعة واحدة، توجيه ذكي وسلس عند الضغط على الإشعار مباشرة إلى تفاصيل البلاغ أو المشروع أو المهمة الميدانية، أيقونة جرس الإشعارات مع عداد حي للوارد غير المقروء في شريط التطبيق الرئيسي (AppBar)، وتحديث تلقائي لحظي عند السحب للتحديث.
- [x] إدارة الملف الشخصي والتحسينات الشاملة للمنصة (Flutter Profile & Polish - Phase 09): بناء وحدة متكاملة للملف الشخصي (عرض وتعديل البيانات المصرح بها: الاسم، رقم الهاتف، الرقم الوطني) وفق معمارية Clean Architecture ونظام تصميم بادر الداكن، إضافة مسار حقيقي في Laravel `PUT /api/v1/auth/me` مع التحقق الصارم وحظر تعديل الأدوار والصلاحيات نهائياً، مزامنة فورية للحالة مع Riverpod وانعكاسها المباشر على الشاشة الرئيسية، تحسين التنقل ودعم كامل لاتجاه RTL، توحيد حالات القوائم الفارغة والأخطاء (`AppEmptyState` و `AppErrorState` مع التمييز الدقيق بين انقطاع الشبكة وأخطاء الخادم والتحقق)، دعم مرونة السحب للتحديث (Pull-to-refresh) عبر الحفاظ التام على البيانات المعروضة عند تعذر التحديث وعرض تنبيهات عائمة ذكية، واكتمال جناح الاختبارات الآلية (87 اختبار Backend بنجاح 100% و 74 اختبار Flutter بنجاح 100% بدون أي تحذيرات تحليل).
- [x] حزمة التحقق من البريد واستعادة كلمة المرور (Flutter Auth - Phase 10): تصميم وتطوير واجهتي التحقق من البريد واستعادة كلمة المرور وفق Clean Architecture والهوية اليمنية وشريط الزاوية الوطني السفلي، والربط بالواجهات الخلفية الآمنة مع الروابط الموقعة المشفرة، حظر استكشاف الحسابات (Anti-Enumeration)، تحديد معدل الطلبات (Rate Limiting)، الروابط العميقة (`bader://verify-email` و `bader://reset-password`)، عداد التهدئة (60 ثانية)، والتحقق الآلي لحسابات Google بنجاح 100% (16 اختبار Backend و 9 اختبارات Flutter UI).
- [x] التفاعل المجتمعي والبلاغات العامة - المرحلة الأولى (Community Interaction Backend & Database - Phase 11): بناء وتطبيق البنية التحتية الخلفية وقواعد البيانات لميزة التفاعل المجتمعي؛ إضافة حقول `is_public` و `likes_count` و `community_alerted_at` لجدول الشكاوى، إنشاء جدول `complaint_likes` مع الفهرس المركب الفريد `UNIQUE(complaint_id, user_id)` والحذف التتابعي، بناء خدمات التفاعل والمشاركة (`CommunityInteractionService` و `CommunityEngagementService`)، حجب وتشفير بيانات المواطن الحساسة في موارد العرض العامة (`PublicComplaintResource` و `PublicComplaintMapResource`)، إطلاق مسارات التغذية العامة والتفاعل وتأشير الخريطة (`/complaints/public` و `/complaints/map` و `/complaints/{id}/like`)، تنبيه المسؤولين آلياً للبلاغات المتصدرة دون التأثير على أولوية الطوارئ الرسمية، وتطوير أمر التسوية الذري `complaints:reconcile-likes`، مع نجاح 100% لجناح الاختبارات الآلية (7 اختبارات متخصصة و 40 توكيداً، بإجمالي 100 اختبار Backend بنجاح 100% و 467 توكيداً).
- [x] تطبيق الهاتف المحمول للتفاعل المجتمعي وبلاغات مجتمع بادر (Flutter Community Engagement & Public Feed - Phase 12): بناء وتكامل ميزة التفاعل المجتمعي في تطبيق Flutter وفق معمارية Clean Architecture ونظام تصميم بادر Obsidian؛ تحديث طبقة النطاق والبيانات (`ComplaintEntity` و `ComplaintModel`) بالحقول الجديدة (`likesCount` و `isLikedByMe` و `isPublic`)، تطوير موفري البيانات والمستودع (`getPublicComplaints`, `getMapComplaints`, `likeComplaint`, `unlikeComplaint`)، إنشاء مدير الحالة `CommunityComplaintsNotifier` مع دعم الترقيم اللحظي، الفرز (الأكثر تفاعلاً والأحدث)، التصفية بالتصنيف والحالة، التحديث التفاؤلي الفوري للإعجاب (Optimistic UI) والتراجع الآلي (Rollback) عند فشل الشبكة، تحديث `map_notifier` و `map_page` لعرض البلاغات العامة ونقاطها وإتاحة التفاعل المباشر من بطاقة المعاينة بالخريطة، إضافة مفتاح الخصوصية الصريح في شاشة إنشاء البلاغ `create_complaint_page`، بطاقة وصول سريعة في الشاشة الرئيسية `home_page` وتسجيل المسار في `app_router`، مع اكتمال جناح الاختبارات الآلية الشاملة بنجاح 100% (105 اختبارات Flutter بنجاح تام).

---
---

# Bader Platform - A Smart National Portal for Public Complaints Management, Developmental Projects Support, and Geographic Tracking

---

## Table of Contents
* [Project Overview](#project-overview)
* [Vision & Strategic Objectives](#vision--strategic-objectives)
* [Target Roles & Access Control (RBAC)](#target-roles--access-control-rbac)
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

Through the mobile application, citizens submit precisely categorized complaints accompanied by mandatory real-time camera captures and verified GPS coordinates. To eliminate ticket drop-off and bureaucratic dead ends, the portal features inter-ministerial forwarding, enabling agencies to re-route misdirected requests directly to the competent authority without cancellation. In addition to complaint management, the platform features an interactive community feed for browsing and upvoting public complaints (Community Likes & Upvotes) to highlight high-priority neighborhood issues, as well as an interactive development portal showcasing delayed and planned infrastructure initiatives, empowering the public and corporate entities to view details and participate through a simulated crowdfunding model to maximize transparency, resource utilization, and civic trust.

---

## Vision & Strategic Objectives

### Vision
To establish an intelligent, transparent urban and governmental service ecosystem driven by civic engagement and crowdsourcing to elevate public infrastructure and service delivery standards.

### Strategic Objectives
1. **Unified Grievance Ingestion:** Consolidate public complaints from disparate departmental silos into a single integrated digital pipeline.
2. **Mitigation of False Reports:** Enforce authentic on-site visual and spatial logging to eliminate fraudulent or duplicate submissions.
3. **Accelerated Field Resolution:** Automate direct incident dispatching to competent units with flexible inter-agency ticket routing.
4. **Transparent Civic Participation:** Involve community members and private organizations in tracking and simulating funding for vital infrastructure and stalled development projects.
5. **Community Engagement & Upvoting:** Enable citizens to browse verified public complaints and voice civic support via upvotes to highlight pressing local priorities while preserving rigorous official crisis classification standards.

---

## Target Roles & Access Control (RBAC)

The platform enforces a granular, hierarchical Role-Based Access Control architecture:

* **Citizen:**
  * Submits verified complaints with mandatory photo and GPS metadata, with an optional public visibility opt-in.
  * Monitors real-time progression from submission through operational resolution.
  * Browses and upvotes public neighborhood complaints to foster communal civic advocacy.
  * Explores development projects and participates via simulated financial pledges.

* **Field Worker:**
  * Receives prioritized work orders through the field mobile application.
  * Navigates to verified geolocations of reported issues with real-time GPS verification (Geofencing).
  * Submits mandatory post-resolution photographic proof with matching coordinates to officially close tickets.

* **Ministry Admin:**
  * Operates the designated ministerial management web console and monitors trending community alerts.
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
* **Privacy by Default:** Explicit, opt-in toggle allowing citizens to mark complaints as public for community interaction, defaulting to private to preserve citizen confidentiality.

### 2. Intelligent Routing & Forwarding Module
* **Automated Direct Dispatching:** Instantaneous routing of validated submissions into the target ministry's data repository upon creation.
* **Seamless Inter-Ministerial Transfer:** One-click reassignment between governmental departments to retain ticket history and avoid re-submission, automatically notifying the citizen of transfer details.

### 3. Projects & Crowdfunding Simulation Module
* Interactive project display cards presenting planned and stalled infrastructure projects, detailed budget requirements, and beneficiary sectors.
* Dynamic, real-time progress indicators reflecting collective public and corporate simulated pledges.

### 4. GIS & Interactive Mapping Module
* Comprehensive interactive map built with Flutter Map and powered by high-performance CARTO Basemaps CDN featuring dual themes: "Clean White (Positron)" and "Satellite Imagery".
* Animated circular pop-out layer control menu for seamless switching between map styles, engineered with full RTL layout alignment.
* Geographic markers with custom teardrop pins and intuitive status-based color coding:
  * Crimson Red: New or urgent incident report.
  * Amber Gold: Field intervention and repair currently in progress.
  * Emerald Green: Issue successfully resolved and verified.
  * Obsidian Black: Ongoing or planned developmental infrastructure projects.
* Interactive bottom preview cards popping up upon pin selection with issue/project summary, progress metrics, and direct navigation to detailed views.
* Frosted-glass top filter bar supporting type filters (All, Complaints only, Projects only) and a dropdown for filtering complaints by operational status.
* Floating geospatial utility controls: High-accuracy live GPS user tracking, automatic viewport bounds fitting (Fit Bounds), and RTL-friendly zoom controllers.

### 5. Smart In-App Notifications Module
* **Dedicated Notification Center:** A dedicated, polished notifications screen built in accordance with the Balagh Design System, featuring intuitive state-based tabs (All vs. Unread).
* **Interactive Notification Cards:** Immediate visual differentiation between read and unread records, infinite scrolling pagination, and pull-to-refresh synchronization.
* **Context-Aware Visual Badging:** Type-driven color-coded iconography representing complaint submission, status changes, inter-agency transfers, field completion, worker assignments, project pledges, and trending community alerts.
* **Smart Deep-Link Navigation:** One-tap contextual navigation directly guiding the user to the relevant complaint details, developmental project, or field assignment page.
* **Secure Read State Management:** Individual read status updates upon opening and atomic bulk "Mark all as read" execution backed by safety confirmation dialogs.
* **Live Reactive AppBar Bell:** Prominent notification bell icon on the home screen featuring a real-time reactive counter badge indicating unread incoming alerts.

### 6. Email Verification & Password Recovery Module
* **National Identity UI Alignment:** Custom-crafted Email Verification (`EmailVerificationPage`) and Forgot Password (`ForgotPasswordPage`) screens honoring the Balagh Obsidian visual branding with Yemeni national corner decorations (`AuthCornerDecoration`).
* **Cryptographically Signed URLs:** Secure, time-limited verification links dispatched via official mail servers coupled with web landing redirects that trigger mobile app deep links (`bader://verify-email`).
* **Deep Linking Engine:** Custom `bader://` URL scheme integration in Flutter and Android configurations for seamless instant account activation upon email link clicks.
* **Anti-Enumeration Security:** Unified, privacy-preserving responses that prevent user reconnaissance by never disclosing account existence on forgot-password submissions.
* **Rate Limiting & Cooldown Guards:** Strict API endpoint rate limits (Throttle) accompanied by an interactive client-side 60-second cooldown timer before retry allowances.
* **Google Authentication Integration:** Automatic email verification bypass for verified Google OAuth identities, avoiding redundant verification steps.

### 7. Community Interaction & Public Engagement Module
* **Public Complaints Feed:** Curated stream of citizen-shared reports (`is_public = true`) restricted to officially vetted and active operational states (`under_review`, `assigned`, `in_progress`, `resolved`, `closed`), strictly omitting unapproved or rejected cases.
* **Strict PII Redaction & Privacy:** Complete sanitization and masking of sensitive citizen data (names, phone numbers, email addresses, and national IDs) across all public resources (`PublicComplaintResource`).
* **Community Upvoting & Likes:** Authenticated citizens can voice support and upvote public grievances, reflecting collective community impact on local neighborhood issues.
* **Atomic Concurrency & Non-Negative Integers:** Database transactions with row-level locks (`lockForUpdate`) and a composite unique constraint `UNIQUE(complaint_id, user_id)` preventing duplicate votes, with portable conditional expressions preventing negative counters.
* **Decoupled Community Prioritization:** Community engagement dynamically influences public sorting and administrative awareness without distorting the official operational priority (`priority: low, medium, high, urgent`), upholding standard crisis triage integrity.
* **Trending Incident Alerts:** Automated system notifications dispatched to ministerial supervisors and Super Admins alongside an audit timeline log (`community_trending_alert`) whenever a report crosses the configured threshold (15 likes).
* **Geospatial Community Map Pins:** Dedicated, high-speed map pins endpoint returning lightweight DTOs with Bounding Box (`bbox`) viewport filtering and coordinate validation for fluid 60fps mobile exploration.
* **Artisan Reconciliation Command:** Dedicated administrative CLI tool (`complaints:reconcile-likes`) for periodically auditing and synchronizing cached counter columns with actual interaction rows.

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
  * MySQL 8+ relational database configured for normalized entities across ministries, departments, users, tickets, community likes, routing histories, field assignments, and developmental initiatives across 18 interrelated tables.
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
  * Flutter Map engine (`flutter_map 7.x`), `latlong2`, and `geolocator` integrated with authenticated CARTO Basemaps CDN for high-speed tile rendering across Positron and high-resolution Satellite layers without watermarks.

---

## Non-Functional Requirements & System Reliability

* **Zero-Drop Guarantee:** Resilient routing and transfer mechanisms prevent reports from being discarded due to misclassification.
* **Data & Evidence Integrity:** Hardened location coordinates prevent spatial spoofing and require on-site proximity verification for ticket resolution.
* **Architectural Scalability:** Modular relational schema and service layers designed to accommodate new ministries, municipalities, and sectors without refactoring base code.
* **Concurrency & Counter Integrity:** Row-level locks (`lockForUpdate`), composite uniqueness constraints `UNIQUE(complaint_id, user_id)`, and non-negative constraints prevent race conditions and counter desynchronization.
* **Privacy by Default:** Absolute citizen anonymity in public feeds, requiring explicit opt-in for publication, with full redaction of personal identifiable information.

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
> **Connectivity & Testing (Production Environment / Hostinger):**  
> The mobile application is currently configured to connect directly to the live production server deployed on Hostinger at `https://bader.ameen-dev.com/api/v1`. 
> Consequently, you no longer need to set up local networking (Localhost / Hotspot firewall rules) to test the app on a physical device.
> 
> *If you need to develop and test backend changes locally, you can easily revert the `baseUrl` inside `mobile/lib/core/network/api_endpoints.dart` to your machine's local IP address and run Laravel using `php artisan serve --host=0.0.0.0`.*

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
| `POST` | `/api/v1/auth/google` | Authenticate user seamlessly via Google ID Token | Public |
| `GET` | `/api/v1/auth/me` | Fetch authenticated user profile, roles, and permissions | Authenticated (`auth:sanctum`) |
| `PUT` | `/api/v1/auth/me` | Update profile information (name, phone, national ID) with strict role immutability | Authenticated (`auth:sanctum`) |
| `POST` | `/api/v1/auth/logout` | Revoke current access token and log out | Authenticated (`auth:sanctum`) |
| `POST` | `/api/v1/auth/forgot-password` | Send password reset instructions link with zero-enumeration security | Public (Rate Limited) |
| `POST` | `/api/v1/auth/reset-password` | Reset account password using token and credentials | Public (Rate Limited) |
| `GET` | `/api/v1/auth/email/verify/{id}/{hash}` | Verify email via signed URL with automated deep linking to app | Public (Signed URL) |
| `POST` | `/api/v1/auth/email/resend` | Resend verification email to specified unauthenticated address | Public (Rate Limited) |
| `POST` | `/api/v1/auth/email/verification-notification` | Resend verification email for currently authenticated session | Authenticated (`auth:sanctum`) |
| `GET` | `/api/v1/auth/email/status` | Retrieve current email verification status and timestamp | Authenticated (`auth:sanctum`) |
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
| `GET` | `/api/v1/complaints/public` | Paginated public complaints feed with status filtering and PII redaction | Public |
| `GET` | `/api/v1/complaints/public/{id}` | Retrieve public complaint details with personal like status and PII redaction | Public |
| `GET` | `/api/v1/complaints/map` | Retrieve lightweight public complaint pins for interactive map within bounding box (`bbox`) | Public |
| `POST` | `/api/v1/complaints/{id}/like` | Upvote/like a public complaint with concurrency-safe atomic locking | Authenticated (`auth:sanctum`) |
| `DELETE` | `/api/v1/complaints/{id}/like` | Remove upvote/like from a public complaint with non-negative guarantee | Authenticated (`auth:sanctum`) |
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
- [x] Complete Eloquent Models (18 Models), domain relationships, and reproducible seeders.
- [x] Services Layer & Complaint Engine with atomic database transactions (`DB::transaction`).
- [x] Complete RESTful APIs v1 implemented under `/api/v1`.
- [x] Hierarchical authorization & validation layer (Policies & FormRequests).
- [x] Query Layer (`ComplaintQueryService`, `StatisticsQueryService`) for high-performance read models.
- [x] Strict role-based authorization middleware barrier (403 Forbidden on unauthorized access).
- [x] Public Transparency & Community Oversight Portal (`/`, `/public/complaints`, `/public/projects`).
- [x] Super Admin Global Monitoring Dashboard (`/admin/*`).
- [x] Ministry Admin Operational Processing Workstation (`/ministry/*`).
- [x] Modern UI/UX Design System: Obsidian Near-Black Theme (`#08080a`), Floating Header, Sticky Desktop Sidebar / Mobile Drawer, full RTL & Mobile-Responsive Cards.
- [x] Comprehensive automated test suite (100 Backend tests: 100% Pass, 467 Assertions).
- [x] Mobile Onboarding Experience & First-Launch Persistence (Flutter PageView, SharedPreferences & Clean Architecture).
- [x] Mobile Authentication System: Full login/register/logout flow integrated with Laravel Sanctum API, Riverpod `Notifier<AuthState>`, Dio HTTP client, `flutter_secure_storage`, GoRouter auth guards with zero-flicker session restoration, and Clean Architecture (3 Auth Tests Passed).
- [x] Backend API Enrichment (Phase 01): Implemented endpoints for active ministries, cascading taxonomy categories, notification management, field assignment acceptance, and real-time worker geolocation verification; added `reopened` & `assigned` statuses, and aligned attachment schemas (`before` / `after`).
- [x] Flutter Core Models & Data Layer (Phase 02): Implemented Clean Architecture Domain Entities, Data Models, Repository Contracts, and Remote Data Sources for Complaints, Projects, and Notifications features; strictly aligned with backend JSON contracts and backed by comprehensive unit tests (19 Tests: 100% Pass).
- [x] Complaint Tracking, Timeline & Attachments (Flutter Complaint Tracking - Phase 04): Implemented citizen complaints directory with reactive pagination, pull-to-refresh, status filtering chips, comprehensive complaint details view, cached visual evidence viewer with interactive zoom (CachedNetworkImage + InteractiveViewer), full audit timeline supporting all 11 official event types, and seamless home page integration, backed by 45 automated tests passing 100%.
- [x] Field Worker Operations & Geofenced Task Execution (Flutter Field Worker - Phase 05): Comprehensive field worker suite following Clean Architecture, featuring status-filtered tasks directory, task and complaint details view, task acceptance (`accept`), real-time GPS proximity verification enforced by the backend within 500m (`verify-location`), geofence-locked execution start (`start`), live camera capture for post-repair evidence (`after`), field completion report, atomic task resolution (`complete`) with automatic complaint state transition to `resolved`, and role-based route guards for field workers, backed by 68 automated tests passing 100%.
- [x] Developmental Projects Discovery & Community Contributions (Flutter Projects - Phase 06): Implemented developmental projects showcase adhering to Clean Architecture and Balagh Design System, featuring categorized projects directory with search and status filtering chips, comprehensive project details view with execution phases timeline and financial progress indicators, simulated community contributions dialog with validation and instant state synchronization, integrated with GoRouter and backed by automated unit tests passing 100%.
- [x] Google Sign-In Integration: Seamless and secure citizen authentication via Google accounts, utilizing Laravel Socialite on the backend and the `google_sign_in` package on the mobile app. Includes UI updates, robust test environment handling, and production-ready configuration & deployment on Hostinger.
- [x] Interactive GIS Mapping & Spatial Exploration (Flutter Interactive Map - Phase 07): Implemented a comprehensive interactive geographic map utilizing Flutter Map and authenticated CARTO Basemaps CDN supporting dual Positron and Satellite styles, an animated pop-out circular layers menu with RTL layout alignment, custom status-coded teardrop pins for complaints and development projects, interactive bottom preview cards with direct routing, a frosted-glass top filter bar (by type and status), live GPS location tracking, and camera viewport bounding (Fit Bounds).
- [x] In-App Notification Center & Real-Time Alerts (Flutter Notifications - Phase 08): Implemented a complete notifications system adhering to Clean Architecture and the Balagh Design System, featuring a dedicated notifications center with reactive pagination and filtering (All vs. Unread), interactive notification cards visually distinguishing read and unread states with dynamic type-based iconography, single and batch "Mark all as read" capabilities, intelligent deep-link navigation to complaints, developmental projects, or field tasks, and an AppBar notification bell with a live reactive unread count badge and pull-to-refresh synchronization.
- [x] User Profile Management & Platform Polish (Flutter Profile & Polish - Phase 09): Implemented a full-featured user profile management suite (viewing & updating authorized fields: name, phone number, national ID) adhering to Clean Architecture and the Balagh Obsidian theme; added dedicated Laravel `PUT /api/v1/auth/me` endpoint with strict validation and total immutability of user roles and permissions; instant state synchronization via Riverpod reflected dynamically across Home cards; polished RTL navigation and GoRouter route guards; unified error and empty states (`AppEmptyState` & `AppErrorState` with rigorous offline vs. server vs. validation error handling); resilient pull-to-refresh across all primary listing screens that preserves previously loaded data upon network dropouts and displays non-intrusive floating snackbars; backed by 100% passing automated suites (87 Backend tests and 74 Flutter tests with zero analysis issues).
- [x] Email Verification & Password Recovery Suite (Flutter Auth - Phase 10): Implemented pixel-perfect Email Verification and Forgot Password screens adhering to Clean Architecture, Yemeni national branding, and custom bottom corner ribbon styling; fully connected to secure Laravel backend APIs featuring signed URLs, anti-enumeration security responses, rate limiting (Throttle), deep linking (`bader://verify-email` & `bader://reset-password`), 60s cooldown timer, live status polling, and Google Sign-in automatic verification; validated with 100% passing tests (16 Backend and 9 Flutter UI tests with zero analyzer warnings).
- [x] Community Interaction Backend & Database Architecture (Phase 11): Engineered and implemented the complete backend infrastructure for citizen community engagement; added `is_public`, `likes_count`, and `community_alerted_at` fields to `complaints` table; created `complaint_likes` table with composite `UNIQUE(complaint_id, user_id)` and cascade deletion; developed `CommunityInteractionService` and `CommunityEngagementService`; guaranteed strict citizen PII redaction across dedicated resources (`PublicComplaintResource` & `PublicComplaintMapResource`); deployed public endpoints (`/complaints/public`, `/complaints/map`, and `/complaints/{id}/like`); implemented automated administrative trending alerts without altering official operational priority; and added an atomic Artisan reconciliation command `complaints:reconcile-likes`, backed by 100% passing automated test suite (7 dedicated feature tests, 40 assertions, 100 total Backend tests passing with 467 assertions).
- [x] Flutter Community Engagement & Public Complaints Feed (Phase 12): Implemented the end-to-end community interaction suite in the Flutter application following Clean Architecture and the Balagh Obsidian design system; extended domain entities and models (`ComplaintEntity`, `ComplaintModel`) with `likesCount`, `isLikedByMe`, and `isPublic`; updated remote data sources and repository contracts (`getPublicComplaints`, `getMapComplaints`, `likeComplaint`, `unlikeComplaint`); created `CommunityComplaintsNotifier` with infinite scroll pagination, multi-criteria filtering (category, status, query) and sorting (popular vs. latest); engineered immediate optimistic liking (Optimistic UI) with automatic state rollback on network disruptions; updated `map_notifier` and `map_page` to retrieve lightweight public map pins with in-map liking and live preview cards; added explicit privacy opt-in switch with explanation in `create_complaint_page`; embedded quick access community card in `home_page`; registered `/community-complaints` in `app_router`; backed by 100% passing automated test suite (105 Flutter tests passing with zero failures).

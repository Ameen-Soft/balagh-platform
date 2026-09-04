# بوابة وطنية ذكية لإدارة الشكاوى العامة ودعم المشاريع التنموية وتتبعها جغرافياً

---

## الفهرس
* [نبذة عن المشروع](#نبذة-عن-المشروع)
* [الرؤية والأهداف الاستراتيجية](#الرؤية-والأهداف-الاستراتيجية)
* [الفئات المستهدفة وأدوار النظام](#الفئات-المستهدفة-وأدوار-النظام-rbac)
* [الوحدات الوظيفية والميزات الأساسية](#الوحدات-الوظيفية-والميزات-الأساسية)
* [المعمارية التقنية المعتمدة](#المعمارية-التقنية-المعتمدة)
* [المتطلبات غير الوظيفية ومعايير الموثوقية](#المتطلبات-غير-الوظيفية-ومعايير-الموثوقية)

---

## نبذة عن المشروع
البوابة الوطنية الذكية هي منصة برمجية متكاملة تهدف إلى بناء حلقة وصل رقمية فعالة ومباشرة بين المواطنين ومختلف الجهات الحكومية (الوزارات والمؤسسات الرسمية). يتكون النظام من تطبيق للهواتف الذكية يخدم المواطنين والفرق الميدانية، إلى جانب لوحات تحكم مركزية متخصصة ومستقلة لكل وزارة وإدارة عليا.

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

## المعمارية التقنية المعتمدة

* **الواجهة الخلفية وتطوير واجهات البرمجة (Backend & APIs):**
  * إطار العمل: Laravel مع لغة PHP، لإنشاء واجهات برمجية متينة (RESTful APIs) وإدارة العمليات والصلاحيات المعقدة بكفاءة وأمان عاليين.
* **قواعد البيانات (Database):**
  * نظام MySQL لإدارة وتخزين العلاقات المترابطة بين الوزارات، المستخدمين، الشكاوى، مسارات التحويل، والمشاريع.
* **تطبيقات الهواتف المحمولة (Mobile App):**
  * إطار العمل: Flutter بلغة Dart، لبرمجة تطبيق هجين موحد وعالي الأداء يعمل على نظامي Android و iOS، ويسهل التكامل مع الكاميرا ونظام تحديد المواقع (GPS).
* **لوحات التحكم الإدارية (Web Dashboards):**
  * قوالب Laravel Blade مدعومة بإطار Tailwind CSS لبناء واجهات إدارة سريعة الاستجابة ومناسبة لكافة الشاشات.
* **خدمات نظم المعلومات الجغرافية (GIS):**
  * مكتبة Leaflet أو واجهة Google Maps API لعرض وإدارة الخرائط والتثبيت الجغرافي.

---

## المتطلبات غير الوظيفية ومعايير الموثوقية

* **موثوقية التدفق وضمان عدم الضياع:** ضمان عدم إهمال أو ضياع أي شكوى بفضل مسار التحويل البيني بدلاً من الرفض العشوائي.
* **نزاهة وصحة البيانات:** منع التلاعب الجغرافي بقفل إحداثيات الموقع وقت التصوير، ومطابقة موقع الموظف الميداني مع موقع البلاغ وقت إتمام المعالجة.
* **قابلية التوسع الهيكلي:** بناء قواعد البيانات والخدمات البرمجية بطريقة تتيح إضافة قطاعات ومؤسسات ووزارات جديدة دون الحاجة لإعادة هيكلة النظام البرمجي.

---
---

# A Smart National Portal for Public Complaints Management, Developmental Projects Support, and Geographic Tracking

---

## Table of Contents
* [Project Overview](#project-overview)
* [Vision & Strategic Objectives](#vision--strategic-objectives)
* [Target Roles & Access Control](#target-roles--access-control-rbac)
* [Functional Modules & Key Features](#functional-modules--key-features)
* [Technical Architecture & Stack](#technical-architecture--stack)
* [Non-Functional Requirements & System Reliability](#non-functional-requirements--system-reliability)

---

## Project Overview
The Smart National Portal is a comprehensive, centralized software ecosystem engineered to bridge communication between citizens and government bodies, including ministries and public authorities. The platform couples a cross-platform mobile application for citizens and field response teams with specialized, secure web dashboards for ministerial administrators and executive leadership.

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

## Technical Architecture & Stack

* **Backend & API Architecture:**
  * Framework: Laravel (PHP) for constructing enterprise-grade RESTful APIs, securing transactional workflows, and handling authentication.
* **Database Management System:**
  * MySQL relational database configured for data normalization across ministries, users, tickets, routing histories, and developmental initiatives.
* **Mobile Application Development:**
  * Framework: Flutter (Dart) delivering a unified, high-performance client application across Android and iOS with native hardware integration (Camera and Location Services).
* **Administrative Dashboards:**
  * Laravel Blade combined with Tailwind CSS for high-performance, modular, and responsive administrative web consoles.
* **Geospatial & Mapping Integration:**
  * Leaflet library or Google Maps API for coordinate plotting, geofencing, and map visualization.

---

## Non-Functional Requirements & System Reliability

* **Zero-Drop Guarantee:** Resilient routing and transfer mechanisms prevent reports from being discarded due to misclassification.
* **Data & Evidence Integrity:** Hardened location coordinates prevent spatial spoofing and require on-site proximity verification for ticket resolution.
* **Architectural Scalability:** Modular relational schema and service layers designed to accommodate new ministries, municipalities, and sectors without refactoring base code.

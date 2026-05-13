# Architecture Overview (Frontend + Backend)

## هل الـ Architecture الحالية مناسبة للمشروع؟

نعم، مناسبة جدًا كبداية لمشروع AI Multi-Tenant E-Commerce Platform، لأنها تفصل المسؤوليات بشكل واضح بين الواجهة (React) والخلفية (Laravel)، وتسمح بالتوسع بدون إعادة بناء كبيرة لاحقًا.

الهيكل الحالي ليس نهاية الطريق، لكنه Base قوية وقابلة للتطوير مع نمو المتطلبات.

---

## Frontend Structure

المسار: `frontend/src/`

- `components/`: مكونات UI قابلة لإعادة الاستخدام.
- `pages/`: صفحات التطبيق (Route-level views).
- `services/`: استدعاءات API والمنطق المرتبط بالاتصال مع الباك-إند.
- `hooks/`: Custom hooks لعزل منطق الحالة والسلوك المتكرر.
- `layouts/`: هياكل الصفحات (Dashboard layout, Auth layout, ...).
- `store/`: إدارة الحالة العامة (مثل Zustand/Redux لاحقًا).
- `types/`: أنواع TypeScript المشتركة.

### لماذا هذا مناسب؟

- يمنع تكدس كل شيء داخل `components` أو `pages`.
- يسهل تقسيم العمل بين الفريق (UI, API, state).
- يدعم التوسع لاحقًا (منتجات، طلبات، مستأجرين، صلاحيات) بدون فوضى.

---

## Backend Structure (Laravel)

المسار: `ecommerce-api/app/`

- `Services/`: منطق الأعمال الذي يجمع أكثر من خطوة أو قاعدة.
- `Domains/`: تنظيم كل نطاق أعمال (Tenant, Product, Order, Billing...).
- `Repositories/`: عزل الوصول إلى البيانات (Eloquent/Query logic).
- `Actions/`: عمليات استخدام محددة وواضحة (CreateOrder, AssignPlan, ...).

### لماذا هذا مناسب؟

- يقلل تضخم Controllers.
- يفصل Business Logic عن HTTP layer.
- يجعل الاختبار أسهل (Unit + Feature).
- مناسب جدًا لمشاريع Multi-Tenant التي تحتاج boundaries واضحة.

---

## لماذا اخترنا هذا التقسيم تحديدًا؟

في مشروع Multi-Tenant E-Commerce، التعقيد يزيد بسرعة بسبب:

- تعدد المستأجرين (tenants) وقواعد العزل بينهم.
- اختلاف الخطط والخصائص لكل مستأجر.
- عمليات التجارة (catalog, cart, checkout, orders, payments).
- لاحقًا: ذكاء اصطناعي للتوصية/التسعير/التحليلات.

هذا التقسيم يضمن أن إضافة هذه الطبقات لاحقًا تتم بشكل منظم بدل إعادة هيكلة كاملة.

---

## هل تحتاج تحسين لاحقًا؟

نعم، مع تقدم المشروع يُفضل تطوير الـ Architecture بهذا الشكل:

1. داخل `Domains/` إنشاء Modules واضحة مثل:
   - `Tenant/`, `Catalog/`, `Inventory/`, `Order/`, `Payment/`, `AI/`.
2. اعتماد DTO/Request validation strategy ثابتة بين Actions وServices.
3. توحيد Error handling و API response format.
4. إضافة Testing structure مرتب لكل Domain.
5. إضافة Authorization boundaries واضحة لكل tenant.

---

## خلاصة

الهيكل الحالي مناسب للمشروع وعملي جدًا كبداية، خصوصًا أنه يضع أساسًا نظيفًا للتوسع. القرار صحيح في هذه المرحلة.

عند بدء بناء الميزات الفعلية، الأفضل الانتقال تدريجيًا إلى تنظيم Domain-based أعمق داخل Laravel، مع الحفاظ على فصل الواجهة في React كما هو.
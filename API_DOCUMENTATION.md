# 📱 pharmacyLink API — توثيق لمطور تطبيق Flutter

> الواجهة الخلفية (Backend) لتطبيق **pharmacyLink** (نظام إدارة صيدليات).
> جميع الاستجابات بصيغة **JSON**. جميع البيانات النصية العربية تُرجَّع كما هي (UTF-8).

---

## 1) 🔌 الرابط الأساسي (Base URL)

| البيئة | الرابط |
|---|---|
| **الإنتاج (Production)** | `https://YOUR-DOMAIN.com/api` |
| **محلي (Local)** | `http://127.0.0.1:8000/api` |

> استبدل `YOUR-DOMAIN.com` بالنطاق الفعلي للسيرفر. كل النقاط أدناه تُكتب بعد `/api`.

---

## 2) 🔐 المصادقة (Authentication)

### تسجيل الدخول
```
POST /api/auth/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "secret"
}
```

**الاستجابة الناجحة (200):**
```json
{
  "status": true,
  "message": "تم تسجيل الدخول بنجاح",
  "token": "7pFNLvUBmONnhaxloo36IKJMU06RPKjEim8ihWUvNcTMlPFVTVtmjk6ed4ZF",
  "token_type": "Bearer",
  "user": {
    "id": 46,
    "name": "API Test",
    "email": "apitest@test.com",
    "role": "admin",             // admin | pharmacist | customer
    "avatar": null,
    "is_active": true,
    "pharmacist": null,          // يظهر فقط لصف الصيدلاني
    "pharmacy": null             // يظهر فقط لصف الصيدلاني
  }
}
```

### إرسال التوكن في الطلبات المحمية
أرسل الهيدر التالي في **كل** طلب يتطلب مصادقة:
```
Authorization: Bearer <token>
```

### الأخطاء الشائعة
| الحالة | المعنى |
|---|---|
| `401` | بيانات الدخول خاطئة |
| `403` | الحساب موقوف، أو الدور غير مسموح له بالنقطة |
| `422` | بيانات الطلب غير صالحة (validation) — الرسالة فيها تفاصيل الحقول |

> ⚠️ **مهم للمطور:** بدون هيدر التوكن (أو بانتهائه) تُرجَّع استجابة `401`؛ وعند مسح التوكن (Logout) يُلغى فوراً.

---

## 3) 📦 شكل الاستجابة الموحّد
```json
{
  "status": true,        // true/false
  "message": "...",      // رسالة عربية (اختياري في بعض النقاط)
  "data": {}             // البيانات الفعلية (كائن، مصفوفة، أو pagination)
}
```

**Pagination:** نقاط القوائم (مثل `GET /medicines`) تُرجِّع:
```json
{
  "current_page": 1,
  "data": [ ... ],
  "total": 50,
  "per_page": 15,
  "last_page": 4
}
```
التحكم بعدد النتائج عبر `?per_page=`.

---

## 4) 🧭 نظرة عامة على النقاط حسب الدور

| المجموعة | البادئة |
|---|---|
| عام (بدون مصادقة) | `/api/...` |
| أي دور مسجّل | `/api/me` , `/api/notifications` , `/api/auth/logout` |
| المستخدم (عميل) | `/api/customer/...` |
| الصيدلاني | `/api/pharmacist/...` |
---

## 5) 🌍 نقاط عامة (بدون مصادقة)

### 5.1 الفحص — `GET /api/test`
```json
{ "status": true, "message": "pharmacyLink API يعمل بنجاح" }
```

### 5.2 إنشاء حساب عميل — `POST /api/auth/register/user`
```json
{
  "name": "أحمد محمد",
  "email": "ahmad@mail.com",
  "password": "123456",
  "password_confirmation": "123456"
}
```
> يُرسَل **كود OTP** على البريد. تفعيل الحساب:

```json
// POST /api/auth/verify-email
{ "email": "ahmad@mail.com", "code": "123456" }

// POST /api/auth/resend-verification-code
{ "email": "ahmad@mail.com" }
```

### 5.3 إنشاء حساب صيدلاني — `POST /api/auth/register/pharmacist`
`multipart/form-data`:
| الحقل | النوع |
|---|---|
| name, email, password, password_confirmation | text |
| national_id, syndicate_number, license_number | text |
| graduation_university | text |
| graduation_year | text (YYYY) |
| certificate_file, syndicate_file, license_file | **ملفات** jpg/pdf |
| notes | text (اختياري) |

> الحساب يُنشأ بحالة **pending** حتى يعتمده المدير.

### 5.4 البحث عن الأدوية — `GET /api/medicines`
```
GET /api/medicines?q=باراسيتامول&category_id=2&per_page=15
```
رجعات: قائمة أدوية نشطة مع `manufacturer` , `category` , `dosageForm` , `activeIngredients`.

### 5.5 تفاصيل دواء — `GET /api/medicines/{id}`

### 5.6 قوائم الكتالوج — `GET`
- `/api/categories`
- `/api/dosage-forms`
- `/api/manufacturers`
- `/api/active-ingredients`

### 5.7 قائمة الصيدليات — `GET /api/pharmacies?q=`
### 5.8 تفاصيل صيدلية + توفر الأدوية — `GET /api/pharmacies/{id}`
```json
{
  "status": true,
  "data": {
    "pharmacy": { ... },
    "available_items": [
      { "medicine": { ... }, "quantity": 20, "price": 5.50 }
    ]
  }
}
```

---

## 6) 🧑‍💼 نقاط أي مستخدم مسجّل (token مطلوب)

| الطريقة | النقطة | الوصف |
|---|---|---|
| GET | `/api/me` | بيانات المستخدم الحالي |
| POST | `/api/auth/logout` | إبطال التوكن |
| GET | `/api/notifications?per_page=` | قائمة الإشعارات |
| POST | `/api/notifications/{id}/read` | تعليم إشعار كمقروء |
| POST | `/api/notifications/read-all` | تعليم الكل كمقروء |

---

## 7) 🛒 نقاط العميل (`/api/customer/...`)

| الطريقة | النقطة | الوصف |
|---|---|---|
| GET | `/api/customer/dashboard` | إحصائيات: عدد الجرعات النشطة، الطلبات، أحدث 3 طلبات |
| GET | `/api/customer/doses` | جرعاتي |
| POST | `/api/customer/doses` | إضافة جرعة |
| PUT | `/api/customer/doses/{id}` | تعديل |
| DELETE | `/api/customer/doses/{id}` | حذف |
| GET | `/api/customer/orders` | طلباتي |
| POST | `/api/customer/orders` | إنشاء طلب |

### ⏰ إضافة/تعديل جرعة
```json
{
  "name_ar": "بنادول",
  "name_en": "Panadol",
  "dosage": "500mg",
  "times": ["08:00", "14:00", "22:00"],
  "until": "2026-12-31",
  "notes": "بعد الأكل"
}
```

### 🛍️ إنشاء طلب
```json
{
  "pharmacy_id": 1,
  "delivery_address": "رام الله - وسط البلد",
  "phone": "0599999999",
  "notes": "يرجى الاتصال عند الوصول",
  "items": [
    { "medicine_id": 5, "qty": 2 },
    { "medicine_id": 9, "qty": 1 }
  ]
}
```
> يُفحص التوفر ويُحسب المجموع تلقائياً. إن كانت الكمية أكبر من المتوفر ← `422` برسالة عربية.
| المدير | `/api/admin/...` |
---

## 8) 💊 نقاط الصيدلاني (`/api/pharmacist/...`) — يتطلب اعتماداً وصيدلية مرتبطة
> إذا لم يكن الصيدلاني معتمداً أو لا يملك صيدلية ← استجابة `403` برسالة عربية.

| الطريقة | النقطة | الوصف |
|---|---|---|
| GET | `/api/pharmacist/dashboard` | إحصائيات صيدليتي + تنبيهات المخزون المنخفض |
| GET | `/api/pharmacist/purchases` | فواتير الشراء |
| POST | `/api/pharmacist/purchases` | تسجيل فاتورة شراء (يدخل الدُفعات للمخزون) |
| GET | `/api/pharmacist/batches` | دُفعات المخزون |
| GET | `/api/pharmacist/sales` | فواتير البيع |
| POST | `/api/pharmacist/sales` | تسجيل فاتورة بيع (ينقص المخزون FIFO) |
| GET | `/api/pharmacist/sale-payments` | دفعات المبيعات |
| POST | `/api/pharmacist/sale-payments` | إضافة دفعة |
| GET | `/api/pharmacist/medicine-requests` | طلباتي من الأدوية |
| POST | `/api/pharmacist/medicine-requests` | اقتراح دواء جديد |
| GET | `/api/pharmacist/pharmacy-requests` | طلبي لصيدلية |
| POST | `/api/pharmacist/pharmacy-requests` | تسجيل صيدلية |
| GET | `/api/pharmacist/orders` | طلبات العملاء على صيدليتي |
| PUT | `/api/pharmacist/orders/{id}/status` | تحديث حالة الطلب |

### 📦 تسجيل فاتورة شراء — `POST /api/pharmacist/purchases`
```json
{
  "invoice_number": "INV-2026-001",
  "supplier_name": "شركة الأدوية الحديثة",
  "purchase_date": "2026-08-12",
  "notes": "شحنة شهر آب",
  "medicine_id": [5, 9],
  "quantity": [100, 50],
  "purchase_price": [3.00, 4.00],
  "selling_price": [5.50, 7.00],
  "expiry_date": ["2028-01-01", "2027-06-01"],
  "lot_number": ["LOT-A", null],
  "manufacturing_date": ["2025-08-01", null]
}
```
> نفس الدواء+نفس السعر+نفس تاريخ الانتهاء → **يُدمج** في دفعة موجودة تلقائياً.

### 🧾 تسجيل فاتورة بيع — `POST /api/pharmacist/sales`
```json
{
  "invoice_number": 10025,
  "discount": 2.00,
  "tax": 0.00,
  "payment_method": "cash",       // cash | bank | palpay
  "status": "complete",           // complete | cancelled
  "notes": null,
  "medicine_id": [5],
  "quantity": [3]
}
```
> يُخصم من **أقرب دفعة انتهاء أولاً (FIFO)**، ويُرسَل تنبيه Low-Stock تلقائياً إن انخفض المخزون.

### 💳 إضافة دفعة — `POST /api/pharmacist/sale-payments`
```json
{
  "sale_id": 12,
  "payment_method": "bank_transfer",
  "transaction_id": "bank_transfer",     // bank_transfer | palbay | jawalbay
  "reference_number": "REF123",
  "sender_name": "شركة التأمين",
  "payment_date": "2026-08-12",
  "notes": null,
  "receipt_image": "<file>"              // اختياري
}
```

### 📝 اقتراح دواء جديد — `POST /api/pharmacist/medicine-requests`
```json
{
  "brand_name_en": "Paracare",
  "brand_name_ar": "باراكير",
  "manufacturer": 1,
  "category": 2,
  "dosage_form": 3,
  "reference_price": 6.00,
  "barcode": null,
  "requires_prescription": true,
  "active_ingredient": [4],
  "strength_value": [500],
  "strength_unit": ["mg"]
}
```

### 🏥 تسجيل صيدلية — `POST /api/pharmacist/pharmacy-requests`
`multipart/form-data` مع: `name_ar`, `name_en`, `phone`, `address`, `logo` (اختياري), `license_document` (اختياري).

### 🔄 تحديث حالة طلب عميل — `PUT /api/pharmacist/orders/{id}/status`
```json
{ "status": "confirmed" }
```
**مسار الحالات المسموح (يرفض القفزات غير القانونية):**
`pending → confirmed → processing → ready → delivered`
(يمكن الإلغاء `cancelled` من pending/confirmed/processing).

---

## 9) 🏢 نقاط المدير (`/api/admin/...`)

| الطريقة | النقطة | الوصف |
|---|---|---|
| GET | `/api/admin/dashboard` | إحصائيات شاملة |
| GET/POST | `/api/admin/medicines` | قائمة/إضافة دواء |
| PUT/DELETE | `/api/admin/medicines/{id}` | تعديل/حذف |
| POST/PUT/DELETE | `/api/admin/categories` , `/api/admin/dosage-forms` , `/api/admin/manufacturers` , `/api/admin/active-ingredients` | إدارة الكتالوج |
| GET | `/api/admin/medicine-requests?status=` | طلبات الأدوية |
| POST | `/api/admin/medicine-requests/{id}/approve` | اعتماد → ينشئ الدواء |
| POST | `/api/admin/medicine-requests/{id}/reject` | رفض (مع `admin_notes`) |
| GET | `/api/admin/pharmacy-requests?status=` | طلبات الصيدليات |
| POST | `/api/admin/pharmacy-requests/{id}/approve` | اعتماد → ينشئ/يعيّن الصيدلية |
| POST | `/api/admin/pharmacy-requests/{id}/reject` | رفض (مع `admin_notes`) |

### ➕ إضافة دواء — `POST /api/admin/medicines`
```json
{
  "brand_name_en": "Panadol Extra",
  "brand_name_ar": "بنادول إكسترا",
  "manufacturer_id": 1,
  "category_id": 2,
  "dosage_form_id": 3,
  "reference_price": 5.00,
  "barcode": "6291041500213",
  "requires_prescription": false,
  "is_active": true,
  "active_ingredient": [4, 5],
  "strength_value": [500, 65],
  "strength_unit": ["mg", "mg"]
}
```

### ✅ اعتماد طلب دواء — `POST /api/admin/medicine-requests/{id}/approve`
يُرسَل **بنفس حقول** الاقتراح أعلاه (يقبل المدير التعديل قبل الاعتماد).

### ✅ اعتماد طلب صيدلية — `POST /api/admin/pharmacy-requests/{id}/approve`
```json
{
  "name_ar": "صيدلية الأمل",
  "name_en": "Al-Amal Pharmacy",
  "phone": "0591111111",
  "address": "نابلس",
  "latitude": 32.221111,
  "longitude": 35.254444,
  "opening_time": "08:00",
  "closing_time": "22:00",
  "status": "opne",             // opne | closed | suspended
  "is_verified": true
}
```
> يُنشئ الصيدلية ويربطها بالصيدلاني ويسجّل **إسناد** (assignment).

---

## 10) 🔧 توصيات لمطور Flutter

1. **احفظ التوكن** بأمان: `shared_preferences` أو `flutter_secure_storage`، وأرفقه لكل طلب:
   ```dart
   headers: {'Authorization': 'Bearer $token'}
   ```
2. عالج `401` بإعادة التوجيه لشاشة تسجيل الدخول.
3. اعرض `message` العربي من كل استجابة مباشرة (SnackBar).
4. عند إرسال ملفات (تسجيل صيدلاني/دفعة) استخدم `http.MultipartRequest` مع أسماء الحقول أعلاه.
5. **المسارات حسب الدور:** الجمل login → `user.role` يخبرك بأي واجهة (تصفح الكتالوج / لوحة الصيدلاني / لوحة الإدارة).
6. حالة `422` تعني Validation — اعرض `errors` تحت كل حقل.

> 📄 هذه الوثيقة كاملة في: `API_DOCUMENTATION.md` داخل المشروع.

<?php

// مولّد مجموعة Postman لتطبيق pharmacyLink

$API = '{{baseUrl}}';

function req($method, $url, $body = null, $raw = true)
{
    $r = ['method' => $method, 'header' => [], 'url' => $url];
    if ($body !== null) {
        $r['body'] = $raw
            ? ['mode' => 'raw', 'raw' => $body, 'options' => ['raw' => ['language' => 'json']]]
            : ['mode' => 'formdata', 'formdata' => $body];
    }
    return $r;
}

function item($name, $request)
{
    return ['name' => $name, 'request' => $request];
}

// ---------- المصادقة ----------
$authLogin = [
    'listen' => 'test',
    'script' => [
        'type' => 'text/javascript',
        'exec' => [
            "const res = pm.response.json();",
            "if (res && res.token) { pm.collectionVariables.set('token', res.token); console.log('Token saved ✔'); }",
        ],
    ],
];

$pharmacistForm = [
    ['key' => 'name', 'type' => 'text', 'value' => 'صيدلاني تجريبي'],
    ['key' => 'email', 'type' => 'text', 'value' => 'pharmacist@mail.com'],
    ['key' => 'password', 'type' => 'text', 'value' => '123456'],
    ['key' => 'password_confirmation', 'type' => 'text', 'value' => '123456'],
    ['key' => 'national_id', 'type' => 'text', 'value' => '123456789'],
    ['key' => 'syndicate_number', 'type' => 'text', 'value' => 'SYN001'],
    ['key' => 'license_number', 'type' => 'text', 'value' => 'LIC001'],
    ['key' => 'graduation_university', 'type' => 'text', 'value' => 'جامعة النجاح'],
    ['key' => 'graduation_year', 'type' => 'text', 'value' => '2015'],
    ['key' => 'certificate_file', 'type' => 'file', 'src' => null],
    ['key' => 'syndicate_file', 'type' => 'file', 'src' => null],
    ['key' => 'license_file', 'type' => 'file', 'src' => null],
];

$loginReq = req('POST', "$API/auth/login", "{\n  \"email\": \"apitest@test.com\",\n  \"password\": \"secret123\"\n}");
$loginReq['event'] = [$authLogin];

$authFolder = [
    'name' => '1. المصادقة (Auth)',
    'item' => [
        array_merge(['name' => 'تسجيل الدخول + حفظ التوكن تلقائياً'], ['request' => $loginReq], ['event' => $loginReq['event']]),
        item('إنشاء حساب عميل (register/user)', req('POST', "$API/auth/register/user", "{\n  \"name\": \"أحمد محمد\",\n  \"email\": \"ahmad@mail.com\",\n  \"password\": \"123456\",\n  \"password_confirmation\": \"123456\"\n}")),
        item('إنشاء حساب صيدلاني (register/pharmacist)', req('POST', "$API/auth/register/pharmacist", $pharmacistForm, false)),
        item('تفعيل البريد OTP (verify-email)', req('POST', "$API/auth/verify-email", "{\n  \"email\": \"ahmad@mail.com\",\n  \"code\": \"123456\"\n}")),
        item('إعادة إرسال كود OTP', req('POST', "$API/auth/resend-verification-code", "{\n  \"email\": \"ahmad@mail.com\"\n}")),
        item('بياناتي (me)', req('GET', "$API/me")),
        item('تسجيل الخروج (logout)', req('POST', "$API/auth/logout")),
    ],
];

// ---------- عام / بدون مصادقة ----------
$publicFolder = [
    'name' => '2. عام (كتالوج / بدون مصادقة)',
    'item' => [
        item('فحص الاتصال (test)', req('GET', "$API/test")),
        item('البحث عن الأدوية (q / category_id / per_page)', req('GET', "$API/medicines?q=&category_id=&per_page=15")),
        item('تفاصيل دواء', req('GET', "$API/medicines/1")),
        item('التصنيفات (categories)', req('GET', "$API/categories")),
        item('أشكال الجرعات (dosage-forms)', req('GET', "$API/dosage-forms")),
        item('الشركات المصنّعة (manufacturers)', req('GET', "$API/manufacturers")),
        item('المواد الفعّالة (active-ingredients)', req('GET', "$API/active-ingredients")),
        item('قائمة الصيدليات (pharmacies?q=)', req('GET', "$API/pharmacies?q=")),
        item('تفاصيل صيدلية + توفر الأدوية', req('GET', "$API/pharmacies/1")),
    ],
];
echo "base built\n";
// ---------- العميل ----------
$customerFolder = [
    'name' => '3. العميل (customer)',
    'item' => [
        item('لوحة التحكم (dashboard)', req('GET', "$API/customer/dashboard")),
        item('قائمة جرعاتي (doses)', req('GET', "$API/customer/doses")),
        item('إضافة جرعة (doses)', req('POST', "$API/customer/doses", "{\n  \"name_ar\": \"بنادول\",\n  \"name_en\": \"Panadol\",\n  \"dosage\": \"500mg\",\n  \"times\": [\"08:00\", \"22:00\"],\n  \"until\": \"2026-12-31\",\n  \"notes\": \"بعد الأكل\"\n}")),
        item('تعديل جرعة (doses/{id})', req('PUT', "$API/customer/doses/1", "{\n  \"name_ar\": \"بنادول إكسترا\",\n  \"name_en\": \"Panadol Extra\",\n  \"dosage\": \"650mg\",\n  \"times\": [\"08:00\", \"22:00\"],\n  \"active\": true\n}")),
        item('حذف جرعة (doses/{id})', req('DELETE', "$API/customer/doses/1")),
        item('قائمة طلباتي (orders)', req('GET', "$API/customer/orders")),
        item('إنشاء طلب (orders)', req('POST', "$API/customer/orders", "{\n  \"pharmacy_id\": 1,\n  \"delivery_address\": \"رام الله - وسط البلد\",\n  \"phone\": \"0599999999\",\n  \"notes\": \"يرجى الاتصال عند الوصول\",\n  \"items\": [\n    { \"medicine_id\": 5, \"qty\": 2 },\n    { \"medicine_id\": 9, \"qty\": 1 }\n  ]\n}")),
    ],
];

// ---------- الصيدلاني ----------
$purchaseBody = "{\n  \"invoice_number\": \"INV-2026-001\",\n  \"supplier_name\": \"شركة الأدوية الحديثة\",\n  \"purchase_date\": \"2026-08-12\",\n  \"notes\": \"شحنة شهر آب\",\n  \"medicine_id\": [5, 9],\n  \"quantity\": [100, 50],\n  \"purchase_price\": [3.00, 4.00],\n  \"selling_price\": [5.50, 7.00],\n  \"expiry_date\": [\"2028-01-01\", \"2027-06-01\"],\n  \"lot_number\": [\"LOT-A\", null],\n  \"manufacturing_date\": [\"2025-08-01\", null]\n}";

$saleBody = "{\n  \"invoice_number\": 10025,\n  \"discount\": 2.00,\n  \"tax\": 0.00,\n  \"payment_method\": \"cash\",\n  \"status\": \"complete\",\n  \"notes\": null,\n  \"medicine_id\": [5],\n  \"quantity\": [3]\n}";

$pharmacistFolder = [
    'name' => '4. الصيدلاني (pharmacist)',
    'item' => [
        item('لوحة التحكم (dashboard)', req('GET', "$API/pharmacist/dashboard")),
        item('فواتير الشراء (purchases)', req('GET', "$API/pharmacist/purchases")),
        item('تسجيل فاتورة شراء (purchases)', req('POST', "$API/pharmacist/purchases", $purchaseBody)),
        item('دُفعات المخزون (batches)', req('GET', "$API/pharmacist/batches")),
        item('فواتير البيع (sales)', req('GET', "$API/pharmacist/sales")),
        item('تسجيل فاتورة بيع (sales)', req('POST', "$API/pharmacist/sales", $saleBody)),
        item('دفعات المبيعات (sale-payments)', req('GET', "$API/pharmacist/sale-payments")),
        item('إضافة دفعة (sale-payments)', req('POST', "$API/pharmacist/sale-payments", "{\n  \"sale_id\": 12,\n  \"payment_method\": \"bank_transfer\",\n  \"transaction_id\": \"bank_transfer\",\n  \"reference_number\": \"REF123\",\n  \"sender_name\": \"شركة التأمين\",\n  \"payment_date\": \"2026-08-12\",\n  \"notes\": null\n}")),
        item('طلبات الأدوية التي أرسلتها (medicine-requests)', req('GET', "$API/pharmacist/medicine-requests")),
        item('اقتراح دواء جديد (medicine-requests)', req('POST', "$API/pharmacist/medicine-requests", "{\n  \"brand_name_en\": \"Paracare\",\n  \"brand_name_ar\": \"باراكير\",\n  \"manufacturer\": 1,\n  \"category\": 2,\n  \"dosage_form\": 3,\n  \"reference_price\": 6.00,\n  \"barcode\": null,\n  \"requires_prescription\": true,\n  \"active_ingredient\": [4],\n  \"strength_value\": [500],\n  \"strength_unit\": [\"mg\"]\n}")),
        item('طلب صيدليتي (pharmacy-requests)', req('GET', "$API/pharmacist/pharmacy-requests")),
        item('تسجيل صيدلية (pharmacy-requests)', req('POST', "$API/pharmacist/pharmacy-requests", [['key' => 'name_ar', 'type' => 'text', 'value' => 'صيدلية الأمل'], ['key' => 'name_en', 'type' => 'text', 'value' => 'Al-Amal Pharmacy'], ['key' => 'phone', 'type' => 'text', 'value' => '0591111111'], ['key' => 'address', 'type' => 'text', 'value' => 'نابلس'], ['key' => 'logo', 'type' => 'file', 'src' => null]], false)),
        item('طلبات العملاء (orders)', req('GET', "$API/pharmacist/orders")),
        item('تحديث حالة طلب (orders/{id}/status)', req('PUT', "$API/pharmacist/orders/1/status", "{\n  \"status\": \"confirmed\"\n}")),
    ],
];
echo "customer+pharmacist built\n";
// ---------- المدير ----------
$adminFolder = [
    'name' => '5. المدير (admin)',
    'item' => [
        item('لوحة التحكم (dashboard)', req('GET', "$API/admin/dashboard")),
        item('كل الأدوية (medicines)', req('GET', "$API/admin/medicines")),
        item('إضافة دواء (medicines)', req('POST', "$API/admin/medicines", "{\n  \"brand_name_en\": \"Panadol Extra\",\n  \"brand_name_ar\": \"بنادول إكسترا\",\n  \"manufacturer_id\": 1,\n  \"category_id\": 2,\n  \"dosage_form_id\": 3,\n  \"reference_price\": 5.00,\n  \"barcode\": \"6291041500213\",\n  \"requires_prescription\": false,\n  \"is_active\": true,\n  \"active_ingredient\": [4],\n  \"strength_value\": [500],\n  \"strength_unit\": [\"mg\"]\n}")),
        item('تعديل دواء (medicines/{id})', req('PUT', "$API/admin/medicines/1", "{\n  \"reference_price\": 6.50,\n  \"is_active\": true\n}")),
        item('حذف دواء (medicines/{id})', req('DELETE', "$API/admin/medicines/1")),
        item('إضافة تصنيف (categories)', req('POST', "$API/admin/categories", "{\n  \"name_ar\": \"مسكنات\",\n  \"name_en\": \"Analgesics\",\n  \"is_active\": true\n}")),
        item('إضافة شكل جرعات (dosage-forms)', req('POST', "$API/admin/dosage-forms", "{\n  \"name_ar\": \"أقراص\",\n  \"name_en\": \"Tablets\",\n  \"is_active\": true\n}")),
        item('إضافة شركة (manufacturers)', req('POST', "$API/admin/manufacturers", "{\n  \"name_ar\": \"جلاكسو\",\n  \"name_en\": \"GSK\",\n  \"is_active\": true\n}")),
        item('إضافة مادة فعّالة (active-ingredients)', req('POST', "$API/admin/active-ingredients", "{\n  \"name_ar\": \"باراسيتامول\",\n  \"name_en\": \"Paracetamol\",\n  \"is_active\": true\n}")),
        item('طلبات الأدوية (medicine-requests?status=)', req('GET', "$API/admin/medicine-requests?status=pending")),
        item('اعتماد طلب دواء (approve)', req('POST', "$API/admin/medicine-requests/1/approve", "{\n  \"brand_name_en\": \"Paracare\",\n  \"brand_name_ar\": \"باراكير\",\n  \"manufacturer\": 1,\n  \"category\": 2,\n  \"dosage_form\": 3,\n  \"reference_price\": 6.00,\n  \"barcode\": null,\n  \"requires_prescription\": true,\n  \"active_ingredient\": [4],\n  \"strength_value\": [500],\n  \"strength_unit\": [\"mg\"]\n}")),
        item('رفض طلب دواء (reject)', req('POST', "$API/admin/medicine-requests/1/reject", "{\n  \"admin_notes\": \"البيانات غير مكتملة\"\n}")),
        item('طلبات الصيدليات (pharmacy-requests?status=)', req('GET', "$API/admin/pharmacy-requests?status=pending")),
        item('اعتماد طلب صيدلية (approve)', req('POST', "$API/admin/pharmacy-requests/1/approve", "{\n  \"name_ar\": \"صيدلية الأمل\",\n  \"name_en\": \"Al-Amal Pharmacy\",\n  \"phone\": \"0591111111\",\n  \"address\": \"نابلس\",\n  \"latitude\": 32.221111,\n  \"longitude\": 35.254444,\n  \"opening_time\": \"08:00\",\n  \"closing_time\": \"22:00\",\n  \"status\": \"opne\",\n  \"is_verified\": true\n}")),
        item('رفض طلب صيدلية (reject)', req('POST', "$API/admin/pharmacy-requests/1/reject", "{\n  \"admin_notes\": \"المستندات غير واضحة\"\n}")),
    ],
];

// ---------- الإشعارات (أي دور) ----------
$notificationsFolder = [
    'name' => '6. الإشعارات (Notifications)',
    'item' => [
        item('قائمة الإشعارات', req('GET', "$API/notifications?per_page=20")),
        item('تعليم إشعار كمقروء', req('POST', "$API/notifications/1/read")),
        item('تعليم الكل كمقروء', req('POST', "$API/notifications/read-all")),
    ],
];

// ---------- التجميع النهائي ----------
$collection = [
    'info' => [
        '_postman_id' => 'pharmacy-link-api-2026',
        'name' => 'pharmacyLink API',
        'description' => "مجموعة نقاط API لتطبيق pharmacyLink.\n\n1) عدّل متغير `baseUrl` إلى رابط سيرفرك (مثال: https://your-domain.com/api)\n2) نفّذ \"تسجيل الدخول\" أولاً — التوكن يُحفظ تلقائياً ويُرسل مع كل الطلبات المحمية.",
        'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
    ],
    'variable' => [
        ['key' => 'baseUrl', 'value' => 'http://127.0.0.1:8000/api'],
        ['key' => 'token', 'value' => ''],
    ],
    'auth' => [
        'type' => 'bearer',
        'bearer' => [['key' => 'token', 'value' => '{{token}}', 'type' => 'string']],
    ],
    'item' => [$authFolder, $publicFolder, $customerFolder, $pharmacistFolder, $adminFolder, $notificationsFolder],
];

file_put_contents(__DIR__ . '/pharmacyLink_API.postman_collection.json', json_encode($collection, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

echo "Collection generated ✔\n";

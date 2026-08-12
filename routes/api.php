<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BatchController;
use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DoseController;
use App\Http\Controllers\Api\MedicineRequestController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PharmacyController;
use App\Http\Controllers\Api\PharmacyRequestController;
use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\SalePaymentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — pharmacyLink (توكن عبر auth:api / عمود api_token)
|--------------------------------------------------------------------------
| المصادقة: أرسل `Authorization: Bearer <token>` أو `api_token` مع الطلب.
| كل مجموعة روابط محمية حسب الدور: customer / pharmacist / admin.
*/

// ==================== عام / بدون مصادقة ====================
Route::get('test', function () {
    return response()->json([
        'status' => true,
        'message' => 'pharmacyLink API يعمل بنجاح',
        'data' => [
            'app_name' => 'pharmacyLink',
            'version' => '1.0.0',
        ],
    ]);
});

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register/user', [AuthController::class, 'registerUser']);
    Route::post('register/pharmacist', [AuthController::class, 'registerPharmacist']);
    Route::post('verify-email', [AuthController::class, 'verifyEmail']);
    Route::post('resend-verification-code', [AuthController::class, 'resendOtp']);
});

// ==================== الكشّاف العام (بدون مصادقة) ====================
Route::get('medicines', [CatalogController::class, 'medicines']);
Route::get('medicines/{medicine}', [CatalogController::class, 'showMedicine']);
Route::get('categories', [CatalogController::class, 'categories']);
Route::get('dosage-forms', [CatalogController::class, 'dosageForms']);
Route::get('manufacturers', [CatalogController::class, 'manufacturers']);
Route::get('active-ingredients', [CatalogController::class, 'activeIngredients']);
Route::get('pharmacies', [PharmacyController::class, 'index']);
Route::get('pharmacies/{pharmacy}', [PharmacyController::class, 'show']);

// ==================== محمي (أي دور مسجّل دخوله) ====================
Route::middleware('auth:api')->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);

    Route::get('notifications', [NotificationController::class, 'index']);
    Route::post('notifications/{id}/read', [NotificationController::class, 'markRead']);
    Route::post('notifications/read-all', [NotificationController::class, 'markAllRead']);
});

// ==================== المستخدم (customer) ====================
Route::middleware(['auth:api', 'role:customer'])->prefix('customer')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'customer']);

    // الجرعات
    Route::get('doses', [DoseController::class, 'index']);
    Route::post('doses', [DoseController::class, 'store']);
    Route::put('doses/{dose}', [DoseController::class, 'update']);
    Route::delete('doses/{dose}', [DoseController::class, 'destroy']);

    // الطلبات
    Route::get('orders', [OrderController::class, 'index']);
    Route::post('orders', [OrderController::class, 'store']);
});

// ==================== الصيدلاني (pharmacist) ====================
Route::middleware(['auth:api', 'role:pharmacist', 'approved.pharmacy'])->prefix('pharmacist')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'pharmacist']);

    // المشتريات والمخزون
    Route::get('purchases', [PurchaseController::class, 'index']);
    Route::post('purchases', [PurchaseController::class, 'store']);
    Route::get('batches', [BatchController::class, 'index']);

    // المبيعات والدفعات
    Route::get('sales', [SaleController::class, 'index']);
    Route::post('sales', [SaleController::class, 'store']);
    Route::get('sale-payments', [SalePaymentController::class, 'index']);
    Route::post('sale-payments', [SalePaymentController::class, 'store']);

    // طلبات الأدوية والصيدليات
    Route::get('medicine-requests', [MedicineRequestController::class, 'index']);
    Route::post('medicine-requests', [MedicineRequestController::class, 'store']);
    Route::get('pharmacy-requests', [PharmacyRequestController::class, 'index']);
    Route::post('pharmacy-requests', [PharmacyRequestController::class, 'store']);

    // طلبات العملاء
    Route::get('orders', [OrderController::class, 'pharmacistIndex']);
    Route::put('orders/{order}/status', [OrderController::class, 'updateStatus']);
});

// ==================== المدير (admin) ====================
Route::middleware(['auth:api', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'admin']);

    // إدارة الأدوية
    Route::get('medicines', [CatalogController::class, 'adminMedicines']);
    Route::post('medicines', [CatalogController::class, 'storeMedicine']);
    Route::put('medicines/{medicine}', [CatalogController::class, 'updateMedicine']);
    Route::delete('medicines/{medicine}', [CatalogController::class, 'destroyMedicine']);

    // إدارة الكتالوج
    Route::post('categories', [CatalogController::class, 'storeCategory']);
    Route::put('categories/{category}', [CatalogController::class, 'updateCategory']);
    Route::delete('categories/{category}', [CatalogController::class, 'destroyCategory']);

    Route::post('dosage-forms', [CatalogController::class, 'storeDosageForm']);
    Route::put('dosage-forms/{dosageForm}', [CatalogController::class, 'updateDosageForm']);
    Route::delete('dosage-forms/{dosageForm}', [CatalogController::class, 'destroyDosageForm']);

    Route::post('manufacturers', [CatalogController::class, 'storeManufacturer']);
    Route::put('manufacturers/{manufacturer}', [CatalogController::class, 'updateManufacturer']);
    Route::delete('manufacturers/{manufacturer}', [CatalogController::class, 'destroyManufacturer']);

    Route::post('active-ingredients', [CatalogController::class, 'storeActiveIngredient']);
    Route::put('active-ingredients/{activeIngredient}', [CatalogController::class, 'updateActiveIngredient']);
    Route::delete('active-ingredients/{activeIngredient}', [CatalogController::class, 'destroyActiveIngredient']);

    // مراجعة طلبات الأدوية
    Route::get('medicine-requests', [MedicineRequestController::class, 'adminIndex']);
    Route::post('medicine-requests/{id}/approve', [MedicineRequestController::class, 'approve']);
    Route::post('medicine-requests/{id}/reject', [MedicineRequestController::class, 'reject']);

    // مراجعة طلبات الصيدليات
    Route::get('pharmacy-requests', [PharmacyRequestController::class, 'adminIndex']);
    Route::post('pharmacy-requests/{id}/approve', [PharmacyRequestController::class, 'approve']);
    Route::post('pharmacy-requests/{id}/reject', [PharmacyRequestController::class, 'reject']);
});

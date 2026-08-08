<?php

use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Catalog\ActiveIngredientController;
use App\Http\Controllers\Catalog\CategoryController;
use App\Http\Controllers\Catalog\DosageFormController;
use App\Http\Controllers\Catalog\ManufacturerController;
use App\Http\Controllers\Catalog\MedicineController;
use App\Http\Controllers\Catalog\MedicineRequestController;
use App\Http\Controllers\Manager\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Portal\AiController;
use App\Http\Controllers\Portal\CatalogController;
use App\Http\Controllers\Portal\DoseController;
use App\Http\Controllers\Portal\OrderController;
use App\Http\Controllers\Portal\PharmacistDashboardController;
use App\Http\Controllers\Portal\PharmacistOrderController;
use App\Http\Controllers\Portal\UserDashboardController;
use App\Http\Controllers\PurchasingInventory\BatchController;
use App\Http\Controllers\PurchasingInventory\PurchaseController;
use App\Http\Controllers\SalesManagement\ReportController;
use App\Http\Controllers\SalesManagement\SaleController;
use App\Http\Controllers\SalesManagement\SalePaymentController;
use App\Http\Controllers\UsersPharmacies\PharmacistController;
use App\Http\Controllers\UsersPharmacies\PharmacyController;
use App\Http\Controllers\UsersPharmacies\PharmacyRequestController;
use App\Http\Controllers\UsersPharmacies\UserController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;


//=============landing / auth============
Route::get('/', function () {
    return view('welcome');
});
Route::get('/testDB', function () {
    try {
        $dbName = DB::connection()->getDatabaseName();
        return "Connected successfully to database: " . $dbName;
    } catch (\Exception $e) {
        return "Connection failed: " . $e->getMessage();
    }
});

Route::get('/phar', function () {
    return view('login');
})->name('login');

Route::get('login/manager', [LoginController::class, 'showManagerLogin'])->name('login.manager');
Route::post('login/manager', [LoginController::class, 'managerLogin'])->name('login.manager.submit');

Route::get('login/pharmacist', [LoginController::class, 'showPharmacistLogin'])->name('login.pharmacist');
Route::post('login/pharmacist', [LoginController::class, 'pharmacistLogin'])->name('login.pharmacist.submit');

Route::get('login/user', [LoginController::class, 'showUserLogin'])->name('login.user');
Route::post('login/user', [LoginController::class, 'userLogin'])->name('login.user.submit');

Route::post('logout', [LoginController::class, 'logout'])->name('logout')->middleware(['auth', 'approved.pharmacy', 'prevent-back-history']);

//=============notifications (any authenticated role)============
Route::middleware(['auth'])->group(function () {
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.markRead');
    Route::post('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.markAllRead');
});

//=============registration============
Route::get('register/user', [RegisterController::class, 'showUserForm'])->name('register.user');
Route::post('register/user', [RegisterController::class, 'registerUser'])->name('register.user.submit');

Route::get('register/pharmacist', [RegisterController::class, 'showPharmacistForm'])->name('register.pharmacist');
Route::post('register/pharmacist', [RegisterController::class, 'registerPharmacist'])->name('register.pharmacist.submit');

//=============email verification (OTP)============
Route::get('verify-email', [EmailVerificationController::class, 'page'])->name('verification.page');

Route::post('verify-email', [EmailVerificationController::class, 'verify'])
    ->middleware('throttle:otp-verify')
    ->name('verification.verify');

Route::post('resend-verification-code', [EmailVerificationController::class, 'resend'])
    ->middleware('throttle:otp-resend')
    ->name('verification.resend');

Route::get('/nav', function () {
    return view('layouts.nav_admin');
});

Route::get('/master', function () {
    return view('masterpage');
});

//=============pharmacist portal============
Route::middleware(['auth', 'role:pharmacist'])->group(function () {
    Route::get('pharmacist/dashboard', [PharmacistDashboardController::class, 'index'])->name('pharmacist.dashboard');

    //-------- pharmacy registration request (propose new or join vacant; admin approval creates/reassigns the real Pharmacy) --------
    Route::get('pharmacy-request', [PharmacyRequestController::class, 'index'])->name('pharmacy_request.index');
    Route::get('pharmacy-request/create', [PharmacyRequestController::class, 'create'])->name('pharmacy_request.create');
    Route::post('pharmacy-request', [PharmacyRequestController::class, 'store'])->name('pharmacy_request.store');
    Route::post('pharmacy-request/leave', [PharmacyRequestController::class, 'leave'])->name('pharmacy_request.leave');
});

//=============user portal============
Route::middleware(['auth', 'role:customer'])->group(function () {
    Route::get('user/dashboard', [UserDashboardController::class, 'index'])->name('user.dashboard');
    Route::get('user/search', [CatalogController::class, 'index'])->name('user.search');
    Route::get('user/pharmacy/{pharmacy}', [CatalogController::class, 'show'])->name('user.pharmacy.show');

    //-------- prescription scanning + AI assistant --------
    Route::get('user/scanner', [AiController::class, 'scannerPage'])->name('user.scanner');
    Route::get('user/assistant', [AiController::class, 'assistantPage'])->name('user.assistant');

    //-------- my dosages --------
    Route::get('user/doses', [DoseController::class, 'page'])->name('user.doses');

    //-------- my orders / checkout --------
    Route::get('user/checkout', [OrderController::class, 'checkoutPage'])->name('user.checkout');
    Route::get('user/orders', [OrderController::class, 'page'])->name('user.orders');

    //-------- JSON endpoints backing the pages above (still session-auth + CSRF, not a separate API stack) --------
    Route::get('api/user/doses', [DoseController::class, 'index'])->name('api.doses.index');
    Route::post('api/user/doses', [DoseController::class, 'store'])->name('api.doses.store');
    Route::put('api/user/doses/{dose}', [DoseController::class, 'update'])->name('api.doses.update');
    Route::delete('api/user/doses/{dose}', [DoseController::class, 'destroy'])->name('api.doses.destroy');

    Route::get('api/orders', [OrderController::class, 'index'])->name('api.orders.index');
    Route::post('api/orders', [OrderController::class, 'store'])->name('api.orders.store');

    Route::post('api/ai/chat', [AiController::class, 'chat'])->name('api.ai.chat');
    Route::post('api/ai/scan-prescription', [AiController::class, 'scanPrescription'])->name('api.ai.scan');
});

//=============manager portal (admin)============
Route::middleware(['auth', 'role:admin'])->group(function () {

    Route::get('admin/dash', [DashboardController::class, 'index'])->name('admin.dash');

    //-------- manufacturers --------
    Route::get('admin/manufacturer', [ManufacturerController::class, 'index'])->name('admin.manufacturer');
    Route::get('admin/manufacturer/create', [ManufacturerController::class, 'create'])->name('admin.manufacturer.create');
    Route::post('admin/manufacturer', [ManufacturerController::class, 'store'])->name('admin.manufacturer.store');
    Route::get('admin/manufacturer/{id}/edit', [ManufacturerController::class, 'edit'])->name('admin.manufacturer.edit');
    Route::put('admin/manufacturer/{id}', [ManufacturerController::class, 'update'])->name('admin.manufacturer.update');
    Route::delete('admin/manufacturer/{id}', [ManufacturerController::class, 'delete'])->name('manufacturer.delete');
    Route::get('admin/manufacturer/trash', [ManufacturerController::class, 'trash'])->name('admin.manufacturer.trash');
    Route::put('admin/manufacturer/{id}/restore', [ManufacturerController::class, 'restore'])->name('admin.manufacturer.restore');
    Route::delete('admin/manufacturer/{id}/force-delete', [ManufacturerController::class, 'forceDelete'])->name('admin.manufacturer.force-delete');

    //-------- category --------
    Route::get('admin/category', [CategoryController::class, 'index'])->name('category.index');
    Route::get('admin/category/create', [CategoryController::class, 'create'])->name('category.create');
    Route::post('admin/category', [CategoryController::class, 'store'])->name('category.store');
    Route::get('admin/category/{id}/edit', [CategoryController::class, 'edit'])->name('category.edit');
    Route::put('admin/category/{id}', [CategoryController::class, 'update'])->name('category.update');
    Route::delete('admin/category/{id}', [CategoryController::class, 'destroy'])->name('admin.catgory.destroy');
    Route::get('admin/category/trash', [CategoryController::class, 'trash'])->name('category.trash');
    Route::put('admin/category/{id}/restore', [CategoryController::class, 'restore'])->name('category.restore');
    Route::delete('admin/category/{id}/force-delete', [CategoryController::class, 'forceDelete'])->name('category.force-delete');

    //-------- dosage forms --------
    Route::get('admin/dosage-form', [DosageFormController::class, 'index'])->name('dosage_form.index');
    Route::get('admin/dosage-form/create', [DosageFormController::class, 'create'])->name('dosage_form.create');
    Route::post('admin/dosage-form', [DosageFormController::class, 'store'])->name('dosage_form.store');
    Route::get('admin/dosage-form/{id}/edit', [DosageFormController::class, 'edit'])->name('dosage_form.edit');
    Route::put('admin/dosage-form/{id}', [DosageFormController::class, 'update'])->name('dosage_form.update');
    Route::delete('admin/dosage-form/{id}', [DosageFormController::class, 'destroy'])->name('dosage_form.destroy');
    Route::get('admin/dosage-form/trash', [DosageFormController::class, 'trash'])->name('dosage_form.trash');
    Route::put('admin/dosage-form/{id}/restore', [DosageFormController::class, 'restore'])->name('dosage_form.restore');
    Route::delete('admin/dosage-form/{id}/force-delete', [DosageFormController::class, 'forceDelete'])->name('dosage_form.force-delete');

    //-------- active ingredients --------
    Route::get('activeingredient', [ActiveIngredientController::class, 'index'])->name('activeingredient.index');
    Route::get('activeingredient/create', [ActiveIngredientController::class, 'create'])->name('activeingredient.create');
    Route::post('activeingredient/store', [ActiveIngredientController::class, 'store'])->name('activeingredient.store');
    Route::get('activeingredient/{id}/edit', [ActiveIngredientController::class, 'edit'])->name('activeingredient.edit');
    Route::put('activeingredient/{id}', [ActiveIngredientController::class, 'update'])->name('activeingredient.update');
    Route::delete('activeingredient/{id}', [ActiveIngredientController::class, 'destroy'])->name('activeingredient.destroy');
    Route::get('activeingredient/trash', [ActiveIngredientController::class, 'trash'])->name('activeingredient.trash');
    Route::put('activeingredient/{id}/restore', [ActiveIngredientController::class, 'restore'])->name('activeingredient.restore');
    Route::delete('activeingredient/{id}/force-delete', [ActiveIngredientController::class, 'forceDelete'])->name('activeingredient.force-delete');

    //-------- medicines --------
    Route::get('admin/medicine', [MedicineController::class, 'index'])->name('medicine.index');
    Route::get('admin/medicine/create', [MedicineController::class, 'create'])->name('medicine.create');
    Route::post('admin/medicine', [MedicineController::class, 'store'])->name('medicine.store');
    Route::get('admin/medicine/{id}/edit', [MedicineController::class, 'edit'])->name('medicine.edit');
    Route::put('admin/medicine/{id}', [MedicineController::class, 'update'])->name('medicine.update');
    Route::delete('admin/medicine/{id}', [MedicineController::class, 'destroy'])->name('medicine.destroy');
    Route::get('admin/medicine/trash', [MedicineController::class, 'trash'])->name('medicine.trash');
    Route::put('admin/medicine/{id}/restore', [MedicineController::class, 'restore'])->name('medicine.restore');
    Route::delete('admin/medicine/{id}/force-delete', [MedicineController::class, 'forceDelete'])->name('medicine.force-delete');

    //-------- pharmacists --------
    Route::get('admin/pharmacist', [PharmacistController::class, 'index'])->name('pharmacist.index');
    Route::get('admin/pharmacist/create', [PharmacistController::class, 'create'])->name('pharmacist.create');
    Route::post('admin/pharmacist', [PharmacistController::class, 'store'])->name('pharmacist.store');
    Route::get('admin/pharmacist/{id}/review', [PharmacistController::class, 'review'])->name('pharmacist.review');
    Route::post('admin/pharmacist/{id}/approve', [PharmacistController::class, 'approve'])->name('pharmacist.approve');
    Route::post('admin/pharmacist/{id}/reject', [PharmacistController::class, 'reject'])->name('pharmacist.reject');

    //-------- pharmacies (read-only; created via pharmacy-request approval) --------
    Route::get('admin/pharmacy', [PharmacyController::class, 'index'])->name('pharmacy.index');

    //-------- pharmacy requests (pharmacist proposals -> admin approves/rejects) --------
    Route::get('admin/pharmacy-request', [PharmacyRequestController::class, 'adminIndex'])->name('admin.pharmacy_request.index');
    Route::get('admin/pharmacy-request/{id}/review', [PharmacyRequestController::class, 'review'])->name('admin.pharmacy_request.review');
    Route::post('admin/pharmacy-request/{id}/approve', [PharmacyRequestController::class, 'approve'])->name('admin.pharmacy_request.approve');
    Route::post('admin/pharmacy-request/{id}/reject', [PharmacyRequestController::class, 'reject'])->name('admin.pharmacy_request.reject');
    Route::post('/pharmacy/{id}/suspend', [PharmacyController::class, 'suspend'])
        ->name('pharmacy.suspend');

    Route::post('/pharmacy/{id}/activate', [PharmacyController::class, 'activate'])
        ->name('pharmacy.activate');
    //-------- users --------
    Route::get('admin/user', [UserController::class, 'index'])->name('user.index');
    Route::get('admin/user/create', [UserController::class, 'create'])->name('user.create');
    Route::post('admin/user', [UserController::class, 'store'])->name('user.store');

    //-------- medicine requests (pharmacist proposals -> admin approves/rejects) --------
    Route::get('admin/medicine-request', [MedicineRequestController::class, 'adminIndex'])->name('admin.medicine_request.index');
    Route::get('admin/medicine-request/{id}/review', [MedicineRequestController::class, 'review'])->name('admin.medicine_request.review');
    Route::post('admin/medicine-request/{id}/approve', [MedicineRequestController::class, 'approve'])->name('admin.medicine_request.approve');
    Route::post('admin/medicine-request/{id}/reject', [MedicineRequestController::class, 'reject'])->name('admin.medicine_request.reject');
});

//=============pharmacist operations (own pharmacy only)============
Route::middleware(['auth', 'role:pharmacist', 'approved.pharmacy'])->group(function () {

    //-------- purchases --------
    Route::get('pharmacist/purchase', [PurchaseController::class, 'index'])->name('purchase.index');
    Route::get('pharmacist/purchase/create', [PurchaseController::class, 'create'])->name('purchase.create');
    Route::post('pharmacist/purchase', [PurchaseController::class, 'store'])->name('purchase.store');

    //-------- batches (read-only; created/merged automatically from purchases) --------
    Route::get('pharmacist/batch', [BatchController::class, 'index'])->name('batch.index');

    //-------- sales --------
    Route::get('pharmacist/sale', [SaleController::class, 'index'])->name('sale.index');
    Route::get('pharmacist/sale/create', [SaleController::class, 'create'])->name('sale.create');
    Route::post('pharmacist/sale', [SaleController::class, 'store'])->name('sale.store');

    //-------- sale payments --------
    Route::get('pharmacist/sale-payment', [SalePaymentController::class, 'index'])->name('sale_payment.index');
    Route::get('pharmacist/sale-payment/create', [SalePaymentController::class, 'create'])->name('sale_payment.create');
    Route::post('pharmacist/sale-payment', [SalePaymentController::class, 'store'])->name('sale_payment.store');

    //-------- reports --------
    Route::get('pharmacist/report', [ReportController::class, 'index'])->name('report.index');

    //-------- medicine requests (propose to admin; never touches the catalog directly) --------
    Route::get('pharmacist/medicine-request', [MedicineRequestController::class, 'index'])->name('medicine_request.index');
    Route::get('pharmacist/medicine-request/create', [MedicineRequestController::class, 'create'])->name('medicine_request.create');
    Route::post('pharmacist/medicine-request', [MedicineRequestController::class, 'store'])->name('medicine_request.store');

    //-------- customer orders (cash-on-delivery requests placed against this pharmacy) --------
    Route::get('pharmacist/orders', [PharmacistOrderController::class, 'index'])->name('pharmacist_order.index');
    Route::post('pharmacist/orders/{order}/status', [PharmacistOrderController::class, 'updateStatus'])->name('pharmacist_order.updateStatus');
});

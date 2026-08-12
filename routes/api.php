<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

// رابط تسجيل الدخول (عام لأي شخص)
Route::post('/login', [AuthController::class, 'login']);

// روابط محمية (يجب أن يكون المستخدم مسجلاً دخوله ومعه التوكن)
Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::get('test', function () {

    return response()->json([
        'status' => true,
        'message' => 'this is new api you ok!',
        'data' => [
            'app_name' => 'pharmacyLink',
            'version' => '0.1'
        ]
    ]);
});

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;

class AuthController extends Controller
{
    //
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة'
            ], 401);
        }

        // توليد توكن جديد وحفظه للمستخدم
        $token = Str::random(60);
        $user->api_token = $token;
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'تم تسجيل الدخول بنجاح',
            'token' => $token,
            'user' => $user
        ]);
    }

    // دالة تسجيل الخروج
    public function logout(Request $request)
    {
        $user = $request->user(); // المستخدم الحالي المحمي بالتوكن

        if ($user) {
            $user->api_token = null;
            $user->save();

            return response()->json([
                'status' => true,
                'message' => 'تم تسجيل الخروج بنجاح'
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'حدث خطأ ما'
        ], 400);
    }
}

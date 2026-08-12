<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * إشعارات المستخدم الحالي.
     */
    public function index(Request $request)
    {
        $notifications = $request->user()->notifications()->paginate($request->query('per_page', 20));

        return response()->json([
            'status' => true,
            'data' => $notifications,
        ]);
    }

    /**
     * تعليم إشعار كمقروء.
     */
    public function markRead(Request $request, $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json([
            'status' => true,
            'message' => 'تم تعليم الإشعار كمقروء.',
        ]);
    }

    /**
     * تعليم كل الإشعارات كمقروءة.
     */
    public function markAllRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json([
            'status' => true,
            'message' => 'تم تعليم كل الإشعارات كمقروءة.',
        ]);
    }
}
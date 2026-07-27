<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()->notifications()->paginate(20);

        $layout = match ($request->user()->role) {
            'admin' => 'layouts.nav_admin',
            'pharmacist' => 'layouts.pharmacist',
            default => 'layouts.user',
        };

        return view('notifications.index', compact('notifications', 'layout'));
    }

    public function markRead(Request $request, $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return redirect($notification->data['url'] ?? back()->getTargetUrl());
    }

    public function markAllRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return back();
    }
}

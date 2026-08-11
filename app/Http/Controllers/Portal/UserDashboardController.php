<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $activeDoses = $user->doses()->where('active', true)->get();
        $recentOrders = $user->orders()->with('pharmacy')->latest()->take(3)->get();

        return view('User.dashboard', [
            'user' => $user,
            'activeDoses' => $activeDoses,
            'recentOrders' => $recentOrders,
        ]);
    }
}

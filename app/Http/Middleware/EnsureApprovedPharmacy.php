<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApprovedPharmacy
{
    // public function handle(Request $request, Closure $next): Response
    // {
    //     $pharmacist = $request->user()?->pharmacists;

    //     if (! $pharmacist || $pharmacist->status !== 'approved' || ! $pharmacist->pharmacies) {
    //         return redirect()->route('pharmacist.dashboard')
    //             ->with('error', 'يجب أن يتم اعتماد حسابك وربطه بصيدلية قبل الوصول إلى هذه الصفحة.');
    //     }

    //     return $next($request);
    //
    // }
    public function handle(Request $request, Closure $next): Response
    {
        $pharmacist = $request->user()?->pharmacists;

        if (!$pharmacist || $pharmacist->status !== 'approved' || !$pharmacist->pharmacies) {
            return redirect()->route('pharmacist.dashboard')
                ->with('error', 'يجب أن يتم اعتماد حسابك وربطه بصيدلية قبل الوصول إلى هذه الصفحة.');
        }     

        $pharmacy = $pharmacist->pharmacies;

        // إذا كانت الصيدلية موقوفة
        if ($pharmacy->status === 'suspended') {
            return redirect()
                ->route('pharmacist.dashboard')
                ->with('error', 'تم إيقاف هذه الصيدلية من قبل الإدارة.');
        }

        return $next($request);
    }
}

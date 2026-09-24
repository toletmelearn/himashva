<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CaptureUrlCoupon
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($coupon = $request->query('coupon')) {
            $request->session()->put('auto_coupon', strtoupper(trim($coupon)));
        }

        return $next($request);
    }
}

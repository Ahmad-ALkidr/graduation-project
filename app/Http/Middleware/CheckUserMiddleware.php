<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log; // أضف هذا
use Illuminate\Support\Facades\Route;

class CheckUserMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        Log::info('--- BROADCAST AUTH MIDDLEWARE CHECK ---');

        // ✨ --- بداية الكود الجديد --- ✨
        // احصل على المسار الحالي وقائمة الـ middleware الخاصة به
        $route = Route::current();
        if ($route) {
            $middleware = $route->gatherMiddleware();
            Log::info('Applied Middleware Stack: ' . json_encode($middleware));
        } else {
            Log::info('Could not determine the current route.');
        }
        // ✨ --- نهاية الكود الجديد --- ✨

        if ($request->user()) {
            Log::info('User IS Authenticated. ID: ' . $request->user()->id);
        } else {
            Log::info('User is NULL.');
        }
        Log::info('------------------------------------');

        return $next($request);
    }
}

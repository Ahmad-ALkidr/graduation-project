<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|string[]  $roles  // نقبل إما دور واحد أو مصفوفة أدوار
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // إذا الدور للمستخدم غير موجود ضمن الأدوار المسموح بها في الميدلوير نمنع الوصول
        if (!in_array($user->role, $roles)) {
            return response()->json(['message' => 'Forbidden - you do not have the required role'], 403);
        }

        return $next($request);
    }
}

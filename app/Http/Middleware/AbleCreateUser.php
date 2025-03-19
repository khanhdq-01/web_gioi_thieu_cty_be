<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AbleCreateUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $user = auth()->user();

        // Kiểm tra nếu người dùng chưa đăng nhập
        if (!$user) {
            return response('Unauthorized. Please log in.', 401);
        }

        // Kiểm tra nếu người dùng không có quyền truy cập
        if ($user->role_id != 1) {
            return response('You cannot access this function.', 403);
        }

        return $next($request);
    }
}

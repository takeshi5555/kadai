<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

use Illuminate\Support\Facades\Log;
class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string|array $roles): Response
    {
       if (!Auth::check()) {
        return redirect('/login')->with('error', 'ログインが必要です。');
    }

    $user = Auth::user();
    $requiredRoles = is_string($roles) ? explode(',', $roles) : $roles;
    
    // デバッグ情報
    Log::info('User role: "' . $user->role . '"');
    Log::info('Required roles: ' . json_encode($requiredRoles));
    
    // 直接チェック
    if (in_array($user->role, $requiredRoles)) {
        Log::info('Access granted');
        return $next($request);
    }
    
    Log::warning('Access denied');
    return redirect('/main');
    }
}


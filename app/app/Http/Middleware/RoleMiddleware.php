<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string|array  $roles  必要なロール (例: 'admin', 'moderator', 'admin,moderator')
     */
    public function handle(Request $request, Closure $next, string|array $roles): Response
    {
        if (!Auth::check()) {
            // ログインしていない場合はログインページへリダイレクト
            return redirect('/login')->with('error', 'ログインが必要です。');
        }

        $user = Auth::user();
        $requiredRoles = is_string($roles) ? explode(',', $roles) : $roles; // カンマ区切り文字列を配列に変換

        // ユーザーがいずれかの必要なロールを持っているかチェック
        if (!$user->hasAnyRole($requiredRoles)) {
            // 権限がない場合、ホームページにリダイレクトまたは403エラー
            return redirect('/main')->with('error', 'このページにアクセスする権限がありません。');
            // または abort(403, 'Unauthorized access.');
        }

        return $next($request);
    }
}
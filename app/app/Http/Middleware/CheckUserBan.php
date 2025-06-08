<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Authファサードを使用
use Symfony\Component\HttpFoundation\Response;

class CheckUserBan
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // ユーザーがログインしているか、かつBANされているかを確認
        if (Auth::check() && Auth::user()->is_banned) {
            // アクセスしようとしているルートがマイページでない場合
            if ($request->route()->getName() !== 'mypage.index') { // mypage.index は許可する
                // マイページにリダイレクトし、エラーメッセージをセッションにフラッシュ
                return redirect()->route('mypage.index')->with('error', 'お客様のアカウントは現在BANされています。マイページのみアクセス可能です。');
            }
        }

        return $next($request);
    }
}
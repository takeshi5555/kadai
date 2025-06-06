<?php

namespace App\Http\Controllers;

use Laravel\Socialite\Facades\Socialite;
use App\Models\User; // User モデルをインポート
use Illuminate\Support\Facades\Auth; // Auth ファサードをインポート

class SocialLoginController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // ここでユーザーを検索または作成し、ログインさせる
            $user = User::updateOrCreate([
                'google_id' => $googleUser->id,
            ], [
                'name' => $googleUser->name,
                'email' => $googleUser->email,
                // 必要に応じて、追加のユーザー情報を保存
            ]);

            Auth::login($user); // ユーザーをログインさせる

            // ★★★ 認証後のリダイレクト先 ★★★
            // ここでユーザーをリダイレクトします。
            // 例えば、ダッシュボードやホーム画面など。
            return redirect('/home'); // 例: /home にリダイレクト
            // または return redirect()->intended('/dashboard'); のように、
            // 元々アクセスしようとしたページにリダイレクトするメソッドも使えます。

        } catch (\Exception $e) {
            // エラーハンドリング
            // dd($e->getMessage()); // デバッグ用
            return redirect('/login')->with('error', 'Google ログインに失敗しました。');
        }
    }
}
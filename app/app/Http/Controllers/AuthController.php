<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin() {
        return view('auth.login');
    }

    public function login(Request $request) {
        // ログイン処理を実装
        $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);


    if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user(); 

           
            if ($user->role === 'admin') {
                return redirect('/main');
            } elseif ($user->role === 'moderator') {
                 return redirect('/main'); 
            } else {
               return redirect('/main');
            }
        }

    return back()->withErrors([
        'email' => 'メールアドレスまたはパスワードが正しくありません。',
    ]);
    }

    //ログアウト
    public function logout(Request $request){
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/login');}



    public function showSignup() {
        return view('auth.signup');
    }


    public function signup(Request $request) {
        // 新規登録処理
        $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:8|confirmed',
    ]);

    // セッションに一時保存して確認画面に渡す
    session([
        'name' => $request->name,
        'email' => $request->email,
        'password' => bcrypt($request->password) 
    ]);

    return redirect('/signup/confirm');
    }

    public function showSignupConfirm(Request $request) {
        if (!session()->has('name') || !session()->has('email') || !session()->has('password')) {
        return redirect('/login');
    }

        return view('auth.signup_confirm');
    }

    public function confirmSignup(Request $request) {
        // 登録確定処理
        // セッションから取得してDBに保存
    \App\Models\User::create([
        'name' => session('name'),
        'email' => session('email'),
        'password' => session('password'), // すでにbcrypt済み
    ]);

    // セッションを消しておく
    session()->forget(['name', 'email', 'password']);

    // フラッシュメッセージを添えてログイン画面へ
    return redirect('/login')->with('status', '新規登録が完了しました。ログインしてください。');

    }

    public function showPwdReset() {
        return view('auth.pwd_reset');
    }

    public function sendResetLink(Request $request) {
        // メール送信処理
        $request->validate(['email' => 'required|email']);
        $status = Password::sendResetLink($request->only('email'));
        
        return back()->with('message', $status == Password::RESET_LINK_SENT
        ? '再設定用メールを送信しました。'
        : 'メール送信に失敗しました。');
    }

    public function showPwdForm(Request $request) {
        if (!$request->has('token') || !$request->has('email')) {
        return redirect('/login');
    }

    return view('auth.pwd_form');
    }

    public function resetPassword(Request $request) {
        // パスワード変更処理
        $request->validate([
        'token' => 'required',
        'email' => 'required|email',
        'password' => 'required|min:8|confirmed',
    ]);

    $status = Password::reset(
        $request->only('email', 'password', 'password_confirmation', 'token'),
        function ($user, $password) {
            $user->password = Hash::make($password);
            $user->save();
        }
    );

    return $status == Password::PASSWORD_RESET
        ? redirect('/password/complete')->with('reset_done', true) 
        : back()->withErrors(['email' => 'パスワードリセットに失敗しました。']);
    }

    public function showPwdComplete() {
        if (!session('reset_done')) {
            return redirect('/login');
    }
        return view('auth.pwd_complete');
    }
}

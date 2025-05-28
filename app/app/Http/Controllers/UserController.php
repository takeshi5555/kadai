<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Authファサードを使う

class UserController extends Controller
{
    public function mypage()
    {
        // 認証済みユーザーの情報を取得
        $user = Auth::user();

        // ユーザーに関連するデータを取得 (例: ユーザーが投稿したスキル)
        // $userSkills = $user->skills()->orderBy('created_at', 'desc')->get();

        return view('mypage', compact('user')); // resources/views/mypage.blade.php を表示
    }
}
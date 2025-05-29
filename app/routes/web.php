<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SkillController;
use App\Http\Controllers\SkillImportController;
use App\Http\Controllers\MatchingController; // MatchingHistoryControllerから変更
use App\Http\Controllers\GoogleCalendarController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\UserController; // MypageControllerもこのコントローラーで扱うか、別途MypageControllerを作成
use App\Http\Controllers\MypageController; // マイページ専用のコントローラーを明確にする

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Welcome / Root Page
Route::get('/', function () {
    return view('welcome');
});

// Authentication Pages (認証関連ページ)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout'); // 名前の重複を避けるため上に移動

// Signup Pages (新規登録関連ページ)
Route::get('/signup', [AuthController::class, 'showSignup'])->name('signup');
Route::post('/signup', [AuthController::class, 'signup']);
Route::get('/signup/confirm', [AuthController::class, 'showSignupConfirm'])->name('signup.confirm');
Route::post('/signup/confirm', [AuthController::class, 'confirmSignup']);

// Password Reset Pages (パスワード再設定関連ページ)
Route::get('/password/reset', [AuthController::class, 'showPwdReset'])->name('password.request'); // Laravelのデフォルト名に寄せる
Route::post('/password/reset', [AuthController::class, 'sendResetLink'])->name('password.email'); // Laravelのデフォルト名に寄せる
Route::get('/password/form', [AuthController::class, 'showPwdForm'])->name('password.reset'); // ここがリセットフォームのURL
Route::post('/password/form', [AuthController::class, 'resetPassword'])->name('password.update'); // Laravelのデフォルト名に寄せる
Route::get('/password/complete', [AuthController::class, 'showPwdComplete'])->name('password.complete');


// Main Page (メインページ)
Route::get('/main', [MainController::class, 'index'])->name('main.index'); // `main` だった名前を `main.index` に変更（好みによる）

// Skill Search & Detail (スキル検索・詳細)
Route::get('/skill/search', [SkillController::class, 'index'])->name('skill.search.index'); // 名前を追加
Route::get('/skill/detail/{id}', [SkillController::class, 'show'])->name('skill.detail.show'); // 名前を追加

// Authenticated Routes (認証済みユーザーのみアクセス可能)
Route::middleware(['auth'])->group(function () {

    // My Page (マイページ) - ★ここが追加・修正のポイント★
    Route::get('/mypage', [MypageController::class, 'index'])->name('mypage.index'); // MypageControllerをここで使用

    // Skill Management (スキル管理)
    Route::get('/skill/manage', [SkillController::class, 'manage'])->name('skill.manage.index'); // 名前を追加
    Route::post('/skill', [SkillController::class, 'store'])->name('skill.store'); // 名前を追加
    Route::get('/skill/{id}/edit', [SkillController::class, 'edit'])->name('skill.edit'); // 名前を追加
    Route::post('/skill/{id}/update', [SkillController::class, 'update'])->name('skill.update'); // 名前を追加
    Route::post('/skill/{id}/delete', [SkillController::class, 'destroy'])->name('skill.destroy'); // 名前を追加

    // Skill Import (スキルインポート)
    Route::post('/skill/import', [SkillImportController::class, 'import'])->name('skill.import.store'); // 名前を追加
    Route::get('/skill/import/confirm', [SkillImportController::class, 'confirm'])->name('skill.import.confirm'); // 名前を追加
    Route::post('/skill/import/execute', [SkillImportController::class, 'execute'])->name('skill.import.execute'); // 名前を追加

    // Matching Application (マッチング申し込み)
    Route::get('/matching/apply/{skillId}', [MatchingController::class, 'apply'])->name('matching.apply.form'); // 名前を追加
    Route::post('/matching/apply/confirm', [MatchingController::class, 'confirm'])->name('matching.apply.confirm'); // 名前を追加
    Route::post('/matching/apply/execute', [MatchingController::class, 'store'])->name('matching.apply.store'); // 名前を追加

    // Google Calendar Integration (Googleカレンダー連携)
    Route::get('google/auth', [GoogleCalendarController::class, 'redirectToGoogle'])->name('google.auth');
    Route::get('google/callback', [GoogleCalendarController::class, 'handleGoogleCallback'])->name('google.callback');

    // Matching History & Actions (マッチング履歴・承認・拒否・完了・キャンセル)
    Route::get('/matching/history', [MatchingController::class, 'history'])->name('matching.history.index'); // 名前を追加
    Route::post('/matching/{id}/approve', [MatchingController::class, 'approve'])->name('matching.approve'); // 名前を追加
    Route::post('/matching/{id}/reject', [MatchingController::class, 'reject'])->name('matching.reject'); // 名前を追加
    Route::post('/matching/{id}/cancel', [MatchingController::class, 'cancel'])->name('matching.cancel'); // 名前を追加
    Route::post('/matching/{id}/complete', [MatchingController::class, 'complete'])->name('matching.complete'); // 名前を追加
    // マッチング履歴ダウンロード（追加）
    Route::get('/matching/history/download', [MatchingController::class, 'download'])->name('matching.history.download'); // MatchingControllerでdownloadメソッドを実装してください

    // Review (レビュー)
    Route::get('/review/{matchingId}', [ReviewController::class, 'form'])->name('review.form'); // 名前を追加
    Route::post('/review/{matchingId}', [ReviewController::class, 'submit'])->name('review.submit'); // 名前を追加

    // Message (メッセージ)
    Route::get('/message/{matchingId}', [MessageController::class, 'show'])->name('message.show');
    Route::post('/message/{matchingId}', [MessageController::class, 'store'])->name('message.store');

});
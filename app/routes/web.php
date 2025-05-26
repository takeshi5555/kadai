<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SkillController;
use App\Http\Controllers\SkillImportController;
use App\Http\Controllers\MatchingController;
use App\Http\Controllers\GoogleCalendarController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\MessageController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::get('/signup', [AuthController::class, 'showSignup']);
Route::post('/signup', [AuthController::class, 'signup']);

Route::get('/signup/confirm', [AuthController::class, 'showSignupConfirm']);
Route::post('/signup/confirm', [AuthController::class, 'confirmSignup']);

Route::get('/password/reset', [AuthController::class, 'showPwdReset']);
Route::post('/password/reset', [AuthController::class, 'sendResetLink']);

Route::get('/password/form', [AuthController::class, 'showPwdForm']);
Route::post('/password/form', [AuthController::class, 'resetPassword']);

Route::get('/password/complete', [AuthController::class, 'showPwdComplete']);
// 認証関連ページ
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// パスワード再設定関連
Route::get('/password/reset', [AuthController::class, 'showPwdReset']);
Route::post('/password/reset', [AuthController::class, 'sendResetLink']);

Route::get('/password/form', [AuthController::class, 'showPwdForm'])->name('password.reset'); // リンクからアクセス
Route::post('/password/form', [AuthController::class, 'resetPassword']);

Route::get('/password/complete', [AuthController::class, 'showPwdComplete']);
// パスワード再設定関連


//スキル関係
Route::get('/skill/search', [SkillController::class, 'index']);
Route::get('/skill/detail/{id}', [SkillController::class, 'show']);

Route::middleware(['auth'])->group(function () {
    Route::get('/skill/manage', [SkillController::class, 'manage']);
    Route::post('/skill', [SkillController::class, 'store']);
    Route::get('/skill/{id}/edit', [SkillController::class, 'edit']);
    Route::post('/skill/{id}/update', [SkillController::class, 'update']);
    Route::post('/skill/{id}/delete', [SkillController::class, 'destroy']);
});

//excelのインポートどうたらこうたら
Route::middleware('auth')->group(function () {
    Route::post('/skill/import', [SkillImportController::class, 'import']);
    Route::get('/skill/import/confirm', [SkillImportController::class, 'confirm']);
    Route::post('/skill/import/execute', [SkillImportController::class, 'execute']);
});

//マッチング関連
Route::middleware('auth')->group(function () {
    Route::get('/matching/apply/{skillId}', [MatchingController::class, 'apply']);
    Route::post('/matching/apply/confirm', [MatchingController::class, 'confirm']);
    Route::post('/matching/apply/execute', [MatchingController::class, 'store']);

});
//グーグルのやつ
Route::middleware('auth')->group(function () {
    Route::get('google/auth', [GoogleCalendarController::class, 'redirectToGoogle'])->name('google.auth');
    Route::get('google/callback', [GoogleCalendarController::class, 'handleGoogleCallback'])->name('google.callback');
});

//マッチング履歴とか承認とか
Route::middleware('auth')->group(function () {
    Route::get('/matching/history', [MatchingController::class, 'history']);
    Route::post('/matching/{id}/approve', [MatchingController::class, 'approve']);
    Route::post('/matching/{id}/reject', [MatchingController::class, 'reject']);
    Route::post('/matching/{id}/cancel', [MatchingController::class, 'cancel']);
    Route::post('/matching/{id}/complete', [MatchingController::class, 'complete']);

});

//レビューらへん
Route::middleware('auth')->group(function () {
    Route::get('/review/{matchingId}', [ReviewController::class, 'form']);
    Route::post('/review/{matchingId}', [ReviewController::class, 'submit']);
});

//message
Route::middleware('auth')->group(function () {
    Route::get('/message/{matchingId}', [MessageController::class, 'show'])->name('message.show');
    Route::post('/message/{matchingId}', [MessageController::class, 'store'])->name('message.store');
});

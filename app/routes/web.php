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
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminsController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Admin\SkillsController;
use App\Http\Controllers\Admin\ReportsController;
use Illuminate\Http\Request;
use App\Http\Controllers\ExportController;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
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
Route::post('/logout', [AuthController::class, 'logout'])->name('logout'); 

// Signup Pages (新規登録関連ページ)
Route::get('/signup', [AuthController::class, 'showSignup'])->name('signup');
Route::post('/signup', [AuthController::class, 'signup']);
Route::get('/signup/confirm', [AuthController::class, 'showSignupConfirm'])->name('signup.confirm');
Route::post('/signup/confirm', [AuthController::class, 'confirmSignup']);

Route::post('/signup/back', function (Request $request) {
    return redirect('/signup')->withInput($request->only('name', 'email'));
})->name('signup.back');

// Password Reset Pages (パスワード再設定関連ページ)
Route::get('/password/reset', [AuthController::class, 'showPwdReset'])->name('password.request'); // Laravelのデフォルト名に寄せる
Route::post('/password/reset', [AuthController::class, 'sendResetLink'])->name('password.email'); // Laravelのデフォルト名に寄せる
Route::get('/password/form', [AuthController::class, 'showPwdForm'])->name('password.reset'); // ここがリセットフォームのURL
Route::post('/password/form', [AuthController::class, 'resetPassword'])->name('password.update'); // Laravelのデフォルト名に寄せる
Route::get('/password/complete', [AuthController::class, 'showPwdComplete'])->name('password.complete');

Route::post('/warning/{warning}/mark-as-read', [MypageController::class, 'markWarningAsRead'])
     ->name('warning.mark_as_read')
     ->middleware('auth'); 

// Main Page (メインページ)
Route::get('/main', [MainController::class, 'index'])->name('main.index'); // `main` だった名前を `main.index` に変更（好みによる）

// Skill Search & Detail (スキル検索・詳細)
Route::get('/skill/search', [SkillController::class, 'search'])->name('skill.search');
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

     // ★追加: マッチング履歴のエクスポート
    Route::get('/profile/export-matching-history', [ExportController::class, 'showExportForm'])->name('profile.export.form');
    Route::post('/profile/export-matching-history', [ExportController::class, 'exportMatchingHistory'])->name('profile.export.execute');

    //reports
    Route::post('/reports', [ReportController::class, 'store'])->name('reports.store');
});

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {

    // 管理者 ('admin') ロールのみがアクセスできるルート
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/', [AdminsController::class, 'index'])->name('index'); // 管理者ダッシュボード
        Route::resource('users', UsersController::class)->except(['show', 'create', 'store']); // ユーザー管理
        Route::resource('skills', SkillsController::class)->except(['show', 'create', 'store']); // スキル管理


                // ユーザーBAN/BAN解除のルート
        Route::put('users/{user}/ban', [UsersController::class, 'toggleBan'])->name('users.toggleBan');
        
        // ReportsController のルート (既存のresourceは削除または修正)
        // updateはAJAX用なので個別定義
        Route::put('reports/{report}', [ReportsController::class, 'update'])->name('reports.update');
        // warnUserはPOSTリクエスト
        Route::post('reports/{report}/warn', [ReportsController::class, 'warnUser'])->name('reports.warnUser');
    });

    

    // 管理者 ('admin') またはモデレーター ('moderator') ロールのどちらかがアクセスできるルート
    // これらのルートも '/admin' URLプレフィックスと 'admin.' ルート名プレフィックスを持つ
    Route::middleware(['role:admin,moderator'])->group(function () {
        Route::resource('reports', ReportsController::class)->except(['create', 'store', 'edit','update']); // 通報管理
    });

});

// Google認証ページへのリダイレクト
Route::get('/auth/google/redirect', function () {
    return Socialite::driver('google')->redirect();
})->name('google.redirect');

// Googleからのコールバック
Route::get('/auth/google/callback', function () {
    try {
        $googleUser = Socialite::driver('google')->user();
    } catch (\Exception $e) {
        // エラーメッセージを一時的に表示する
        dd($e);
        // return redirect('/login')->with('error', 'Google認証に失敗しました。'); // この行はコメントアウトまたは削除
    }

    // メールアドレスでユーザーを検索または新規作成
    $user = User::firstOrCreate(
        ['email' => $googleUser->getEmail()],
        [
            'name' => $googleUser->getName(),
            'password' => bcrypt(Str::random(16)), // ランダムなパスワードを生成
            'google_id' => $googleUser->getId(), // Google IDを保存
            // 必要に応じて他のユーザー情報も保存
        ]
    );

    // ユーザーがGoogle IDを持っていなければ更新
    if (is_null($user->google_id)) {
        $user->google_id = $googleUser->getId();
        $user->save();
    }

    // ログイン処理
    Auth::login($user, true); // trueでremember me

    return redirect('/main'); // ログイン後のリダイレクト先
});
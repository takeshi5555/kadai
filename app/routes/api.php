<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ReportReasonController;
use Illuminate\Support\Facades\Log;
use App\Notifications\TestPushNotification;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/report-reasons/{id}/children', [ReportReasonController::class, 'getChildren']);

Route::middleware('auth:sanctum')->post('/webpush/subscribe', function (Request $request) {
    Log::info('Webpush Subscribe Route: リクエストを受信しました。');
    Log::info('ユーザーID: ' . auth()->id());
    Log::info('リクエストペイロード: ' . json_encode($request->all()));

    $user = $request->user();
    $subscription = $request->json('subscription');

    $user->webPushSubscriptions()->updateOrCreate(
        ['endpoint' => $subscription['endpoint']],
        [
            'public_key' => $subscription['keys']['p256dh'],
            'auth_token' => $subscription['keys']['auth'],
            'content_encoding' => $subscription['contentEncoding'] ?? 'aesgcm',
        ]
    );

    return response()->json(['message' => 'Subscription saved successfully!']);
});
Route::middleware('auth:sanctum')->post('/send-test-push', function (Request $request) {
    $user = $request->user();

    // ユーザーに通知を送る
    $user->notify(new TestPushNotification());

    return response()->json(['message' => '通知を送信しました']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/notifications/count', [NotificationController::class, 'getNotificationCount']);
    Route::post('/notifications/message/read', [NotificationController::class, 'markMessageAsRead']);
    Route::post('/notifications/matching/confirm', [NotificationController::class, 'confirmMatching']);
});

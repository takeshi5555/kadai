<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google\Client; // Google API クライアントライブラリの Client クラス
use Google\Service\Calendar; // Google Calendar サービスのメインクラス
use Illuminate\Support\Facades\Auth; // ユーザー認証
use Illuminate\Support\Facades\Log; // ログ出力
use Illuminate\Support\Facades\Session; // セッション管理

class GoogleCalendarController extends Controller
{
    protected $client; // Google Client インスタンスを保持するプロパティ

    public function __construct()
    {
        // Google Client インスタンスの初期設定
        $client = new Client();
        $client->setClientId(config('services.google.client_id'));       // .envからクライアントIDを取得
        $client->setClientSecret(config('services.google.client_secret')); // .envからクライアントシークレットを取得
        $client->setRedirectUri(config('services.google.redirect'));     // .envからリダイレクトURIを取得

        // カレンダーのイベント操作に必要なスコープを設定
        // https://www.googleapis.com/auth/calendar.events はイベントの作成・閲覧・更新・削除のみ
        // https://www.googleapis.com/auth/calendar はカレンダー自体の管理も含むフルアクセス
        $client->addScope(config('services.google.calendar_scope'));

        // オフラインアクセスを有効にすることで、リフレッシュトークンを取得できる
        // リフレッシュトークンがあれば、ユーザーがオフラインでも新しいアクセストークンを生成できる
        $client->setAccessType('offline');
        // 同意画面でアカウント選択と同意のプロンプトを常に表示
        $client->setPrompt('select_account consent');

        $this->client = $client;
    }

    /**
     * Google認証画面にリダイレクトする
     * ユーザーがGoogleカレンダーへのアクセスを許可するための最初のステップ
     */
    public function redirectToGoogle()
    {
        // Google認証のURLを生成
        $authUrl = $this->client->createAuthUrl();
        // 生成したURLにユーザーをリダイレクト
        return redirect()->away($authUrl);
    }

    /**
     * Googleからのコールバックを処理する
     * ユーザーがGoogle認証を許可した後、Googleがここにリダイレクトしてくる
     */
    public function handleGoogleCallback(Request $request)
    {
        // URLに認証コード（code）が含まれているか確認
        if ($request->has('code')) {
            try {
                // 認証コードを使ってアクセストークンを取得
                $token = $this->client->fetchAccessTokenWithAuthCode($request->input('code'));

                // エラーチェック
                if (isset($token['error'])) {
                    Log::error('Google OAuth Error: ' . $token['error_description']);
                    return redirect('/')->with('error', 'Google認証に失敗しました。');
                }

                // アクセストークンとリフレッシュトークンをデータベースに保存
                // Auth::user() にはログイン中のユーザーオブジェクトが入る
                // users テーブルに 'google_access_token', 'google_refresh_token', 'google_expires_in', 'google_scope' カラムが必要です
                Auth::user()->update([
                    'google_access_token' => $token['access_token'],
                    'google_refresh_token' => isset($token['refresh_token']) ? $token['refresh_token'] : null, // リフレッシュトークンは初回のみ取得されることが多い
                    'google_expires_in' => time() + $token['expires_in'], // 現在時刻 + 有効期限秒数
                    'google_scope' => $token['scope'],
                ]);

                // 成功メッセージをセッションにフラッシュ
                Session::flash('google_token_saved', true);

                // 認証成功後、ダッシュボードなどにリダイレクト
                return redirect()->route('dashboard')->with('success', 'Googleカレンダーとの連携が完了しました！');

            } catch (\Exception $e) {
                // トークン取得時の予期せぬエラー
                Log::error('Google OAuth Token Fetch Error: ' . $e->getMessage());
                return redirect('/')->with('error', 'Google認証中にエラーが発生しました。');
            }
        } else {
            // ユーザーが認証を拒否した場合や、認証コードがない場合
            Log::warning('Google OAuth: Authorization code not received or user denied consent.');
            return redirect('/')->with('error', 'Google認証がキャンセルされました。');
        }
    }

    /**
     * ログイン中のユーザーのためのGoogle Clientインスタンスを取得するヘルパーメソッド
     * 他のコントローラー（例: MatchingController）から呼び出されることを想定
     * アクセストークンの期限切れをチェックし、必要に応じてリフレッシュする
     */
    public function getGoogleClientForUser()
    {
        // ユーザーがログインしているか確認
        if (!Auth::check()) {
            Log::warning('Attempted to get Google Client for unauthenticated user.');
            return null; // ログインしていない場合はnullを返すか、例外をスロー
        }

        $user = Auth::user();
        $accessToken = $user->google_access_token;
        $refreshToken = $user->google_refresh_token;
        $expiresIn = $user->google_expires_in;

        // アクセストークンがない、または有効期限切れが近い場合
        // 有効期限が切れる1分前になったらリフレッシュを試みる
        if (!$accessToken || ($expiresIn && $expiresIn < time() + 60)) {
            if ($refreshToken) {
                // リフレッシュトークンが存在する場合、新しいアクセストークンを取得
                try {
                    $this->client->fetchAccessTokenWithRefreshToken($refreshToken);
                    $newAccessToken = $this->client->getAccessToken();

                    // 新しいトークンをDBに保存
                    $user->update([
                        'google_access_token' => $newAccessToken['access_token'],
                        'google_expires_in' => time() + $newAccessToken['expires_in'],
                        // リフレッシュトークン自体は通常変わらないが、念のため更新
                        'google_refresh_token' => isset($newAccessToken['refresh_token']) ? $newAccessToken['refresh_token'] : $refreshToken,
                    ]);
                    $this->client->setAccessToken($newAccessToken); // クライアントインスタンスにも新しいトークンを設定
                    Log::info('Google access token refreshed for user: ' . $user->id);
                } catch (\Exception $e) {
                    // リフレッシュトークンも無効な場合など、リフレッシュに失敗
                    Log::error('Failed to refresh Google access token for user ' . $user->id . ': ' . $e->getMessage());
                    // 再認証が必要であることを示すために、エラーメッセージ付きでnullを返す（呼び出し元でリダイレクトを処理）
                    Session::flash('error', 'Google認証の有効期限が切れました。再認証してください。');
                    return null;
                }
            } else {
                // リフレッシュトークンがない場合、再認証が必要
                Session::flash('error', 'Googleカレンダーとの連携が必要です。');
                return null;
            }
        } else {
            // アクセストークンが有効な場合、それをクライアントに設定
            $this->client->setAccessToken([
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'expires_in' => $expiresIn - time(), // 残り有効期限を秒で渡す
            ]);
        }
        return $this->client; // 認証済み/リフレッシュ済みのクライアントを返す
    }
}

// メインJavaScript（修正版）
// ユーザーの操作でのみ実行されるようにする

// VAPID公開キーをHTMLのメタタグから取得
const vapidPublicKeyElement = document.querySelector('meta[name="webpush-vapid-public-key"]');
let vapidPublicKey = vapidPublicKeyElement ? vapidPublicKeyElement.getAttribute('content') : null;


if (vapidPublicKey && vapidPublicKey.startsWith('base64:')) {
    vapidPublicKey = vapidPublicKey.substring(7); // 'base64:'.length は 7
}
// vapidPublicKeyが取得できなかった場合の初期チェック
if (!vapidPublicKey) {
    console.error('VAPID public key meta tag not found or empty. Please ensure <meta name="webpush-vapid-public-key" content="..."> is in your HTML.');
    alert('通知機能の設定に問題があります。VAPID公開キーが取得できませんでした。');
    // ここで以降の処理を停止する
    // VAPIDキーがないとプッシュ通知は機能しないため、エラーメッセージ表示後、早期リターンする
    throw new Error('VAPID Public Key is not available.');
}


// Base64からUint8Arrayに変換
function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding)
        .replace(/\-/g, '+')
        .replace(/_/g, '/');

    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);

    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}

// Push subscriptionを作成
function subscribeUserToPush() {
    // vapidPublicKeyが利用可能であることを確認
    if (!vapidPublicKey) {
        console.error('VAPID public key is not available for subscription.');
        alert('VAPID公開キーが取得できないため、プッシュ通知の購読を開始できません。');
        return Promise.reject('VAPID key not available');
    }

    return navigator.serviceWorker.ready
        .then(function(registration) {
            console.log('Service Worker is ready, subscribing to push...');
            
            const subscribeOptions = {
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(vapidPublicKey)
            };

            return registration.pushManager.subscribe(subscribeOptions);
        })
        .then(function(pushSubscription) {
            console.log('Push subscription successful:', pushSubscription);
            
            // サーバーにsubscriptionを送信
            return sendSubscriptionToServer(pushSubscription);
        })
        .catch(function(error) {
            console.error('Push subscription failed:', error);
            
            // エラーメッセージをより具体的に
            if (error.name === 'AbortError') {
                alert('プッシュ通知の登録に失敗しました。VAPID設定またはネットワーク接続を確認してください。');
            } else if (error.name === 'NotSupportedError') {
                alert('このブラウザはプッシュ通知をサポートしていません。');
            } else if (error.name === 'NetworkError') {
                 alert('プッシュ通知サービスへの接続に失敗しました。インターネット接続を確認してください。');
            }
             else {
                alert('プッシュ通知の設定中に予期せぬエラーが発生しました: ' + error.message);
            }
            throw error; // エラーを再スローして、呼び出し元に伝える
        });
}

// Subscriptionをサーバーに送信
function sendSubscriptionToServer(subscription) {
    const csrfTokenElement = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = csrfTokenElement ? csrfTokenElement.getAttribute('content') : null;

    if (!csrfToken) {
        console.warn('CSRF token not found in meta tag. Skipping subscription save to server.');
        return Promise.resolve(); // CSRFトークンがなくても、通知購読自体は成功しているため、エラーにしない
    }

    // サーバーサイドで設定した購読保存ルートを使用
    return fetch('/api/webpush/subscribe', { // ★ここをLaravelのroute/web.phpで設定したPOSTルートに合わせる
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        credentials: 'include',
        body: JSON.stringify({
            subscription: subscription
        })
    })
    .then(response => {
        if (!response.ok) {
            // サーバーからのエラーレスポンスを詳しくログに表示
            return response.json().then(errorData => {
                throw new Error(`Server responded with error: ${response.status} ${response.statusText} - ${JSON.stringify(errorData)}`);
            }).catch(() => {
                throw new Error(`Server responded with error: ${response.status} ${response.statusText}`);
            });
        }
        return response.json();
    })
    .then(data => {
        console.log('Subscription saved to server:', data);
        alert('プッシュ通知が有効になりました！'); // サーバー保存成功後にユーザーに通知
    })
    .catch(error => {
        console.warn('Failed to save subscription to server:', error);
        alert('プッシュ通知は有効になりましたが、サーバーへの登録に失敗しました。');
        // サーバー保存に失敗してもsubscription自体は有効であるため、致命的なエラーとはしない
    });
}

// 通知を有効にする関数（ユーザーの操作でのみ実行）
function enableNotifications() {
    console.log('Enable notifications clicked');

    // ブラウザサポートチェック
    if (!('serviceWorker' in navigator)) {
        alert('このブラウザはService Workerをサポートしていません。最新のブラウザを使用してください。');
        return;
    }

    if (!('PushManager' in window)) {
        alert('このブラウザはプッシュ通知をサポートしていません。最新のブラウザを使用してください。');
        return;
    }

    if (!('Notification' in window)) {
        alert('このブラウザは通知をサポートしていません。最新のブラウザを使用してください。');
        return;
    }

    // 既に許可されているかチェック
    if (Notification.permission === 'granted') {
        console.log('Notification permission already granted. Proceeding to subscribe.');
        registerServiceWorkerAndSubscribe();
        return;
    }

    if (Notification.permission === 'denied') {
        alert('通知が拒否されています。ブラウザの設定から通知を許可してください。');
        return;
    }

    // 通知許可を要求
    Notification.requestPermission()
        .then(function(permission) {
            console.log('Notification permission:', permission);
            
            if (permission === 'granted') {
                registerServiceWorkerAndSubscribe();
            } else {
                alert('プッシュ通知を有効にするには、通知の許可が必要です。');
            }
        })
        .catch(function(error) {
            console.error('Notification permission request error:', error);
            alert('通知許可の取得中にエラーが発生しました。');
        });
}

// Service Workerを登録してプッシュ通知を設定
function registerServiceWorkerAndSubscribe() {
    navigator.serviceWorker.register('/service-worker.js')
        .then(function(registration) {
            console.log('Service Worker registered successfully:', registration.scope);
            return subscribeUserToPush(); // プッシュ購読処理を呼び出す
        })
        .catch(function(error) {
            console.error('Service Worker registration failed:', error);
            alert('Service Workerの登録に失敗しました。ブラウザキャッシュをクリアして再試行してください。');
        });
}

// テスト通知を送信する関数 (この通知はブラウザのAPIを使ったもので、サーバーからのプッシュ通知とは異なる点に注意)
function sendTestNotification() {
    if (Notification.permission === 'granted') {
        navigator.serviceWorker.ready.then(function(registration) {
            registration.showNotification('テスト通知', {
                body: 'これはテスト通知です',
                icon: '/favicon.ico',
                // 必要に応じて他のオプションも追加
            });
            console.log('Local test notification shown.');
        }).catch(function(error) {
            console.error('Failed to show local test notification:', error);
            alert('ローカルテスト通知の表示に失敗しました。');
        });
    } else {
        alert('通知が許可されていません。テスト通知を表示できません。');
    }
}


// DOM読み込み完了後にイベントリスナーを設定
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded');

    // 通知有効化ボタン
    const notificationBtn = document.getElementById('enable-notifications');
    if (notificationBtn) {
        notificationBtn.addEventListener('click', enableNotifications);
        console.log('Notification button event listener added');
    } else {
        console.warn('Notification enable button (#enable-notifications) not found.');
    }

    // テスト通知ボタン
    const testBtn = document.getElementById('test-notification');
    if (testBtn) {
        testBtn.addEventListener('click', sendTestNotification);
        console.log('Test notification button event listener added');
    } else {
        console.warn('Test notification button (#test-notification) not found.');
    }

    // 現在の通知状態を表示
    if ('Notification' in window) {
        console.log('Current notification permission:', Notification.permission);
    } else {
        console.warn('Notification API not supported in this browser.');
    }
});
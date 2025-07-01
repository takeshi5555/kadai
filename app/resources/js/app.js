// resources/js/app.js

// Bootstrap (Laravel Breeze/UI などで提供される基本的なJS機能) を読み込む
require('./bootstrap');

// Firebase SDK 関連のインポートはすべて削除
// import { initializeApp } from "firebase/app";
// import { getMessaging, getToken, onMessage, deleteToken } from "firebase/messaging";

// Firebaseの設定オブジェクトも削除
// const firebaseConfig = { ... };

// Firebaseの初期化とMessagingサービスの取得も削除
// const app = initializeApp(firebaseConfig);
// const messaging = getMessaging(app);

// CSRFトークンとVAPID公開鍵の取得はpush-notifications.jsに任せるため、ここからは削除
// const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
// const vapidPublicKey = document.querySelector('meta[name="vapid-public-key"]').getAttribute('content');

// Service Worker登録とgetToken、onMessageなどのFCM関連ロジックは全て削除
// if ('serviceWorker' in navigator) { ... }

// sendTokenToServer 関数も削除
// function sendTokenToServer(token) { ... }

// unsubscribe 関数も削除
// function unsubscribe() { ... }


// DOM読み込み完了後にイベントリスナーを設定 (ナビバーのバッジ表示調整のみ残す)
document.addEventListener('DOMContentLoaded', function() {
    var navbarCollapse = document.getElementById('navbarNav'); // ナビバーのID

    if (navbarCollapse) {
        navbarCollapse.addEventListener('shown.bs.collapse', function () {
            var badges = document.querySelectorAll('.navbar-collapse .nav-item .nav-link.position-relative .badge');
            badges.forEach(function(badge) {
                badge.style.position = 'static';
                badge.style.marginLeft = '5px';
                badge.style.transform = 'none';
                badge.style.display = 'inline-block';
                badge.style.top = 'auto';
                badge.style.right = 'auto';
            });
        });
        // 必要であれば、hidden.bs.collapse イベントの処理もここに追加
    }
});
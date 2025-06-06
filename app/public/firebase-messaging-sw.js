// public/firebase-messaging-sw.js

// Firebase SDK の CDN を読み込み (Viteのビルド対象外なので直接指定)
importScripts('https://www.gstatic.com/firebasejs/9.23.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/9.23.0/firebase-messaging-compat.js');

// Firebaseの初期化（firebasaConfigはここに直接記述するか、共有の仕組みで渡す）
// Service Workerはメインのスクリプトとは独立して動作するため、firebaseConfigを直接記述するのが確実です
const firebaseConfig = {
  apiKey: "AIzaSyC4ZsFPI5VYJvQm7Sv3siM8VIavlp76C9U",
  authDomain: "skillswap-297c1.firebaseapp.com",
  projectId: "skillswap-297c1",
  storageBucket: "skillswap-297c1.firebasestorage.app",
  messagingSenderId: "458391938988",
  appId: "1:458391938988:web:a847baf1a825ce375ffc09",
  measurementId: "G-GP1NF02XC0"
};

firebase.initializeApp(firebaseConfig);

const messaging = firebase.messaging();

// バックグラウンドでプッシュ通知を受信したときの処理
messaging.onBackgroundMessage((payload) => {
    console.log('[firebase-messaging-sw.js] Received background message ', payload);

    const notificationTitle = payload.notification.title;
    const notificationOptions = {
        body: payload.notification.body,
        icon: '/favicon.ico' // 通知に表示するアイコンのパス (publicディレクトリからの相対パス)
        // 他のオプションも追加可能 (例: data, image, actionsなど)
    };

    self.registration.showNotification(notificationTitle, notificationOptions);
});

// 通知をクリックしたときの処理
self.addEventListener('notificationclick', (event) => {
    event.notification.close(); // 通知を閉じる

    // クリックされた通知に関連するURLを開く
    // payload.notification.data にURLが含まれている場合など
    if (event.notification.data && event.notification.data.url) {
        clients.openWindow(event.notification.data.url);
    } else {
        // デフォルトのURL（例: アプリのトップページ）
        clients.openWindow('/');
    }
});
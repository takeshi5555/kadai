// public/service-worker.js (強化版)

const CACHE_NAME = 'app-cache-v1';
const SW_VERSION = '1.0.0';

// Service Workerのインストールイベント
self.addEventListener('install', function(event) {
    console.log(`Service Worker ${SW_VERSION} installing...`);
    
    event.waitUntil(
        caches.open(CACHE_NAME).then(function(cache) {
            // 重要なリソースをプリキャッシュ
            return cache.addAll([
                '/',
                '/favicon.ico',
                '/images/notification-icon.png',
                '/images/notification-badge.png'
            ]).catch(function(error) {
                console.warn('Cache preload failed:', error);
            });
        }).then(function() {
            // 新しいService Workerをすぐに有効化
            return self.skipWaiting();
        })
    );
});

// Service Workerの活性化イベント
self.addEventListener('activate', function(event) {
    console.log(`Service Worker ${SW_VERSION} activated`);
    
    event.waitUntil(
        // 古いキャッシュをクリア
        caches.keys().then(function(cacheNames) {
            return Promise.all(
                cacheNames.map(function(cacheName) {
                    if (cacheName !== CACHE_NAME) {
                        console.log('Deleting old cache:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        }).then(function() {
            // 全てのクライアントを制御下に置く
            return self.clients.claim();
        })
    );
});

// プッシュ通知イベントのリッスン
self.addEventListener('push', function(event) {
    console.log('Push event received:', event);
    
    let data = {};
    let title = '通知';
    
    if (event.data) {
        try {
            data = event.data.json();
            console.log('Push data parsed:', data);
        } catch (e) {
            console.warn('Failed to parse push data as JSON:', e);
            data = { 
                body: event.data.text(),
                title: '通知'
            };
        }
    }

    title = data.title || '新しい通知';
    
    const options = {
        body: data.body || '新しい通知があります。',
        icon: data.icon || '/favicon.ico',
        badge: data.badge || '/favicon.ico',
        image: data.image || undefined,
        tag: data.tag || 'default-notification',
        renotify: data.renotify !== undefined ? data.renotify : true,
        requireInteraction: data.requireInteraction || false,
        silent: data.silent || false,
        timestamp: data.timestamp ? new Date(data.timestamp).getTime() : Date.now(),
        data: {
            url: data.url || data.data?.url || '/',
            type: data.type || data.data?.type || 'general',
            id: data.id || data.data?.id || Date.now(),
            ...data.data
        },
        actions: data.actions || [
            {
                action: 'view',
                title: '表示',
                icon: '/images/view-icon.png'
            },
            {
                action: 'dismiss',
                title: '閉じる',
                icon: '/images/close-icon.png'
            }
        ],
        vibrate: data.vibrate || [200, 100, 200] // バイブレーションパターン
    };

    event.waitUntil(
        self.registration.showNotification(title, options)
            .then(() => {
                console.log('Notification displayed successfully');
                
                // 通知表示をトラッキング（オプション）
                return fetch('/api/notifications/displayed', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        notification_id: options.data.id,
                        timestamp: new Date().toISOString()
                    })
                }).catch(error => {
                    console.warn('Failed to track notification display:', error);
                });
            })
            .catch(error => {
                console.error('Failed to show notification:', error);
            })
    );
});

// 通知クリックイベントのリッスン
self.addEventListener('notificationclick', function(event) {
    console.log('Notification clicked:', event);
    
    const notification = event.notification;
    const action = event.action;
    const data = notification.data || {};
    
    // 通知を閉じる
    notification.close();
    
    // アクションに基づく処理
    if (action === 'dismiss') {
        console.log('Notification dismissed');
        return;
    }
    
    const urlToOpen = action === 'view' || !action 
        ? (data.url || '/') 
        : '/';
    
    // クリック追跡
    const trackClick = fetch('/api/notifications/clicked', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            notification_id: data.id,
            action: action || 'default',
            timestamp: new Date().toISOString()
        })
    }).catch(error => {
        console.warn('Failed to track notification click:', error);
    });

    // ウィンドウの処理
    const windowHandling = clients.matchAll({
        type: 'window',
        includeUncontrolled: true
    }).then(function(clientList) {
        const targetUrl = new URL(urlToOpen, self.location.origin).href;
        
        // 既に開かれているウィンドウがあるかチェック
        for (let i = 0; i < clientList.length; i++) {
            const client = clientList[i];
            const clientUrl = new URL(client.url);
            const targetUrlObj = new URL(targetUrl);
            
            // 同じパスのウィンドウがあればフォーカス
            if (clientUrl.pathname === targetUrlObj.pathname && 'focus' in client) {
                return client.focus().then(() => {
                    // メッセージを送信してページを更新
                    return client.postMessage({
                        type: 'NOTIFICATION_CLICKED',
                        data: data
                    });
                });
            }
        }
        
        // 新しいウィンドウを開く
        if (clients.openWindow) {
            return clients.openWindow(targetUrl);
        }
    });

    event.waitUntil(Promise.all([trackClick, windowHandling]));
});

// 通知が閉じられた時のイベント
self.addEventListener('notificationclose', function(event) {
    console.log('Notification closed:', event);
    
    const data = event.notification.data || {};
    
    // 閉じられたことを追跡
    event.waitUntil(
        fetch('/api/notifications/closed', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                notification_id: data.id,
                timestamp: new Date().toISOString()
            })
        }).catch(error => {
            console.warn('Failed to track notification close:', error);
        })
    );
});

// バックグラウンド同期
self.addEventListener('sync', function(event) {
    console.log('Background sync triggered:', event.tag);
    
    if (event.tag === 'background-sync') {
        event.waitUntil(
            // バックグラウンドでのデータ同期処理
            syncData().catch(error => {
                console.error('Background sync failed:', error);
            })
        );
    }
});

// メッセージイベント（ページからの通信）
self.addEventListener('message', function(event) {
    console.log('Message received:', event.data);
    
    const { type, payload } = event.data;
    
    switch (type) {
        case 'SKIP_WAITING':
            self.skipWaiting();
            break;
        case 'GET_VERSION':
            event.ports[0].postMessage({ version: SW_VERSION });
            break;
        case 'CLEAR_NOTIFICATIONS':
            self.registration.getNotifications().then(notifications => {
                notifications.forEach(notification => notification.close());
            });
            break;
        default:
            console.log('Unknown message type:', type);
    }
});

// エラーハンドリング
self.addEventListener('error', function(event) {
    console.error('Service Worker error:', event.error);
    
    // エラーをサーバーに報告（オプション）
    fetch('/api/errors/service-worker', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            error: event.error.toString(),
            filename: event.filename,
            lineno: event.lineno,
            colno: event.colno,
            timestamp: new Date().toISOString(),
            userAgent: navigator.userAgent
        })
    }).catch(() => {
        // エラー報告が失敗しても何もしない
    });
});

// 未処理の Promise 拒否をキャッチ
self.addEventListener('unhandledrejection', function(event) {
    console.error('Unhandled promise rejection in SW:', event.reason);
    
    // 必要に応じてエラー報告
    fetch('/api/errors/unhandled-rejection', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            reason: event.reason.toString(),
            timestamp: new Date().toISOString()
        })
    }).catch(() => {
        // エラー報告が失敗しても何もしない
    });
});

// ユーティリティ関数
async function syncData() {
    try {
        // バックグラウンドでの同期処理
        const response = await fetch('/api/sync');
        const data = await response.json();
        console.log('Data synced successfully:', data);
        return data;
    } catch (error) {
        console.error('Sync failed:', error);
        throw error;
    }
}

// デバッグ用ログ
console.log(`Service Worker ${SW_VERSION} loaded at:`, new Date().toISOString());
// public/js/notification-updater.js

class NotificationUpdater {
    constructor() {
        this.updateInterval = 1000; // 30秒ごとに更新
        this.isUpdating = false;
        console.log('NotificationUpdater コンストラクタ実行');
        this.init();
        
        // コンストラクタ実行直後にも更新をトリガー
        this.immediateUpdate();
    }

    immediateUpdate() {
        console.log('即座更新を実行');
        // 非同期で即座に実行
        Promise.resolve().then(() => {
            this.updateNotifications();
        });
    }

    init() {
        // ページロード時に即座に更新（遅延なし）
        setTimeout(() => {
            this.updateNotifications();
        }, 0);
        
        // 定期的に更新を開始
        this.startPeriodicUpdates();
        
        // ページがアクティブになった時に更新
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                this.updateNotifications();
            }
        });
    }

    startPeriodicUpdates() {
        setInterval(() => {
            if (!document.hidden && !this.isUpdating) {
                this.updateNotifications();
            }
        }, this.updateInterval);
    }

    async updateNotifications() {
        if (this.isUpdating) return;
        this.isUpdating = true;

        try {
            console.log('通知の更新を開始...');
            const response = await fetch('/api/notifications/count', {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            console.log('レスポンスステータス:', response.status);
            console.log('レスポンスURL:', response.url);

            if (!response.ok) {
                const errorText = await response.text();
                console.error('エラーレスポンス:', errorText);
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();
            console.log('取得したデータ:', data);
            console.log('データの型:', typeof data);
            console.log('pending_matching_count の値:', data.pending_matching_count);
            console.log('pending_matching_count の型:', typeof data.pending_matching_count);
            
            this.updateBadges(data);
        } catch (error) {
            console.error('通知の更新に失敗しました:', error);
            console.error('エラーの詳細:', error.message);
        } finally {
            this.isUpdating = false;
        }
    }

    updateBadges(data) {
        console.log('=== バッジ更新処理開始 ===');
        console.log('受信データ:', data);
        console.log('unread_message_count:', data.unread_message_count);
        console.log('pending_matching_count:', data.pending_matching_count);
        
        // メッセージ通知バッジの更新
        this.updateMessageBadge(data.unread_message_count || 0);
        
        // マッチング通知バッジの更新（pending_matching_countを使用）
        this.updateMatchingBadge(data.pending_matching_count || 0);
        
        console.log('=== バッジ更新処理完了 ===');
    }

    updateMessageBadge(messageCount) {
        console.log('メッセージバッジ更新開始 - count:', messageCount);
        const messageBadge = document.querySelector('.message-notification-badge');
        console.log('既存のメッセージバッジ要素:', messageBadge);
        
        if (messageCount > 0) {
            if (messageBadge) {
                console.log('既存バッジを更新:', messageCount);
                // テキストコンテンツを更新（visually-hiddenを保持）
                const hiddenSpan = messageBadge.querySelector('.visually-hidden');
                messageBadge.innerHTML = `${messageCount}`;
                if (hiddenSpan) {
                    messageBadge.appendChild(hiddenSpan);
                } else {
                    messageBadge.innerHTML += `<span class="visually-hidden">未読メッセージ</span>`;
                }
                messageBadge.style.display = 'inline';
            } else {
                console.log('新しいバッジを作成:', messageCount);
                this.createMessageBadge(messageCount);
            }
        } else {
            console.log('メッセージカウントが0のためバッジを非表示');
            if (messageBadge) {
                messageBadge.style.display = 'none';
            }
        }
    }

    updateMatchingBadge(matchingCount) {
        console.log('マッチングバッジ更新開始 - count:', matchingCount);
        const matchingBadge = document.querySelector('.matching-notification-badge');
        console.log('既存のマッチングバッジ要素:', matchingBadge);
        
        if (matchingCount > 0) {
            if (matchingBadge) {
                console.log('既存バッジを更新:', matchingCount);
                // テキストコンテンツを更新（visually-hiddenを保持）
                const hiddenSpan = matchingBadge.querySelector('.visually-hidden');
                matchingBadge.innerHTML = `${matchingCount}`;
                if (hiddenSpan) {
                    matchingBadge.appendChild(hiddenSpan);
                } else {
                    matchingBadge.innerHTML += `<span class="visually-hidden">未確認マッチング</span>`;
                }
                matchingBadge.style.display = 'inline';
            } else {
                console.log('新しいバッジを作成:', matchingCount);
                this.createMatchingBadge(matchingCount);
            }
        } else {
            console.log('マッチングカウントが0のためバッジを非表示');
            if (matchingBadge) {
                matchingBadge.style.display = 'none';
            }
        }
    }

    createMessageBadge(count) {
        // IDで直接取得を試行
        let messageLink = document.querySelector('#message-nav-link');
        
        // IDがない場合は従来の方法で取得
        if (!messageLink) {
            messageLink = document.querySelector('a[href*="/matching/history"]');
            messageLink = Array.from(document.querySelectorAll('a')).find(link => 
                link.querySelector('.bi-chat-dots-fill') && link.href.includes('/matching/history')
            );
        }
        
        if (messageLink) {
            const badge = document.createElement('span');
            badge.className = 'badge rounded-pill bg-danger ms-1 position-relative message-notification-badge';
            badge.style.cssText = 'top: -5px; left: 0px;';
            badge.innerHTML = `${count}<span class="visually-hidden">未読メッセージ</span>`;
            messageLink.appendChild(badge);
        } else {
            console.warn('メッセージ通知バッジを追加するリンクが見つかりませんでした');
        }
    }

    createMatchingBadge(count) {
        console.log('マッチングバッジ作成開始 - count:', count);
        
        // IDで直接取得を試行
        let matchingLink = document.querySelector('#matching-nav-link');
        console.log('ID指定でのリンク取得:', matchingLink);
        
        // IDがない場合は従来の方法で取得
        if (!matchingLink) {
            console.log('IDでの取得に失敗、他の方法で検索');
            const matchingLinks = document.querySelectorAll('a[href*="/matching"]');
            console.log('マッチング関連リンク数:', matchingLinks.length);
            
            matchingLink = Array.from(matchingLinks).find(link => {
                const hasThumbIcon = link.querySelector('.bi-hand-index-thumb-fill');
                const hasHeartIcon = link.querySelector('.bi-heart-fill');
                const hasMatchingText = link.textContent.includes('マッチング');
                console.log('リンク検査:', {
                    href: link.href,
                    hasThumbIcon: !!hasThumbIcon,
                    hasHeartIcon: !!hasHeartIcon,
                    hasMatchingText: hasMatchingText,
                    textContent: link.textContent.trim()
                });
                return hasThumbIcon || hasHeartIcon || hasMatchingText;
            });
        }
        
        if (matchingLink) {
            console.log('マッチングリンクが見つかりました:', matchingLink);
            console.log('リンクのhref:', matchingLink.href);
            console.log('リンクのテキスト:', matchingLink.textContent);
            
            // 既存のバッジをチェック
            const existingBadge = matchingLink.querySelector('.matching-notification-badge');
            if (existingBadge) {
                console.log('既存のバッジが見つかりました、削除します');
                existingBadge.remove();
            }
            
            const badge = document.createElement('span');
            badge.className = 'badge rounded-pill bg-warning text-dark ms-1 position-relative matching-notification-badge';
            badge.style.cssText = 'top: -5px; left: 0px;';
            badge.innerHTML = `${count}<span class="visually-hidden">未確認マッチング</span>`;
            matchingLink.appendChild(badge);
            console.log('マッチングバッジを作成しました:', badge);
        } else {
            console.error('マッチング通知バッジを追加するリンクが見つかりませんでした');
            // 全てのaタグを調査
            const allLinks = document.querySelectorAll('a');
            console.log('全リンク数:', allLinks.length);
            allLinks.forEach((link, index) => {
                if (link.textContent.includes('マッチング') || link.href.includes('matching')) {
                    console.log(`リンク${index}:`, {
                        href: link.href,
                        text: link.textContent.trim(),
                        innerHTML: link.innerHTML
                    });
                }
            });
        }
    }

    // 手動で更新をトリガーする関数（メッセージ送信後やマッチング後などに使用）
    triggerUpdate() {
        this.updateNotifications();
    }

    // 特定の通知タイプのみ更新
    async updateSpecificNotification(type) {
        try {
            const data = await this.fetchNotificationData();
            if (type === 'message') {
                this.updateMessageBadge(data.unread_message_count || 0);
            } else if (type === 'matching') {
                this.updateMatchingBadge(data.pending_matching_count || 0);
            }
        } catch (error) {
            console.error(`${type}通知の更新に失敗しました:`, error);
        }
    }

    async fetchNotificationData() {
        const response = await fetch('/api/notifications/count', {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        return await response.json();
    }
}

// DOMが読み込まれた後に初期化
document.addEventListener('DOMContentLoaded', () => {
    // ログインユーザーのみ実行
    if (document.querySelector('meta[name="csrf-token"]')) {
        console.log('NotificationUpdater を初期化中...');
        window.notificationUpdater = new NotificationUpdater();
        
        // 追加の初期化チェック（DOM要素が完全に読み込まれるまで待機）
        setTimeout(() => {
            if (window.notificationUpdater) {
                console.log('初期化完了後の即座更新を実行');
                window.notificationUpdater.updateNotifications();
            }
        }, 100);
    }
});

// ページが完全に読み込まれた後にも実行（画像等の読み込み完了後）
window.addEventListener('load', () => {
    if (window.notificationUpdater) {
        console.log('ページ完全読み込み後の更新を実行');
        window.notificationUpdater.updateNotifications();
    }
});

// グローバルに関数を公開
window.updateNotifications = () => {
    if (window.notificationUpdater) {
        window.notificationUpdater.triggerUpdate();
    }
};

window.updateMessageNotifications = () => {
    if (window.notificationUpdater) {
        window.notificationUpdater.updateSpecificNotification('message');
    }
};

window.updateMatchingNotifications = () => {
    if (window.notificationUpdater) {
        window.notificationUpdater.updateSpecificNotification('matching');
    }
};
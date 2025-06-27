// resources/js/app.js

require('./bootstrap');

import { initializeApp } from "firebase/app";
import { getMessaging, getToken, onMessage, deleteToken } from "firebase/messaging";




const firebaseConfig = {
  apiKey: "AIzaSyC4ZsFPI5VYJvQm7Sv3siM8VIavlp76C9U",
  authDomain: "skillswap-297c1.firebaseapp.com",
  projectId: "skillswap-297c1",
  storageBucket: "skillswap-297c1.firebasestorage.app",
  messagingSenderId: "458391938988",
  appId: "1:458391938988:web:a847baf1a825ce375ffc09",
  measurementId: "G-GP1NF02XC0"
};

const app = initializeApp(firebaseConfig);
const messaging = getMessaging(app);

const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const vapidPublicKey = document.querySelector('meta[name="vapid-public-key"]').getAttribute('content');

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/firebase-messaging-sw.js')
            .then(registration => {
                console.log('Service Worker registered with scope:', registration.scope);

                Notification.requestPermission().then((permission) => {
                    if (permission === 'granted') {
                        console.log('Notification permission granted.');

                        getToken(messaging, {
                            vapidKey: vapidPublicKey,
                            serviceWorkerRegistration: registration // ← 重要！！
                        }).then((currentToken) => {
                            if (currentToken) {
                                console.log('FCM Registration Token:', currentToken);
                                sendTokenToServer(currentToken);
                            } else {
                                console.warn('No registration token available. Request permission to generate one.');
                            }
                        }).catch((err) => {
                            console.error('An error occurred while retrieving token. ', err);
                        });

                        onMessage(messaging, (payload) => {
                            console.log('Message received. ', payload);
                            const notificationTitle = payload.notification.title;
                            const notificationOptions = {
                                body: payload.notification.body,
                                icon: '/favicon.ico'
                            };
                            new Notification(notificationTitle, notificationOptions);
                        });
                    } else {
                        console.warn('Unable to get permission to notify.');
                    }
                });

            })
            .catch(error => {
                console.error('Service Worker registration failed:', error);
            });
    });
}

function sendTokenToServer(token) {
    fetch('/webpush/subscribe', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ token: token })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('FCM Token sent to server and saved.');
        } else {
            console.error('Failed to save FCM Token on server:', data.message);
        }
    })
    .catch(error => {
        console.error('Error sending FCM Token to server:', error);
    });
}

function unsubscribe() {
    getToken(messaging).then((currentToken) => {
        if (currentToken) {
            deleteToken(messaging).then(() => {
                console.log('Token deleted.');
                fetch('/webpush/unsubscribe', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ token: currentToken })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('FCM Token removed from server.');
                    } else {
                        console.error('Failed to remove FCM Token from server:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error removing FCM Token from server:', error);
                });
            }).catch((err) => {
                console.error('Unable to delete token. ', err);
            });
        }
    }).catch((err) => {
        console.error('Error retrieving token to unsubscribe. ', err);
    });
}

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
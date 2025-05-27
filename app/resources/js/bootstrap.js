import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window._ = require('lodash');

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

window.axios = require('axios');

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

// import Echo from 'laravel-echo';

// window.Pusher = require('pusher-js');

// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: process.env.MIX_PUSHER_APP_KEY,
//     cluster: process.env.MIX_PUSHER_APP_CLUSTER,
//     forceTLS: true
// });

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    // .env ファイルの APP_KEY を Laravel Mix/Vite 経由で取得
    key: process.env.MIX_PUSHER_APP_KEY || process.env.VITE_PUSHER_APP_KEY || 'your_app_key',
    cluster: process.env.MIX_PUSHER_APP_CLUSTER || process.env.VITE_PUSHER_APP_CLUSTER || 'mt1', // ★この行を追加または修正★
    wsHost: window.location.hostname, // ブラウザから見たホスト名 (例: localhost)
    wsPort: 6001, // Echo Server のポート
    forceTLS: false, // HTTPS でない場合は false
    disableStats: true,
    enabledTransports: ['ws', 'wss'],
    // authEndpoint: '/broadcasting/auth' // デフォルトを使用
});

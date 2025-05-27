@extends('layouts.app')

@section('title', 'メッセージ')

@section('content')
<h1>メッセージ（マッチングID: {{ $matching->id }})</h1>

<div id="messages-container" style="border:1px solid #ccc; padding:10px; max-height:400px; overflow-y:auto;">
    @forelse ($messages as $msg)
        <div class="message-item" style="margin-bottom: 10px; {{ $msg->sender_id === Auth::id() ? 'text-align: right;' : 'text-align: left;' }}">
            @if ($msg->sender_id !== Auth::id())
                <strong>{{ $msg->sender->name }}:</strong><br>
            @else
                <strong>あなた:</strong><br>
            @endif
            {{ $msg->content }}<br>
            <small>{{ $msg->created_at->format('Y-m-d H:i') }}</small>
        </div>
    @empty
        <p id="no-messages-yet">まだメッセージはありません。</p>
    @endforelse
</div>

<form id="message-form" style="margin-top:20px;">
    @csrf
    <textarea name="content" id="message-content" rows="3" cols="60" required placeholder="メッセージを入力してください"></textarea><br>
    <button type="submit">送信</button>
</form>
@endsection

@push('scripts')
<script>
    // DOMContentLoaded が発火してから Echo の購読やフォームのリスナーをアタッチ
    document.addEventListener('DOMContentLoaded', function() {
        const matchingId = {{ $matching->id }};
        const currentUserId = {{ Auth::id() }};
        const currentUserName = "{{ Auth::user()->name }}"; // 自分の名前も取得

        // ここで window.Echo が確実に存在することを確認
        if (typeof window.Echo === 'undefined' || window.Echo === null) {
            console.error("Laravel Echo is not initialized. Check app.js and its dependencies.");
            // Echoがない場合、リアルタイム機能は諦めるか、エラー処理を記述
        } else {
            // Laravel Echo を使ってチャネルを購読し、イベントをリッスン
            window.Echo.private(`matching.${matchingId}`)
                .listen('MessageSent', (e) => {
                    console.log('新着メッセージ:', e);
                    const messagesContainer = document.getElementById('messages-container');
                    const noMessagesYet = document.getElementById('no-messages-yet');

                    if (noMessagesYet) {
                        noMessagesYet.remove();
                    }

                    const messageDiv = document.createElement('div');
                    messageDiv.classList.add('message-item');
                    messageDiv.style.marginBottom = '10px';

                    let senderName = e.sender_name;
                    let textAlign = 'left';

                    if (e.sender_id === currentUserId) {
                        senderName = 'あなた';
                        textAlign = 'right';
                    }
                    messageDiv.style.textAlign = textAlign;

                    messageDiv.innerHTML = `
                        <strong>${senderName}:</strong><br>
                        ${e.content}<br>
                        <small>${e.created_at}</small>
                    `;
                    messagesContainer.appendChild(messageDiv);
                    messagesContainer.scrollTop = messagesContainer.scrollHeight;
                });
        }

        // フォームの非同期送信
        document.getElementById('message-form').addEventListener('submit', function(event) {
            event.preventDefault(); // デフォルトのフォーム送信をキャンセル

            const contentInput = document.getElementById('message-content');
            const messageContent = contentInput.value;

            if (messageContent.trim() === '') {
                alert('メッセージを入力してください。');
                return;
            }

            fetch('/message/' + matchingId, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ content: messageContent })
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => { throw err; });
                }
                return response.json();
            })
            .then(data => {
                console.log('メッセージ送信成功:', data);
                const messagesContainer = document.getElementById('messages-container');
                const noMessagesYet = document.getElementById('no-messages-yet');

                if (noMessagesYet) {
                    noMessagesYet.remove();
                }

                const messageDiv = document.createElement('div');
                messageDiv.classList.add('message-item');
                messageDiv.style.marginBottom = '10px';
                messageDiv.style.textAlign = 'right';

                const now = new Date();
                const year = now.getFullYear();
                const month = (now.getMonth() + 1).toString().padStart(2, '0');
                const day = now.getDate().toString().padStart(2, '0');
                const hours = now.getHours().toString().padStart(2, '0');
                const minutes = now.getMinutes().toString().padStart(2, '0');
                const formattedTime = `${year}-${month}-${day} ${hours}:${minutes}`;

                messageDiv.innerHTML = `
                    <strong>あなた:</strong><br>
                    ${messageContent}<br>
                    <small>${formattedTime}</small>
                `;
                messagesContainer.appendChild(messageDiv);
                messagesContainer.scrollTop = messagesContainer.scrollHeight;

                contentInput.value = '';
            })
            .catch(error => {
                console.error('メッセージ送信エラー:', error);
                alert('メッセージの送信に失敗しました。');
                if (error.message) {
                    alert('エラー詳細: ' + error.message);
                }
            });
        });
    }); // DOMContentLoaded の閉じタグ

    // ページロード時に一番下までスクロール（これはDOMContentLoadedの外でも可）
    window.onload = function() {
        const messagesContainer = document.getElementById('messages-container');
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    };
</script>
@endpush
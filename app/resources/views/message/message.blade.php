@extends('layouts.app')

@section('title', 'メッセージ')

@section('content')
<div class="container my-4"> {{-- コンテナを追加し、上下に余白 --}}

    {{-- フラッシュメッセージの表示エリア - 削除（レイアウトファイルで表示される） --}}
    {{-- 
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    --}}

    {{-- ヘッダー部分 --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">メッセージ（マッチングID: {{ $matching->id }})</h1> {{-- 見出しのサイズ調整 --}}
        @php
            $partner = null;
            if (Auth::id() === $matching->offerUser->id) {
                $partner = $matching->requestUser; // 自分が提供者なら、相手はリクエスト者
            } elseif (Auth::id() === $matching->requestUser->id) {
                $partner = $matching->offerUser; // 自分がリクエスト者なら、相手は提供者
            }
        @endphp

        @if ($partner)
            <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#reportModal"
                data-reportable-type="App\Models\User"
                data-reportable-id="{{ $partner->id }}"
                data-reported-user-id="{{ $partner->id }}">
                <i class="bi bi-flag me-1"></i> {{ $partner->name }}さんを通報
            </button>
        @endif
    </div>

    {{-- メッセージ履歴のカード --}}
    <div class="card mb-4 shadow-sm"> {{-- カードコンポーネントと影、下余白 --}}
        <div class="card-header bg-primary text-white">
            メッセージ履歴
        </div>
        <div class="card-body p-3" id="messages-container" style="max-height: 400px; overflow-y: auto;">
            @forelse ($messages as $msg)
                <div class="message-item d-flex {{ $msg->sender_id === Auth::id() ? 'justify-content-end' : 'justify-content-start' }} mb-2">
                    <div class="card {{ $msg->sender_id === Auth::id() ? 'bg-info text-white' : 'bg-light' }}" style="max-width: 75%;">
                        <div class="card-body py-2 px-3">
                            <strong class="d-block mb-1">{{ $msg->sender_id === Auth::id() ? 'あなた' : $msg->sender->name }}:</strong>
                            <p class="mb-0">{{ $msg->content }}</p>
                            <small class="text-muted d-block text-end mt-1">{{ $msg->created_at->format('Y-m-d H:i') }}</small>
                        </div>
                    </div>
                </div>
            @empty
                <p id="no-messages-yet" class="text-center text-muted">まだメッセージはありません。</p>
            @endforelse
        </div>
    </div>

    {{-- メッセージ入力フォームのカード --}}
    <div class="card shadow-sm">
        <div class="card-body">
            <form id="message-form">
                @csrf
                <div class="mb-3">
                    <label for="message-content" class="form-label visually-hidden">メッセージ入力</label>
                    <textarea class="form-control" name="content" id="message-content" rows="3" required placeholder="メッセージを入力してください"></textarea>
                </div>
                <div class="d-grid gap-2"> {{-- ボタンをグリッドで配置（幅いっぱい） --}}
                    <button type="submit" class="btn btn-primary btn-lg">送信</button>
                </div>
            </form>
        </div>
    </div>

</div> {{-- .container の閉じタグ --}}


{{-- 通報モーダル（変更なし、または前回の修正済みコードを使用） --}}
<div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="reportForm" method="POST" action="{{ route('reports.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="reportModalLabel">コンテンツを通報する</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="reportable_type" id="reportable_type">
                    <input type="hidden" name="reportable_id" id="reportable_id">
                    <input type="hidden" name="reported_user_id" id="reported_user_id">

                    <div class="mb-3">
                        <label for="reason_id" class="form-label">通報理由（大まかな選択）</label>
                        <select class="form-select" id="reason_id" name="reason_id" required>
                            <option value="">選択してください</option>
                            @foreach(\App\Models\ReportReason::topLevel()->get() as $reason)
                                <option value="{{ $reason->id }}">{{ $reason->reason_text }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3" id="sub_reason_container" style="display: none;">
                        <label for="sub_reason_id" class="form-label">詳細な理由</label>
                        <select class="form-select" id="sub_reason_id" name="sub_reason_id">
                            <option value="">選択してください</option>
                        </select>
                        <div id="loadingSubReasons" style="display: none; margin-top: 5px;">
                            <div class="spinner-border spinner-border-sm text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <span>読み込み中...</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="comment" class="form-label">具体的な状況を記入してください (任意)</label>
                        <textarea class="form-control" id="comment" name="comment" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <button type="submit" class="btn btn-danger" id="submitReportButton" style="display: none;">通報を送信</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const matchingId = {{ $matching->id }};
        const currentUserId = {{ Auth::id() }};
        const currentUserName = "{{ Auth::user()->name }}";

        // メッセージコンテナのスクロールを一番下にするための関数
        function scrollToBottomOfMessages() {
            const messagesContainer = document.getElementById('messages-container');
            if (messagesContainer) {
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }
        }

        if (typeof window.Echo === 'undefined' || window.Echo === null) {
            console.error("Laravel Echo is not initialized. Check app.js and its dependencies.");
        } else {
            window.Echo.private(`matching.${matchingId}`)
                .listen('MessageSent', (e) => {
                    console.log('新着メッセージ:', e);
                    const messagesContainer = document.getElementById('messages-container');
                    const noMessagesYet = document.getElementById('no-messages-yet');

                    if (noMessagesYet) {
                        noMessagesYet.remove();
                    }

                    const messageDiv = document.createElement('div');
                    messageDiv.classList.add('message-item', 'd-flex', e.sender_id === currentUserId ? 'justify-content-end' : 'justify-content-start', 'mb-2');

                    let senderName = e.sender_name;
                    let cardBgClass = 'bg-light';
                    let textColorClass = '';

                    if (e.sender_id === currentUserId) {
                        senderName = 'あなた';
                        cardBgClass = 'bg-info text-white'; // 送信メッセージの色
                        textColorClass = 'text-white'; // 送信メッセージのテキスト色
                    }
                    
                    messageDiv.innerHTML = `
                        <div class="card ${cardBgClass}" style="max-width: 75%;">
                            <div class="card-body py-2 px-3">
                                <strong class="d-block mb-1">${senderName}:</strong>
                                <p class="mb-0">${e.content}</p>
                                <small class="text-muted d-block text-end mt-1 ${e.sender_id === currentUserId ? 'text-white-50' : ''}">${e.created_at}</small>
                            </div>
                        </div>
                    `;
                    messagesContainer.appendChild(messageDiv);
                    scrollToBottomOfMessages(); // 新しいメッセージが来たらスクロール
                });
        }

        document.getElementById('message-form').addEventListener('submit', function(event) {
            event.preventDefault();

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
                messageDiv.classList.add('message-item', 'd-flex', 'justify-content-end', 'mb-2'); // 自分のメッセージは右寄せ

                const now = new Date();
                const year = now.getFullYear();
                const month = (now.getMonth() + 1).toString().padStart(2, '0');
                const day = now.getDate().toString().padStart(2, '0');
                const hours = now.getHours().toString().padStart(2, '0');
                const minutes = now.getMinutes().toString().padStart(2, '0');
                const formattedTime = `${year}-${month}-${day} ${hours}:${minutes}`;

                messageDiv.innerHTML = `
                    <div class="card bg-info text-white" style="max-width: 75%;">
                        <div class="card-body py-2 px-3">
                            <strong class="d-block mb-1">あなた:</strong>
                            <p class="mb-0">${messageContent}</p>
                            <small class="text-white-50 d-block text-end mt-1">${formattedTime}</small>
                        </div>
                    </div>
                `;
                messagesContainer.appendChild(messageDiv);
                scrollToBottomOfMessages(); // 送信後スクロール

                contentInput.value = ''; // 入力欄をクリア
            })
            .catch(error => {
                console.error('メッセージ送信エラー:', error);
                alert('メッセージの送信に失敗しました。');
                if (error.message) {
                    alert('エラー詳細: ' + error.message);
                }
            });
        });

        // ページロード時にスクロール
        scrollToBottomOfMessages();


        //ここから通報の内容
        const reportModal = document.getElementById('reportModal');
        const reasonSelect = document.getElementById('reason_id');
        const subReasonContainer = document.getElementById('sub_reason_container');
        const subReasonSelect = document.getElementById('sub_reason_id');
        const submitReportButton = document.getElementById('submitReportButton');
        const loadingSubReasons = document.getElementById('loadingSubReasons');

        // 初期状態では送信ボタンと詳細理由コンテナを非表示
        submitReportButton.style.display = 'none';
        subReasonContainer.style.display = 'none';

        // 通報理由（大まかな選択）が変更されたときの処理
        reasonSelect.addEventListener('change', function () {
            const selectedReasonId = this.value;

            // 子理由をクリア
            subReasonSelect.innerHTML = '<option value="">選択してください</option>';
            // 詳細理由コンテナを非表示に戻し、送信ボタンも非表示にする
            subReasonContainer.style.display = 'none';
            submitReportButton.style.display = 'none';
            subReasonSelect.removeAttribute('required');

            if (selectedReasonId) {
                loadingSubReasons.style.display = 'block'; // ローディング表示
                fetch(`/api/report-reasons/${selectedReasonId}/children`)
                    .then(response => response.json())
                    .then(data => {
                        loadingSubReasons.style.display = 'none'; // ローディング非表示

                        if (data.length > 0) {
                            const fragment = document.createDocumentFragment();
                            data.forEach(subReason => {
                                const option = document.createElement('option');
                                option.value = subReason.id;
                                option.textContent = subReason.reason_text;
                                fragment.appendChild(option);
                            });
                            subReasonSelect.appendChild(fragment);
                            subReasonContainer.style.display = 'block'; // 子理由のセレクトボックスを表示
                            subReasonSelect.setAttribute('required', 'required');
                        } else {
                            subReasonContainer.style.display = 'none';
                            subReasonSelect.removeAttribute('required');
                            // 子理由がない場合は、大まかな理由が選択されていれば即座に送信ボタンを表示
                            submitReportButton.style.display = 'inline-block';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching sub reasons:', error);
                        alert('詳細な理由の取得に失敗しました。');
                        loadingSubReasons.style.display = 'none';
                        subReasonContainer.style.display = 'none';
                        subReasonSelect.removeAttribute('required');
                        submitReportButton.style.display = 'none';
                    });
            }
        });

        // 詳細な理由が選択されたときの処理
        subReasonSelect.addEventListener('change', function() {
            if (this.value) { // 何らかのオプションが選択された場合
                submitReportButton.style.display = 'inline-block'; // 送信ボタンを表示
            } else {
                // 大まかな理由が選択されており、かつ詳細理由がない場合に送信ボタンを表示するロジックを考慮
                // 現在は大まかな理由が選択され、かつ子理由がない場合のみ、送信ボタンが表示される
                // 子理由があるが「選択してください」に戻した場合は非表示
                const selectedReasonId = reasonSelect.value;
                if (selectedReasonId && subReasonSelect.options.length <= 1) { // 選択肢が「選択してください」のみの場合
                    submitReportButton.style.display = 'inline-block';
                } else {
                    submitReportButton.style.display = 'none';
                }
            }
        });


        // モーダルが表示される直前に、data-属性から値を取得してフォームにセット
        reportModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const reportableType = button.getAttribute('data-reportable-type');
            const reportableId = button.getAttribute('data-reportable-id');
            const reportedUserId = button.getAttribute('data-reported-user-id');

            reportModal.querySelector('#reportable_type').value = reportableType;
            reportModal.querySelector('#reportable_id').value = reportableId;
            reportModal.querySelector('#reported_user_id').value = reportedUserId;

            // モーダルが開くときにフォームをリセットし、初期状態に戻す
            const reportForm = document.getElementById('reportForm'); // フォーム要素を取得
            reportForm.reset();
            reasonSelect.value = ''; // 明示的にリセット
            subReasonSelect.innerHTML = '<option value="">選択してください</option>'; // 明示的にリセット
            subReasonContainer.style.display = 'none';
            subReasonSelect.removeAttribute('required');
            submitReportButton.style.display = 'none';
            loadingSubReasons.style.display = 'none';
        });

        // モーダルが完全に閉じられたときにフォームをリセット
        reportModal.addEventListener('hidden.bs.modal', function () {
            const reportForm = document.getElementById('reportForm'); // フォーム要素を取得
            reportForm.reset();
            // JavaScriptで制御している表示状態もリセット
            subReasonContainer.style.display = 'none';
            subReasonSelect.innerHTML = '<option value="">選択してください</option>';
            subReasonSelect.removeAttribute('required');
            submitReportButton.style.display = 'none';
            loadingSubReasons.style.display = 'none';
        });
    });
</script>
@endpush

@push('styles')
<style>
/* カスタムCSS（public/css/app.css に追加すると良い） */


.skill-card-link {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}
.skill-card-link:hover {
    transform: translateY(-8px); /* ホバーで少し浮き上がる */
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.7); /* ホバーで影を濃くする */
}


/* あなたの新しい :root 変数定義をここに貼り付けます */
:root {
    --skillswap-primary:rgb(110, 161, 209); /* 新しいメインブルー（少し落ち着いた青） */
    --skillswap-primary-dark:rgb(121, 165, 203); /* メインカラーより濃い青（ホバー用やアクセントに） */
    --skillswap-text-light: #ffffff; /* 明るい背景用テキスト（白） */
    --skillswap-text-dark: #333333; /* 暗い背景用テキスト（濃いグレー） */
    --skillswap-bg-light: #f8f9fa; /* 薄い背景色 */
    --skillswap-border: #dee2e6; /* ボーダー色 */

    /* ステータス・警告に限定して使用する色（青と赤に集約） */
    --status-success: var(--skillswap-primary-dark); /* 承認ボタン: ブランドの濃い青 */
    --status-success-light: #C5E1F7; /* 承認バッジ/背景用: 薄い青 */

    --status-warning: #E26B6B; /* 申請取り消しボタン: 赤のバリエーション（少し明るめ） */
    --status-warning-dark: #CD5C5C;
    --status-warning-light: #F8D7DA; /* 申請取り消しバッジ/背景用: 薄い赤 */

    --status-danger:rgb(211, 103, 114); /* 拒否ボタン/警告: Bootstrapの赤に近い */
    --status-danger-dark: #c82333;
    --status-danger-light: #f8d7da; /* 薄い赤 */

    --status-info: #6c757d; /* 完了ボタン/その他情報ボタン: Bootstrapのミディアムグレー */
    --status-info-dark: #5a6268;
    --status-info-light: #e2e6ea; /* 完了バッジ/情報アラート背景用: 薄いグレー */

    /* 保留中の状態を示す色 */
    --status-pending: #6c757d; /* 保留中バッジ: Bootstrapのミディアムグレー */
    --status-pending-light: #e2e6ea; /* 保留中のバッジ背景用 */
}

/* 汎用的なリンクカラー */
a {
    color: var(--skillswap-primary-dark); /* リンクは濃い青 */
    text-decoration: none;
}
a:hover {
    color: var(--skillswap-primary); /* ホバー時はメインの青 */
    text-decoration: underline;
}

/* カードの基本的なスタイル */
.card {
    border-radius: 10px;
    border: 1px solid var(--skillswap-border);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
}

.card-body {
    padding: 2.5rem;
}

/* --- カードヘッダーの色の調整 --- */
/* 全体的なカードヘッダーのデフォルト色（.card-header）を濃い青に */
.card-header {
    background-color: var(--skillswap-primary-dark) !important; /* 新しい濃い青 */
    color: var(--skillswap-text-light) !important;
    font-weight: bold;
    border-bottom: 1px solid var(--skillswap-primary-dark) !important;
}

/* ただし、警告カードのヘッダーは赤を保持 */
.card-header.bg-danger {
    background-color: var(--status-danger) !important;
    border-color: var(--status-danger) !important;
}

/* スキル管理ページの特定のカードヘッダーを上書き */
/* 新規スキル登録 (bg-primaryを使用) */
.card-header.bg-primary {
    background-color: var(--skillswap-primary-dark) !important; /* 新しい濃い青 */
    border-color: var(--skillswap-primary-dark) !important;
}

/* 登録済みスキル (bg-successを使用) */
.card-header.bg-success {
    background-color: var(--skillswap-primary-dark) !important; /* 新しい濃い青に統一 */
    border-color: var(--skillswap-primary-dark) !important;
}

/* スキルの一括登録 (bg-infoを使用) */
.card-header.bg-info {
    background-color: var(--status-info) !important; /* グレーに統一 */
    border-color: var(--status-info) !important;
}


/* 編集ボタン（カードヘッダー内のbtn-light） */
.card-header .btn-light {
    background-color: rgba(255, 255, 255, 0.2) !important;
    border-color: rgba(255, 255, 255, 0.3) !important;
    color: var(--skillswap-text-light) !important;
}
.card-header .btn-light:hover {
    background-color: rgba(255, 255, 255, 0.3) !important;
    border-color: rgba(255, 255, 255, 0.4) !important;
}

/* --- モーダルの調整 --- */
.modal-header {
    /* 修正前: background-color: var(--skillswap-primary-dark); */
    background-color: var(--skillswap-primary)  !important;; /* スキル管理ページのカードヘッダーより少し明るい青に */
    color: var(--skillswap-text-light);
    border-bottom: 1px solid var(--skillswap-primary); /* ボーダーも合わせて調整 */
}
.modal-header .btn-close {
    filter: invert(1); /* 白いボタンアイコンを反転して見やすくする */
}

/* --- ボタンの調整 --- */
/* メインのアクションボタン (btn-primary) */
.btn-primary {
    background-color: var(--skillswap-primary); /* メインの青 */
    border-color: var(--skillswap-primary);
    color: var(--skillswap-text-light);
    transition: background-color 0.2s ease, border-color 0.2s ease;
}
.btn-primary:hover {
    background-color: var(--skillswap-primary-dark); /* ホバー時は濃い青 */
    border-color: var(--skillswap-primary-dark);
    color: var(--skillswap-text-light);
}

/* btn-secondary の調整（落ち着いたグレー） */
.btn-secondary {
    background-color: var(--status-info); /* status-infoを共通のグレーとして使用 */
    border-color: var(--status-info);
    color: var(--skillswap-text-light);
}
.btn-secondary:hover {
    background-color: var(--status-info-dark);
    border-color: var(--status-info-dark);
}

/* btn-info の調整（グレー） */
.btn-info {
    background-color: var(--status-info); /* グレー */
    border-color: var(--status-info);
    color: var(--skillswap-text-light);
    transition: background-color 0.2s ease, border-color 0.2s ease;
}
.btn-info:hover {
    background-color: var(--status-info-dark); /* ホバー時は少し濃いグレー */
    border-color: var(--status-info-dark);
}


/* Googleログインボタンは現状維持が良いでしょう */
.btn-google {
    background-color: #DB4437;
    border-color: #DB4437;
    color: var(--skillswap-text-light);
    transition: background-color 0.2s ease-in-out, border-color 0.2s ease-in-out;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}
.btn-google:hover {
    background-color: #C23326;
    border-color: #C23326;
    color: var(--skillswap-text-light);
}

/* --- アラート/メッセージの調整 --- */
/* セッション成功メッセージ (alert-success) */
.alert.alert-success {
    background-color: var(--status-success-light) !important; /* 薄い青 */
    border-color: var(--skillswap-primary) !important; /* メインの青のボーダー */
    color: var(--skillswap-text-dark) !important; /* 濃いめのテキスト */
}

/* バリデーションエラーメッセージ (alert-danger) */
.alert.alert-danger {
    background-color: var(--status-danger-light) !important; /* 薄い赤 */
    border-color: var(--status-danger) !important;
    color: #721c24 !important; /* Bootstrap dangerのテキスト色 */
}

/* まだ登録済みのスキルはありません (alert-info) */
.alert.alert-info {
    background-color: var(--status-info-light) !important; /* 薄いグレー */
    border-color: var(--status-info) !important;
    color: var(--skillswap-text-dark) !important; /* 濃いめのテキスト */
    font-weight: bold;
}

/* 管理者からの警告カード内のアラート */
.card-body .alert-danger {
    background-color: var(--status-danger-light) !important; /* 薄い赤 */
    border-color: var(--status-danger) !important;
    color: #721c24 !important; /* Bootstrap dangerのテキスト色 */
}
.card-body .alert-danger .alert-heading {
    color: var(--status-danger) !important;
}
.card-body .btn-outline-danger:hover { /* ユーザー通報時のコメントのボタン */
    background-color: var(--status-danger) !important;
    color: var(--skillswap-text-light) !important;
}


/* --- スキル管理テーブル内のボタンの調整 --- */
/* スキル管理カード内のボタンはプライマリカラーに統一 */
/* 編集ボタン (btn-info) をプライマリカラーの青に */
.edit-skill-btn { /* HTMLで `btn btn-sm btn-info` となっているので、個別のクラスで上書き */
    background-color: var(--skillswap-primary) !important;
    border-color: var(--skillswap-primary) !important;
    color: var(--skillswap-text-light) !important;
}
.edit-skill-btn:hover {
    background-color: var(--skillswap-primary-dark) !important;
    border-color: var(--skillswap-primary-dark) !important;
}

/* 削除ボタン (btn-danger) は既存の赤を維持 */
.delete-skill-btn {
    background-color: var(--status-danger) !important;
    border-color: var(--status-danger) !important;
    color: var(--skillswap-text-light) !important;
}
.delete-skill-btn:hover {
    background-color: var(--status-danger-dark) !important;
    border-color: var(--status-danger-dark) !important;
}

/* スキルカードのホバーエフェクト（変更なし） */
.skill-card-link {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}
.skill-card-link:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15) !important;
}


/* --- バッジの色の調整 --- */
/* ステータスが0（保留中）の場合のバッジスタイル */
.badge.bg-warning { /* 元々bg-warningが使われている箇所（ステータス0） */
    background-color: var(--status-pending) !important; /* 保留中はミディアムグレー */
    color: var(--skillswap-text-light) !important;
}
.badge.bg-success {
    background-color: var(--status-success-light) !important; /* 薄い青 */
    color: var(--skillswap-primary-dark) !important; /* テキストは濃い青 */
}
.badge.bg-danger {
    background-color: var(--status-danger-light) !important; /* 薄い赤 */
    color: var(--status-danger) !important;
}
/* ステータスが完了の場合、bg-infoを使用していると想定 */
.badge.bg-info {
    background-color: var(--status-info-light) !important; /* 薄いグレー */
    color: var(--skillswap-text-dark) !important;
}

/* データエクスポートモーダルのボタンもプライマリカラーに統一 */
#exportHistoryModal .modal-footer .btn-primary {
    background-color: var(--skillswap-primary);
    border-color: var(--skillswap-primary);
    color: var(--skillswap-text-light);
}
#exportHistoryModal .modal-footer .btn-primary:hover {
    background-color: var(--skillswap-primary-dark);
    border-color: var(--skillswap-primary-dark);
}

/* HTMLからのテキスト色指定を調整 */
.text-primary { /* <span class="text-primary"> のスキル名 */
    color: var(--skillswap-primary-dark) !important; /* 濃い青 */
}

.text-success { /* <span class="text-success"> のユーザー名 */
    color: var(--skillswap-text-dark) !important; /* 濃いグレー */
}

#messages-container { /* メッセージリストのコンテナID */
    background-color: var(--skillswap-bg-light) !important; /* 薄い背景色に設定 */
}

/* あなたが送信したメッセージのスタイル */
/* 現在 'bg-info text-white' が適用されていますが、これをブランドの青系に統一 */
.card.bg-info.text-white { /* より具体的にセレクタを指定 */
    background-color: var(--skillswap-primary) !important; /* メインの青 */
    border-color: var(--skillswap-primary) !important;
    color: var(--skillswap-text-light) !important; /* テキスト色は白 */
}
/* ホバー時のスタイル（もしあれば） */
.card.bg-info.text-white:hover {
    background-color: var(--skillswap-primary-dark) !important; /* ホバー時は濃い青 */
    border-color: var(--skillswap-primary-dark) !important;
}


/* 相手から受信したメッセージのスタイル（既に薄いグレーbg-lightなので維持） */
.card.bg-light {
    background-color: #f8f9fa !important; /* Bootstrapのデフォルトbg-light色を明示 */
    border-color: var(--skillswap-border) !important; /* ボーダーはブランドのボーダー色 */
    color: var(--skillswap-text-dark) !important; /* テキストは濃いグレー */
}

</style>
@endpush
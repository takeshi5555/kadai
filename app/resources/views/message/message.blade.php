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
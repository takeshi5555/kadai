@extends('layouts.app')

@section('title', 'マイページ')

@section('content')
@php use Illuminate\Support\Str; @endphp

<div class="container" style="max-width: 960px; margin: 0 auto; padding: 20px;">
    <h1 class="mb-4">{{ $user->name }}さんのマイページ</h1>

{{-- --- ユーザー情報カード --- --}}
<div class="card mb-4 shadow-sm">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h2 class="h5 mb-0">ユーザー情報</h2>
        {{-- 編集ボタンを追加 --}}
        <button type="button" class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#editUserInfoModal">
            <i class="bi bi-pencil-square me-1"></i> 編集
        </button>
    </div>
    <div class="card-body">
        <p><strong>ユーザー名:</strong> {{ $user->name }}</p>
        <p><strong>メールアドレス:</strong> {{ $user->email }}</p>
        <a href="{{ route('password.request') }}" class="btn btn-secondary mt-2">
            パスワードを再設定する
        </a>
    </div>
</div>

{{-- --- ユーザー情報編集モーダル --- --}}
<div class="modal fade" id="editUserInfoModal" tabindex="-1" aria-labelledby="editUserInfoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('mypage.updateUserInfo') }}" method="POST">
                @csrf
                @method('PUT') {{-- PUTメソッドを使用 --}}

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="editUserInfoModalLabel">ユーザー情報を編集</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">ユーザー名</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">メールアドレス</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                        @error('email')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <button type="submit" class="btn btn-primary">更新</button>
                </div>
            </form>
        </div>
    </div>
</div>

    {{-- 未読メッセージ通知 --}}
    @if($unreadMessagesCount > 0)
    <div class="alert alert-info mb-4" style="
        background-color: #e0f7fa;
        border-color: #00bcd4;
        color: #006064;
        padding: 15px;
        border-radius: 5px;
    ">
        <strong>新着メッセージがあります！</strong>
        <p class="mb-2">{{ $unreadMessagesCount }}件の未読メッセージがあります。</p>
        <a href="{{ route('matching.history.index') }}" class="btn btn-primary">
            メッセージ履歴を見る
        </a>
    </div>
    @endif

    {{-- 管理者からの警告カード --}}
@if($unreadWarnings->isNotEmpty() || $readWarnings->isNotEmpty())
<div class="card mb-4 shadow-sm">
    <div class="card-header bg-danger text-white">
        <h2 class="h5 mb-0">あなたへの管理者からの警告</h2>
    </div>
    <div class="card-body">
        @if($unreadWarnings->isEmpty() && $readWarnings->isEmpty())
            {{-- このブロックは、上記の @if 条件によってほとんど表示されなくなるが、念のため残しておく --}}
            <p class="text-muted">現在、あなたへの警告はありません。</p>
        @else
            {{-- 未確認の警告表示 --}}
            @if($unreadWarnings->isNotEmpty())
                <h4 class="h6 mb-3 text-danger">
                    未確認の警告 ({{ $unreadWarnings->count() }} 件)
                </h4>
                
                @foreach($unreadWarnings as $warning)
                    <div class="alert alert-danger mb-3" role="alert">
                        <h5 class="alert-heading h6 mb-1">
                            新しい警告 ({{ ($warning->warned_at ?? $warning->created_at)->format('Y年m月d日 H:i') }})
                        </h5>
                        
                        <p class="mb-1">
                            <strong>管理者からのメッセージ:</strong> {{ $warning->message }}
                        </p>

                        @if($warning->report)
                            @if($warning->report->reason)
                                <p class="mb-1">
                                    <strong>通報カテゴリ:</strong> {{ $warning->report->reason->reason_text }}
                                </p>
                            @endif
                            
                            @if($warning->report->subReason)
                                <p class="mb-1">
                                    <strong>通報詳細:</strong> {{ $warning->report->subReason->reason_text }}
                                </p>
                            @endif
                            
                            @if($warning->report->comment)
                                <p class="mb-1">
                                    <strong>ユーザー通報時のコメント:</strong> {{ $warning->report->comment }}
                                </p>
                            @endif
                        @endif

                        <hr>
                        <form action="{{ route('warning.mark_as_read', $warning) }}" method="POST" class="text-end">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                この警告を確認済みにする
                            </button>
                        </form>
                    </div>
                @endforeach
            @endif

            <p class="text-info small mt-3">
                ※これらの警告は、過去の行為に対して管理者から通知されたものです。
            </p>
        @endif
    </div>
</div>
@endif {{-- ここに追記 --}}

{{-- スキル管理カード --}}
<div class="card mb-4 shadow-sm">
    <div class="card-header bg-success text-white">
        <h2 class="h5 mb-0">私が提供できるスキル</h2>
    </div>
    <div class="card-body">
        @if($skills->isEmpty())
            <p class="text-center text-muted">まだスキルを登録していません。</p>
            <a href="{{ route('skill.manage.index') }}" class="btn btn-success mt-2">
                新しいスキルを登録する
            </a>
        @else
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4"> {{-- レスポンシブなグリッドレイアウト --}}
                @foreach($skills as $skill)
                    <div class="col">
                        <a href="{{ route('skill.detail.show', $skill->id) }}" 
                           class="card h-100 shadow-sm text-decoration-none text-body skill-card-link"> {{-- カード全体をリンクに --}}
                            {{-- ★ここを修正★ image_urlアクセサを使用 --}}
                            <img src="{{ $skill->image_url }}" 
                                 class="card-img-top" 
                                 alt="{{ $skill->title }}" 
                                 style="height: 180px; object-fit: cover;">
                            
                            <div class="card-body">
                                <h3 class="card-title h6 mb-2">
                                    {{ $skill->title }} ({{ $skill->category }})
                                </h3>
                                <p class="card-text text-muted small mb-2">
                                    {{ Str::limit($skill->description, 100) }}
                                </p>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
            
            <div class="text-center mt-4">
                <a href="{{ route('skill.manage.index') }}" class="btn btn-success">
                    新しいスキルを登録・管理する
                </a>
            </div>
        @endif
    </div>
</div>

    {{-- マッチング履歴カード --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-info text-white">
            <h2 class="h5 mb-0">進行中のマッチング</h2>
        </div>
        <div class="card-body">
{{-- 申し込んだマッチング --}}
<h4 class="mt-2 mb-3">あなたが申し込んでいるマッチング</h4>
@if($appliedMatchings->isEmpty())
    <p class="text-muted">現在、あなたが申し込んでいるマッチングはまだありません。</p>
@else
    <div class="matching-list">
        @foreach($appliedMatchings as $matching)
            <div class="card mb-3">
                <div class="card-body">
                    <h3 class="h5">
                        {{-- あなたがリクエストしているスキル (offeringSkillは相手のもの) --}}
                        <span class="text-primary">{{ $matching->offeringSkill->title ?? 'N/A' }}</span>
                        を提供希望する
                        {{-- 相手の名前 (offeringSkillを所有するユーザー) --}}
                        <span class="text-success">{{ $matching->offeringSkill->user->name ?? 'N/A' }}</span>
                        さんへの申し込み
                    </h3>
                    {{-- 既存のステータス表示はそのまま --}}
                    <div class="row mb-2">
                        <div class="col-md-6">
                            <p><strong>ステータス:</strong>
                                <span class="badge bg-{{ $matching->status == 1 ? 'success' : ($matching->status == 3 ? 'danger' : 'warning') }}">
                                    {{ $matching->statusText }}
                                </span>
                            </p>
                        </div>
                    </div>

                    {{-- あなたが提供するスキルと相手が提供するスキルを表示 --}}
                    <p><strong>あなたが提供するスキル:</strong> {{ $matching->receivingSkill->title ?? 'N/A' }}</p>
                    <p><strong>相手が提供するスキル:</strong> {{ $matching->offeringSkill->title ?? 'N/A' }}</p>
                    {{-- 予定日時をここへ移動 --}}
                    <p><strong>予定日時:</strong>
                        {{ $matching->scheduled_at ? $matching->scheduled_at->format('Y-m-d H:i') : '未定' }}
                    </p>

                    {{-- アクションボタン --}}
                    <div class="action-buttons mt-3">
                        <a href="{{ route('message.show', ['matchingId' => $matching->id]) }}"
                           class="btn btn-primary btn-sm">メッセージを見る</a>

                        {{-- 申し込んだ側はキャンセルボタンのみ --}}
                        @if($matching->status == 0 || $matching->status == 1)
                            <form action="{{ route('matching.cancel', $matching->id) }}"
                                  method="POST" class="d-inline"
                                  onsubmit="return confirm('本当にこの申請を取り消しますか？');">
                                @csrf
                                <button type="submit" class="btn btn-warning btn-sm">申請取り消し</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif

---

{{-- 申し込まれたマッチング --}}
<h4 class="mt-4 mb-3">相手から申し込まれているマッチング</h4>
@if($receivedMatchings->isEmpty())
    <p class="text-muted">現在、相手から申し込まれているマッチングはまだありません。</p>
@else
    <div class="matching-list">
        @foreach($receivedMatchings as $matching)
            <div class="card mb-3">
                <div class="card-body">
                    <h3 class="h5">
                        {{-- あなたが提供するスキル (offeringSkillはあなたのもの) --}}
                        <span class="text-primary">{{ $matching->offeringSkill->title ?? 'N/A' }}</span>
                        を提供希望する
                        {{-- 相手の名前 (receivingSkillを所有するユーザー) --}}
                        <span class="text-success">{{ $matching->receivingSkill->user->name ?? 'N/A' }}</span>
                        さんからの申し込み
                    </h3>
                    {{-- 既存のステータス表示はそのまま --}}
                    <div class="row mb-2">
                        <div class="col-md-6">
                            <p><strong>ステータス:</strong>
                                <span class="badge bg-{{ $matching->status == 1 ? 'success' : ($matching->status == 3 ? 'danger' : 'warning') }}">
                                    {{ $matching->statusText }}
                                </span>
                            </p>
                        </div>
                    </div>

                    {{-- あなたが提供するスキルと相手が提供するスキルを表示 --}}
                    <p><strong>あなたが提供するスキル:</strong> {{ $matching->receivingSkill->title ?? 'N/A' }}</p>
                    <p><strong>相手が提供するスキル:</strong> {{ $matching->offeringSkill->title ?? 'N/A' }}</p>
                    {{-- 予定日時をここへ移動 --}}
                    <p><strong>予定日時:</strong>
                        {{ $matching->scheduled_at ? $matching->scheduled_at->format('Y-m-d H:i') : '未定' }}
                    </p>

                    {{-- アクションボタン --}}
                    <div class="action-buttons mt-3">
                        <a href="{{ route('message.show', ['matchingId' => $matching->id]) }}"
                           class="btn btn-primary btn-sm">メッセージを見る</a>

                        @if($matching->status == 0) {{-- 保留中の場合、承認/拒否ボタンを表示 --}}
                            <form action="{{ route('matching.approve', $matching->id) }}"
                                  method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm">承認する</button>
                            </form>
                            <form action="{{ route('matching.reject', $matching->id) }}"
                                  method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-danger btn-sm">拒否する</button>
                            </form>
                        @elseif($matching->status == 1) {{-- 承認済みの場合、完了/キャンセルボタンを表示 --}}
                            <form action="{{ route('matching.complete', $matching->id) }}"
                                  method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-info btn-sm">完了する</button>
                            </form>
                            <form action="{{ route('matching.cancel', $matching->id) }}"
                                  method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-warning btn-sm">キャンセル</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
            
            <div class="text-center mt-4">
                <a href="{{ route('matching.history.index') }}" class="btn btn-info">
                    全マッチング履歴を見る
                </a>
            </div>
        </div>
    </div>

    {{-- データエクスポートカード --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-secondary text-white">
            <h2 class="h5 mb-0">
                <i class="fas fa-download me-2"></i>データエクスポート
            </h2>
        </div>
        <div class="card-body">
            <p class="text-muted mb-3">
                期間を指定してマッチング履歴をCSV形式でエクスポートします。就職活動や学習の振り返りにご活用ください。
            </p>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exportHistoryModal">
                <i class="fas fa-file-export me-2"></i>履歴をエクスポート
            </button>
        </div>
    </div>

    {{-- エクスポートモーダル --}}
    <div class="modal fade" id="exportHistoryModal" tabindex="-1" aria-labelledby="exportHistoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exportHistoryModalLabel">
                        マッチング履歴のエクスポート設定
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <form method="POST" action="{{ route('profile.export.execute') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="start_date" class="form-label">開始日 (任意):</label>
                            <input type="date" class="form-control" id="start_date" name="start_date">
                        </div>
                        
                        <div class="mb-3">
                            <label for="end_date" class="form-label">終了日 (任意):</label>
                            <input type="date" class="form-control" id="end_date" name="end_date">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">ステータスで絞り込む (任意):</label>
                            
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="status_filter[]" 
                                       value="0" id="statusPending" checked>
                                <label class="form-check-label" for="statusPending">申請中</label>
                            </div>
                            
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="status_filter[]" 
                                       value="1" id="statusApproved" checked>
                                <label class="form-check-label" for="statusApproved">承認済み</label>
                            </div>
                            
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="status_filter[]" 
                                       value="2" id="statusCompleted" checked>
                                <label class="form-check-label" for="statusCompleted">完了</label>
                            </div>
                            
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="status_filter[]" 
                                       value="3" id="statusRejected">
                                <label class="form-check-label" for="statusRejected">拒否</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">閉じる</button>
                        <button type="submit" class="btn btn-primary">エクスポート</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>

/* public/css/app.css */

/* CSSカスタムプロパティ（変数）を再定義/確認 - 元の濃い色調 */
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

    --status-danger: #dc3545; /* 拒否ボタン/警告: Bootstrapの赤に近い */
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

/* カードヘッダーの背景色を統一 */
.card-header {
    background-color: var(--skillswap-primary-dark) !important; /* 少し濃い青をヘッダーに */
    color: var(--skillswap-text-light) !important;
    font-weight: bold;
    border-bottom: 1px solid var(--skillswap-primary-dark) !important;
}

/* ただし、警告カードのヘッダーは赤を保持 */
.card-header.bg-danger {
    background-color: var(--status-danger) !important;
    border-color: var(--status-danger) !important;
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

/* モーダルのヘッダー */
.modal-header {
    background-color: var(--skillswap-primary-dark); /* 少し濃い青をヘッダーに */
    color: var(--skillswap-text-light);
    border-bottom: 1px solid var(--skillswap-primary-dark);
}
.modal-header .btn-close {
    filter: invert(1);
}

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

/* 未読メッセージ通知の調整 */
.alert.alert-info {
    background-color: var(--status-info-light) !important; /* 薄いグレー */
    border-color: var(--status-info) !important;
    color: var(--skillswap-text-dark) !important; /* 濃いめのテキスト */
    font-weight: bold;
}
/* alert-info 内のボタンはプライマリカラーに統一 */
.alert-info .btn-primary {
    background-color: var(--skillswap-primary);
    border-color: var(--skillswap-primary);
    color: var(--skillswap-text-light);
}
.alert-info .btn-primary:hover {
    background-color: var(--skillswap-primary-dark);
    border-color: var(--skillswap-primary-dark);
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
.card-body .alert-danger .btn-outline-danger {
    color: var(--status-danger) !important;
    border-color: var(--status-danger) !important;
}
.card-body .btn-outline-danger:hover { /* ユーザー通報時のコメントのボタン */
    background-color: var(--status-danger) !important;
    color: var(--skillswap-text-light) !important;
}


/* スキル管理カード内のボタンはプライマリカラーに統一 */
.card-body .btn-success { /* HTML側のクラスは.btn-successだが、CSSでブランドカラーに上書き */
    background-color: var(--skillswap-primary);
    border-color: var(--skillswap-primary);
    color: var(--skillswap-text-light);
}
.card-body .btn-success:hover {
    background-color: var(--skillswap-primary-dark);
    border-color: var(--skillswap-primary-dark);
}

/* スキルカードのホバーエフェクト（変更なし） */
.skill-card-link {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}
.skill-card-link:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15) !important;
}

/* マッチング履歴カード内のボタン */
/* メッセージを見るボタンはプライマリカラー */
.matching-list .btn-primary {
    background-color: var(--skillswap-primary); /* メインの青 */
    border-color: var(--skillswap-primary);
    color: var(--skillswap-text-light);
}
.matching-list .btn-primary:hover {
    background-color: var(--skillswap-primary-dark); /* ホバー時は濃い青 */
    border-color: var(--skillswap-primary-dark);
}

/* 承認ボタン (ステータス: 成功) */
.matching-list .btn-success {
    background-color: var(--status-success); /* ブランドの濃い青 */
    border-color: var(--status-success);
    color: var(--skillswap-text-light);
}
.matching-list .btn-success:hover {
    background-color: var(--skillswap-primary); /* ホバー時はメインの青 */
    border-color: var(--skillswap-primary);
}

/* 拒否ボタン (ステータス: 危険) */
.matching-list .btn-danger {
    background-color: var(--status-danger);
    border-color: var(--status-danger);
    color: var(--skillswap-text-light);
}
.matching-list .btn-danger:hover {
    background-color: var(--status-danger-dark);
    border-color: var(--status-danger-dark);
}

/* 申請取り消し/キャンセルボタン (ステータス: 警告) */
.matching-list .btn-warning {
    background-color: var(--status-warning); /* 赤のバリエーション */
    border-color: var(--status-warning);
    color: var(--skillswap-text-light);
}
.matching-list .btn-warning:hover {
    background-color: var(--status-warning-dark);
    border-color: var(--status-warning-dark);
}

/* 完了ボタン (ステータス: 情報) */
/* ここを修正：セレクタから.matching-listを削除し、btn-info全体に適用 */
.btn-info { 
    background-color: var(--status-info); /* グレー */
    border-color: var(--status-info);
    color: var(--skillswap-text-light);
}
.btn-info:hover {
    background-color: var(--status-info-dark);
    border-color: var(--status-info-dark);
}

/* バッジの色の調整 */
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
/* スキルカードのホバーエフェクト */
.skill-link {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.skill-link:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
}

/* カードの基本設定 */
.container .card {
    max-width: 100%;
    box-sizing: border-box;
}

/* テキストの折り返し */
.card-body p {
    word-wrap: break-word;
    overflow-wrap: break-word;
}

/* ボタンの最適化 */
.card-body .btn {
    max-width: 100%;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* マッチングリストのスタイル */
.matching-list .card {
    border-left: 4px solid #007bff;
}

/* アクションボタンの間隔 */
.action-buttons .btn {
    margin-right: 0.5rem;
    margin-bottom: 0.5rem;
}

/* レビューセクションのスタイル */
.review-section .alert {
    margin-bottom: 0.5rem;
}

/* ステータスバッジのスタイル調整 */
.badge {
    font-size: 0.875em;
}

/* レスポンシブ対応 */
@media (max-width: 768px) {
    .action-buttons .btn {
        width: 100%;
        margin-right: 0;
    }
    
    .row .col-md-6 {
        margin-bottom: 0.5rem;
    }
}
</style>
@endpush
@extends('layouts.app') {{-- layouts/app.blade.php を継承 --}}

@section('title', 'レビュー投稿') {{-- ページのタイトルを設定 --}}

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h1 class="mb-0 text-center">レビュー投稿</h1>
                    </div>
                    <div class="card-body">
                        <p class="mb-4 text-center">
                            <strong>対象スキル:</strong>
                            <span class="badge bg-info text-dark me-1">{{ $matching->offeringSkill->title }}</span>
                            <i class="bi bi-arrow-left-right"></i>
                            <span class="badge bg-info text-dark ms-1">{{ $matching->receivingSkill->title }}</span>
                        </p>

                        <form method="POST" action="{{ url('/review/' . $matching->id) }}">
                            @csrf

                            <div class="mb-3">
                                <label for="rating" class="form-label d-block text-center">評価 (1〜5):</label>
                                <div class="d-flex justify-content-center">
                                    <select name="rating" id="rating" class="form-select w-auto" required>
                                        @for ($i = 1; $i <= 5; $i++)
                                            <option value="{{ $i }}" @if($i == 5) selected @endif>{{ $i }}</option>
                                        @endfor
                                    </select>
                                </div>
                                @error('rating')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="comment" class="form-label">コメント:</label>
                                <textarea name="comment" id="comment" class="form-control" rows="5" maxlength="1000" placeholder="レビューコメントを入力してください"></textarea>
                                @error('comment')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-grid gap-2 col-6 mx-auto">
                                <button type="submit" class="btn btn-primary btn-lg">送信</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div> 
    </div>
@endsection

@push('styles')
<style>
    .card {
        --bs-card-height: auto;
        height: auto !important;
    }

    .skill-detail-card {
        min-height: 200px; 

    }

    .skill-provider-card {
        min-height: 200px;
    }
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

/* --- カードヘッダーの色の調整 --- */
/* 全体的なカードヘッダーのデフォルト色（.card-header）を濃い青に */
.card-header {
    background-color: var(--skillswap-primary-dark) !important; /* 新しい濃い青 */
    color: var(--skillswap-text-light) !important;
    font-weight: bold;
    border-bottom: 1px solid var(--skillswap-primary-dark) !important;
}

/* 警告カードのヘッダーは赤を保持 */
.card-header.bg-danger {
    background-color: var(--status-danger) !important;
    border-color: var(--status-danger) !important;
}

/* マッチング履歴ページのカードヘッダーを調整 */
/* あなたが申請したマッチング (bg-primaryを使用) */
.card-header.bg-primary {
    background-color: var(--skillswap-primary-dark) !important; /* 濃い青 */
    border-color: var(--skillswap-primary-dark) !important;
}

/* あなたに申請されたマッチング (bg-successを使用) */
.card-header.bg-success {
    background-color: var(--skillswap-primary) !important; /* 少し落ち着いた青に */
    border-color: var(--skillswap-primary) !important;
}

/* ヘッダー内の編集ボタン（もしあれば） */
.card-header .btn-light {
    background-color: rgba(255, 255, 255, 0.2) !important;
    border-color: rgba(255, 255, 255, 0.3) !important;
    color: var(--skillswap-text-light) !important;
}
.card-header .btn-light:hover {
    background-color: rgba(255, 255, 255, 0.3) !important;
    border-color: rgba(255, 255, 255, 0.4) !important;
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

/* btn-info の調整（完了ボタン、その他情報ボタン） */
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

/* btn-success (承認ボタン、レビューボタン) */
.btn-success {
    background-color: var(--skillswap-primary-dark) !important; /* ブランドの濃い青に統一 */
    border-color: var(--skillswap-primary-dark) !important;
    color: var(--skillswap-text-light) !important;
}
.btn-success:hover {
    background-color: var(--skillswap-primary) !important; /* ホバー時はメインの青 */
    border-color: var(--skillswap-primary) !important;
}

/* btn-warning (申請取り消し) */
.btn-warning {
    background-color: var(--status-warning) !important; /* 赤のバリエーションに統一 */
    border-color: var(--status-warning) !important;
    color: var(--skillswap-text-light) !important;
}
.btn-warning:hover {
    background-color: var(--status-warning-dark) !important;
    border-color: var(--status-warning-dark) !important;
}

/* btn-danger (拒否ボタン) */
.btn-danger {
    background-color: var(--status-danger) !important; /* 強めの赤に統一 */
    border-color: var(--status-danger) !important;
    color: var(--skillswap-text-light) !important;
}
.btn-danger:hover {
    background-color: var(--status-danger-dark) !important;
    border-color: var(--status-danger-dark) !important;
}

/* メッセージボタン (btn-outline-primary) */
.btn-outline-primary {
    color: var(--skillswap-primary-dark) !important; /* 濃い青の文字色に統一 */
    border-color: var(--skillswap-primary-dark) !important; /* 濃い青のボーダーに統一 */
}
.btn-outline-primary:hover {
    background-color: var(--skillswap-primary-dark) !important; /* ホバー時に濃い青の背景に統一 */
    color: var(--skillswap-text-light) !important; /* ホバー時に文字色を白に統一 */
}
/* custom-message-btn の追加スタイル */
.custom-message-btn {
    padding: 0.375rem 0.75rem; /* Bootstrap btn-sm と同じパディング */
    font-size: 0.875rem; /* Bootstrap btn-sm と同じフォントサイズ */
    line-height: 1.5; /* Bootstrap btn-sm と同じラインハイト */
    border-radius: 0.25rem; /* Bootstrap btn-sm と同じボーダーラディウス */
}


/* --- アラート/メッセージの調整 --- */
/* 申請したマッチングはありません (alert-info) */
.alert.alert-info {
    background-color: var(--status-info-light) !important; /* 薄いグレー */
    border-color: var(--status-info) !important;
    color: var(--skillswap-text-dark) !important; /* 濃いめのテキスト */
    font-weight: bold;
}

/* レビュー関連のカード */
.card.border-primary { /* あなたのレビュー */
    border-color: var(--skillswap-primary-dark) !important; /* 濃い青のボーダー */
}
.card.border-secondary { /* 相手のレビュー */
    border-color: var(--status-info) !important; /* グレーのボーダー */
}
.alert.alert-secondary { /* 相手からのレビューはまだありません */
    background-color: var(--status-info-light) !important; /* 薄いグレー */
    border-color: var(--status-info) !important;
    color: var(--skillswap-text-dark) !important;
}

/* --- バッジの色の調整 --- */
/* ステータスバッジ */
.badge.bg-warning { /* 申請中 (status 0) */
    background-color: var(--status-pending) !important; /* 保留中はミディアムグレー */
    color: var(--skillswap-text-light) !important;
}
.badge.bg-success { /* 承認済み (status 1) */
    background-color: var(--status-success-light) !important; /* 薄い青 */
    color: var(--skillswap-primary-dark) !important; /* テキストは濃い青 */
}
.badge.bg-info { /* 完了 (status 2) */
    background-color: var(--status-info-light) !important; /* 薄いグレー */
    color: var(--skillswap-text-dark) !important;
}
.badge.bg-danger { /* キャンセル/拒否 (status 3, 4) */
    background-color: var(--status-danger-light) !important; /* 薄い赤 */
    color: var(--status-danger) !important;
}
.text-primary { /* <span class="text-primary"> のスキル名 */
    color: var(--skillswap-primary) !important; /* ここを通常のメイン青（明るい方）に変更 */
}

.text-success { /* <span class="text-success"> のユーザー名 */
    color: var(--skillswap-primary) !important; /* 濃いグレーを維持 */
}
</style>
@endpush
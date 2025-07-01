@extends('layouts.app')

@section('title', 'マッチング申し込み')

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h1 class="mb-4 text-center">マッチング申し込み</h1>

                {{-- 相手のスキル情報と提供者情報 --}}
                <div class="card shadow-sm mb-4">
                    {{-- ここを bg-primary に変更 --}}
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">相手のスキルと提供者情報</h5>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title"><strong>スキル名：</strong> <span class="text-primary">{{ $targetSkill->title }}</span></h5> {{-- スキル名をブランドカラーに --}}
                        <p class="card-text mb-2"><strong>カテゴリ：</strong> {{ $targetSkill->category }}</p>
                        <p class="card-text mb-3"><strong>説明：</strong> {!! nl2br(e($targetSkill->description)) !!}</p>

                        <hr class="my-3">

                        <h6>スキル提供者： <span class="text-success">{{ $targetSkill->user->name }}</span></h6> {{-- 提供者名をダークテキストに --}}
                        <ul class="list-unstyled mb-3">
                            <li><strong>総マッチング件数：</strong> {{ $targetUserTotalMatchingCount }}件</li>
                            <li><strong>全レビューの評価平均：</strong>
                                @if ($targetUserAverageRating)
                                    {{ number_format($targetUserAverageRating, 1) }}
                                @else
                                    まだ評価はありません
                                @endif
                            </li>
                        </ul>

                        @if ($targetUserLatestReviews->isNotEmpty())
                            <h6>最新レビュー</h6>
                            <ul class="list-group mb-3">
                                @foreach ($targetUserLatestReviews as $review)
                                    <li class="list-group-item">
                                        評価：{{ $review->rating }}
                                        <p class="mb-0">{{ $review->comment }}</p>
                                        <small class="text-muted">{{ $review->created_at->format('Y/m/d H:i') }}</small>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            {{-- alert-info はグローバルCSSで薄いグレーになるように定義済み --}}
                            <div class="alert alert-info" role="alert">
                                この提供者へのレビューはまだありません。
                            </div>
                        @endif

                    </div>
                </div>

                {{-- 自分のスキル選択フォーム（変更なし） --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">自分のスキルを選択し、日時を設定</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="/matching/apply/confirm">
                            @csrf
                            <input type="hidden" name="receiving_skill_id" value="{{ $targetSkill->id }}">

                            <div class="mb-3">
                                <label for="offering_skill_id" class="form-label">提供するスキル:</label>
                                <select name="offering_skill_id" id="offering_skill_id" class="form-select" required>
                                    <option value="">選択してください</option>
                                    @foreach ($mySkills as $skill)
                                        <option value="{{ $skill->id }}">{{ $skill->title }}（{{ $skill->category }}）</option>
                                    @endforeach
                                </select>
                                @error('offering_skill_id')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="scheduled_at" class="form-label">日時を選択:</label>
                                <input type="datetime-local" name="scheduled_at" id="scheduled_at" class="form-control" required>
                                @error('scheduled_at')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">確認画面へ</button>
                                <a href="/skill/detail/{{ $targetSkill->id }}" class="btn btn-secondary btn-lg">キャンセルして戻る</a>
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
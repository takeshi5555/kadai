@extends('layouts.app')

@section('title', 'マッチング履歴')

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <h1 class="mb-4 text-center">マッチング履歴</h1>

                {{-- あなたが申請したマッチング --}}
<h2 class="mb-3">あなたが申請したマッチング</h2>
@forelse ($applied as $match)
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                相手：{{ $match->receivingSkill->user->name ?? '不明' }}
            </h5>
            <span class="badge bg-{{
                match ($match->status) {
                    0 => 'warning', // 保留中
                    1 => 'success', // 承認済み
                    2 => 'info',    // 完了
                    3 => 'danger',  // キャンセル
                    4 => 'danger',  // 拒否
                    default => 'secondary' // その他のステータス
                }
            }} text-dark fs-6">
                @switch($match->status)
                    @case(0) 保留中 @break
                    @case(1) 承認済み @break
                    @case(2) 完了 @break
                    @case(3) キャンセル @break
                    @case(4) 拒否 @break
                @endswitch
            </span>
        </div>
        <div class="card-body">
            <p class="card-text mb-1"><strong>あなたが提供するスキル:</strong> {{ $match->offeringSkill->title ?? '不明' }}</p>
            <p class="card-text mb-1"><strong>相手が提供するスキル:</strong> {{ $match->receivingSkill->title ?? '不明' }}</p>
            <p class="card-text mb-3"><strong>日時:</strong> {{ $match->scheduled_at ? \Carbon\Carbon::parse($match->scheduled_at)->format('Y年m月d日 H時i分') : '未定' }}</p>

            <div class="d-flex flex-wrap gap-2 mb-3">
                @if ($match->status === 0)
                    <form method="POST" action="/matching/{{ $match->id }}/cancel" onsubmit="return confirm('本当にこの申請を取り消しますか？');" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-warning btn-sm">申請取り消し</button>
                    </form>
                @endif

                @if ($match->status === 1)
                    <form method="POST" action="/matching/{{ $match->id }}/complete" onsubmit="return confirm('このマッチングを完了しますか？');" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-info btn-sm">完了</button>
                    </form>
                @endif

                {{-- ★ここを修正: statusが0, 1, 2の時にメッセージボタンを表示する★ --}}
                    {{-- ★ここを修正: statusが0, 1, 2の時にメッセージボタンを表示する★ --}}
                    @if (in_array($match->status, [0, 1, 2]))
                        @php
                            $unreadCount = $match->unreadMessagesCount();
                        @endphp
                        {{-- 変更点: btn-sm の代わりにカスタムクラスを追加し、d-inline-flex を使用 --}}
                        <a href="/message/{{ $match->id }}" class="btn btn-outline-primary custom-message-btn d-inline-flex align-items-center justify-content-center"> 
                            <span>メッセージ</span>
                            @if ($unreadCount > 0)
                                <span class="badge rounded-pill bg-danger ms-1" style="font-size: 0.6em;">
                                    {{ $unreadCount }}
                                    <span class="visually-hidden">未読メッセージ</span>
                                </span>
                            @endif
                        </a>
                    @endif
            </div>

            @if ($match->status === 2)
                <div class="row mt-3">
                    <div class="col-md-6">
                        @if ($match->myReview)
                            <div class="card bg-light border-primary mb-2">
                                <div class="card-body py-2">
                                    <h6 class="card-title mb-1">あなたのレビュー:</h6>
                                    <p class="card-text mb-1">評価: {{ $match->myReview->rating }} / 5</p>
                                    <p class="card-text text-muted mb-0">コメント: {{ $match->myReview->comment ?? '（なし）' }}</p>
                                </div>
                            </div>
                        @else
                            <a href="/review/{{ $match->id }}" class="btn btn-success btn-sm">レビューを書く</a>
                        @endif
                    </div>
                    <div class="col-md-6">
                        @if ($match->partnerReview)
                            <div class="card bg-light border-secondary mb-2">
                                <div class="card-body py-2">
                                    <h6 class="card-title mb-1">相手のレビュー:</h6>
                                    <p class="card-text mb-1">評価: {{ $match->partnerReview->rating }} / 5</p>
                                    <p class="card-text text-muted mb-0">コメント: {{ $match->partnerReview->comment ?? '（なし）' }}</p>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-secondary py-2 px-3 mb-0" role="alert">
                                相手からのレビューはまだありません。
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
@empty
    <div class="alert alert-info" role="alert">
        申請したマッチングはありません。
    </div>
@endforelse

---

{{-- あなたに申請されたマッチング --}}
<h2 class="mb-3">あなたに申請されたマッチング</h2>
@forelse ($received as $match)
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                相手：{{ $match->offeringSkill->user->name ?? '不明' }}
            </h5>
            <span class="badge bg-{{
                match ($match->status) {
                    0 => 'warning', // 申請中
                    1 => 'success', // 承認済み
                    2 => 'info',    // 完了
                    3 => 'danger',  // 拒否 (※以前のコンテキストで4は表示しないようにしたため、ここでは考慮不要)
                    default => 'secondary' // その他のステータス
                }
            }} text-dark fs-6">
                @switch($match->status)
                    @case(0) 保留中 @break
                    @case(1) 承認済み @break
                    @case(2) 完了 @break
                    @case(3) 拒否 @break {{-- 実際には表示されないが、念のため記述 --}}
                @endswitch
            </span>
        </div>
        <div class="card-body">
            <p class="card-text mb-1"><strong>あなたが提供するスキル:</strong> {{ $match->receivingSkill->title ?? '不明' }}</p>
            <p class="card-text mb-1"><strong>相手が提供するスキル:</strong> {{ $match->offeringSkill->title ?? '不明' }}</p>
            <p class="card-text mb-3"><strong>日時:</strong> {{ $match->scheduled_at ? \Carbon\Carbon::parse($match->scheduled_at)->format('Y年m月d日 H時i分') : '未定' }}</p>

            <div class="d-flex flex-wrap gap-2 mb-3">
                @if ($match->status === 0)
                    <form method="POST" action="/matching/{{ $match->id }}/approve" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm">承認</button>
                    </form>
                    <form method="POST" action="/matching/{{ $match->id }}/reject" onsubmit="return confirm('本当にこの申請を拒否しますか？');" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-sm">拒否</button>
                    </form>
                @endif

                @if ($match->status === 1)
                    <form method="POST" action="/matching/{{ $match->id }}/complete" onsubmit="return confirm('このマッチングを完了しますか？');" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-info btn-sm">完了</button>
                    </form>
                @endif

                {{-- ★ここを修正: statusが0, 1, 2の時にメッセージボタンを表示する★ --}}
                    {{-- ★ここを修正: statusが0, 1, 2の時にメッセージボタンを表示する★ --}}
                    @if (in_array($match->status, [0, 1, 2]))
                        @php
                            $unreadCount = $match->unreadMessagesCount();
                        @endphp
                        {{-- 変更点: btn-sm の代わりにカスタムクラスを追加し、d-inline-flex を使用 --}}
                        <a href="/message/{{ $match->id }}" class="btn btn-outline-primary custom-message-btn d-inline-flex align-items-center justify-content-center"> 
                            <span>メッセージ</span>
                            @if ($unreadCount > 0)
                                <span class="badge rounded-pill bg-danger ms-1" style="font-size: 0.6em;">
                                    {{ $unreadCount }}
                                    <span class="visually-hidden">未読メッセージ</span>
                                </span>
                            @endif
                        </a>
                    @endif
            </div>

            @if ($match->status === 2)
                <div class="row mt-3">
                    <div class="col-md-6">
                        @if ($match->myReview)
                            <div class="card bg-light border-primary mb-2">
                                <div class="card-body py-2">
                                    <h6 class="card-title mb-1">あなたのレビュー:</h6>
                                    <p class="card-text mb-1">評価: {{ $match->myReview->rating }} / 5</p>
                                    <p class="card-text text-muted mb-0">コメント: {{ $match->myReview->comment ?? '（なし）' }}</p>
                                </div>
                            </div>
                        @else
                            <a href="/review/{{ $match->id }}" class="btn btn-success btn-sm">レビューを書く</a>
                        @endif
                    </div>
                    <div class="col-md-6">
                        @if ($match->partnerReview)
                            <div class="card bg-light border-secondary mb-2">
                                <div class="card-body py-2">
                                    <h6 class="card-title mb-1">相手のレビュー:</h6>
                                    <p class="card-text mb-1">評価: {{ $match->partnerReview->rating }} / 5</p>
                                    <p class="card-text text-muted mb-0">コメント: {{ $match->partnerReview->comment ?? '（なし）' }}</p>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-secondary py-2 px-3 mb-0" role="alert">
                                相手からのレビューはまだありません。
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
@empty
    <div class="alert alert-info" role="alert">
        申請されたマッチングはありません。
    </div>
@endforelse
            </div>
        </div>
    </div>
@endsection



<style>
    .card {
        --bs-card-height: auto;
        height: auto !important;
    }

    .skill-detail-card {
        min-height: 150px; 

    }

    .skill-provider-card {
        min-height: 150px;
    }
.custom-message-btn {
    padding: 0.25rem 0.5rem; /* Bootstrapのbtn-smと同じパディング */
    font-size: 0.875rem;     /* Bootstrapのbtn-smと同じフォントサイズ */
    line-height: 1.25;       /* 行の高さはbtn-smのデフォルトを参考に、少し調整 */
    height: 31px;            /* ★明示的に高さを指定する（btn-smの標準的な高さ）★ */
    min-width: fit-content;  /* コンテンツに合わせた最小幅 */
    /* Flexbox関連 */
    display: inline-flex;
    align-items: center;
    justify-content: center;
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


</style>
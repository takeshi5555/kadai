@extends('layouts.app')

@section('content')
@php use Illuminate\Support\Str; @endphp {{-- Str::limit を使うため、ここでuse宣言 --}}

<div class="container py-5"> {{-- 全体的な上下のパディングを大きく --}}
    <h1 class="text-center mb-3">SkillSwap</h1> {{-- タイトルを中央寄せに --}}
    <p class="lead text-center mb-5">スキルシェアサービスへようこそ！</p> {{-- リード文として少し大きく、中央寄せに --}}

    {{-- --- 主要CTAボタン --- --}}
    <div class="d-grid gap-3 col-md-8 col-lg-6 mx-auto mb-5"> {{-- ボタンを中央に配置し、適切な幅に --}}
        <a href="{{ url('/skill/search') }}" class="btn btn-primary btn-lg">スキルを探す</a>

        @guest
            <a href="{{ url('/signup') }}" class="btn btn-outline-secondary btn-lg">新規登録はこちら</a>
        @else
            <a href="{{ url('/skill/manage') }}" class="btn btn-success btn-lg">スキルを教える</a>
        @endguest
    </div>
    {{-- --- 主要CTAボタンここまで --- --}}

    <hr class="my-5"> {{-- セクション間の明確な区切り --}}

       <section class="mb-5 text-center bg-light p-5 rounded">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                <h2 class="mb-4 display-5 fw-bold">SkillSwapで、あなたの世界を広げよう。</h2>
                <p class="lead mb-4">SkillSwapは、あなたが持っているスキルを他の誰かに教えたり、逆に学びたいスキルを持つ人から学ぶことができる、スキルシェアサービスです。</p>
                <div class="row mt-4">
                    <div class="col-md-4 mb-3">
                        <i class="bi bi-person-check fs-2 text-primary"></i>
                        <h4 class="mt-2">簡単登録</h4>
                        <p class="text-muted">数ステップであなたのスキルを登録できます。</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <i class="bi bi-book fs-2 text-success"></i>
                        <h4 class="mt-2">多様なスキル</h4>
                        <p class="text-muted">プログラミングからビジネスまで、あらゆるスキルが集まります。</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <i class="bi bi-hand-thumbs-up fs-2 text-info"></i>
                        <h4 class="mt-2">安心サポート</h4>
                        <p class="text-muted">安全なマッチングとサポート体制で安心。</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- --- おすすめカテゴリ --- --}}
    <section class="mb-5">
        <h2 class="text-center mb-4">おすすめカテゴリ</h2>
        @if($categoriesToDisplay->isEmpty())
        <p class="text-center text-muted">カテゴリはまだ登録されていません。</p>
        @else
        <div class="d-flex flex-wrap justify-content-center gap-3">
            @foreach($categoriesToDisplay as $category) 
            <a href="{{ url('/skill/search?category=' . urlencode($category->name)) }}" class="card text-decoration-none text-body category-card" style="width: 180px;">
                <img src="{{ asset($category->image) }}" class="card-img-top" alt="{{ $category->name }}" style="height: 120px; object-fit: cover;">
                <div class="card-body p-2 text-center">
                    <h5 class="card-title h6 mb-0">{{ $category->name }}</h5>
                </div>
            </a>
            @endforeach 
        </div>
        @endif
    </section>
    {{-- --- おすすめカテゴリここまで --- --}}

    <hr class="my-5">

{{-- --- 新着スキル --- --}}
<section class="mb-5">
    <h2 class="text-center mb-4">新着スキル</h2>
    @if($newSkills->isEmpty())
        <p class="text-center text-muted">新着スキルはまだありません。</p>
    @else
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            @foreach($newSkills as $skill)
                <div class="col">
                    <a href="{{ url('/skill/detail/' . $skill->id) }}" class="card h-100 shadow-sm text-decoration-none text-body skill-card-link">
                        @if($skill->image_path)
                            {{-- スキルに画像が設定されている場合 --}}
                            <img src="{{ asset('storage/' . $skill->image_path) }}" 
                                class="card-img-top" 
                                alt="{{ $skill->title }}" 
                                style="height: 180px; object-fit: cover;"
                                onerror="this.onerror=null; this.src='{{ $skill->default_category_image_path }}';">
                        @else
                            {{-- スキルに画像が設定されていない場合 --}}
                            <img src="{{ $skill->default_category_image_path }}" 
                                class="card-img-top" 
                                alt="カテゴリ: {{ $skill->category ?? '不明' }} のデフォルト画像" 
                                style="height: 180px; object-fit: cover;">
                        @endif
                        <div class="card-body">
                            <h3 class="card-title h5 mb-2">{{ $skill->title }}</h3>
                            <p class="card-text small text-muted mb-2">
                                <strong>カテゴリ:</strong> {{ $skill->category }}<br>
                                <strong>提供者:</strong> {{ $skill->user->name ?? '不明なユーザー' }}
                            </p>
                            <p class="card-text mb-0">{{ Str::limit($skill->description, 100) }}</p>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    @endif
</section>
{{-- --- 新着スキルここまで --- --}}

    <hr class="my-5">

{{-- --- おすすめレビュー --- --}}
<section class="mb-5">
    <h2 class="text-center mb-4">おすすめレビュー</h2>
    @if($featuredReviews->isEmpty())
        <p class="text-center text-muted">まだレビューがありません。</p>
    @else
        <div class="row row-cols-1 row-cols-md-2 g-4"> {{-- レビューもグリッドレイアウトで --}}
            @foreach($featuredReviews as $review)
                <div class="col">
                    {{-- ★ここを修正★ card全体をaタグで囲む --}}
                    {{-- display_skill が存在する場合のみリンクを有効にする --}}
                    @if($review->display_skill)
                        <a href="{{ route('skill.detail.show', $review->display_skill->id) }}" class="card-link text-decoration-none">
                    @endif
                            <div class="card h-100 shadow-sm review-card"> {{-- レビューカードのデザイン --}}
                                <div class="card-body">
                                    <p class="card-text text-info mb-1">
                                        <strong>提供スキル：</strong>
                                        <span class="fw-bold">{{ $review->display_skill->title ?? '不明なスキル' }}</span>
                                    </p>
                                    <p class="card-text fs-5 fw-bold mb-3">"{{ $review->comment }}"</p> {{-- コメントを強調 --}}
                                    <div class="text-end">
                                        <p class="card-text text-muted small mb-0">
                                            {{ $review->reviewer->name ?? '不明なレビュアー' }} さんから
                                            {{ $review->reviewee->name ?? '不明な提供者' }} さんへ
                                        </p>
                                        <p class="card-text text-muted small">
                                            評価: <span class="fw-bold text-warning">{{ $review->rating }}</span>
                                            {{-- ここに星アイコンなどを追加するとより見栄えが良いです --}}
                                        </p>
                                    </div>
                                </div>
                            </div>
                    @if($review->display_skill)
                        </a>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
    </section>
    {{-- --- おすすめレビューここまで --- --}}

</div>
@endsection

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
.card-link {
        display: block; /* aタグをブロック要素にする */
        height: 100%; /* 親要素の高さに合わせる */
        color: inherit; /* リンクの色を親から継承 */
    }
    .card-link .card {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }
    .card-link:hover .card {
        transform: translateY(-5px); /* ホバーで少し浮き上がる */
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important; /* ホバーで影を強調 */
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
@endpush
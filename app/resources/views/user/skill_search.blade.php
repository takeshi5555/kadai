@extends('layouts.app') {{-- もしレイアウトを使用している場合 --}}

@section('content')
<div class="container py-4">
    <section class="mb-5">
        <h2 class="text-center mb-4">スキル検索結果</h2>

        {{-- 検索フォーム --}}
        <form action="{{ url('/skill/search') }}" method="GET" class="mb-3">
            <div class="row g-3 align-items-center">
                <div class="col-md-6">
                    <input type="text" class="form-control" name="keyword" placeholder="キーワード検索" value="{{ request('keyword') }}">
                </div>
                <div class="col-md-4">
                    <select class="form-select" name="category">
                        <option value="">全てのカテゴリ</option>
                        @foreach($categories as $category)
                            <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>{{ $category }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">検索</button>
                </div>
            </div>
        </form>

        @if($skills->isEmpty())
            <p class="text-center text-muted">該当するスキルは見つかりませんでした。</p>
        @else
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4"> {{-- レスポンシブなグリッドレイアウト --}}
                @foreach($skills as $skill)
                    <div class="col">
                        <div class="card h-100 shadow-sm skill-card-link">
                            <a href="{{ url('/skill/detail/' . $skill->id) }}" class="text-decoration-none text-body">
                                {{-- ★ここを修正★ image_urlアクセサを使用 --}}
                                <img src="{{ $skill->image_url }}" 
                                     class="card-img-top" 
                                     alt="{{ $skill->title }}" 
                                     style="height: 180px; object-fit: cover;">
                                
                                <div class="card-body">
                                    <h3 class="card-title h5 mb-2">{{ $skill->title }}</h3> {{-- タイトルサイズを調整 --}}
                                    <p class="card-text small text-muted mb-2">
                                        <strong>カテゴリ:</strong> {{ $skill->category }}<br>
                                        <strong>提供者:</strong> {{ $skill->user->name ?? '不明なユーザー' }}
                                    </p>
                                    <p class="card-text mb-0">{{ Str::limit($skill->description, 100) }}</p> {{-- 説明文の表示 --}}
                                </div>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
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

</style>
@endpush

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'SkillSwap')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- VAPID公開鍵のメタタグ --}}
    <meta name="webpush-vapid-public-key" content="{{ config('webpush.vapid.public_key') }}"> 
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
</head>
<body>
    {{-- ヘッダー (Bootstrap Navbar) --}}
    <header>
        <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm fixed-top">
            <div class="container-fluid">
                <a class="navbar-brand fw-bold fs-5" href="/main">SkillSwap</a> 
                
                <span class="navbar-text me-3 d-none d-lg-block"> {{-- PC画面のみ表示 --}}
                    {{ Auth::check() ? 'ログイン中' : 'ログアウト中' }}
                </span>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse " id="navbarNav">
                    <ul class="navbar-nav ms-auto flex-column flex-lg-row align-items-lg-center"> {{-- メニュー項目を右寄せ --}}
                        @guest
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center" href="{{ route('login') }}">
                                    <i class="bi bi-box-arrow-in-right fs-5 me-1"></i> ログイン
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center" href="{{ route('signup') }}">
                                    <i class="bi bi-person-plus-fill fs-5 me-1"></i> 新規登録
                                </a>
                            </li>
                        @else
                            {{-- ログイン中のユーザーのメニュー --}}

                            {{-- メッセージ通知アイコン --}}
                            <li class="nav-item me-3">
                                <a class="nav-link d-flex align-items-center" href="/matching/history">
                                    <i class="bi bi-chat-dots-fill fs-5"></i>
                                    <span>メッセージ</span>

                                </a>
                            </li>

{{-- マッチング通知アイコン --}}
<li class="nav-item me-3">
    <a class="nav-link d-flex align-items-center" href="/matching/history" id="matching-nav-link">
        <i class="bi bi-hand-index-thumb-fill fs-5"></i>
        <span>マッチング</span>
        {{-- バッジは全てJavaScriptで制御 --}}
    </a>
</li>

                            {{-- ... 既存のメニュー項目 ... --}}
                            @if (Auth::user()->isAdmin())
                                <li class="nav-item">
                                    <a class="nav-link" href="/admin">管理者ページ</a>
                                </li>
                            @elseif (Auth::user()->isModerator())
                                <li class="nav-item">
                                    <a class="nav-link" href="/moderator/reports">通報管理</a>
                                </li>
                            @endif

                            <li class="nav-item">
                                <a class="nav-link" href="/mypage">マイページ</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/skill/search">スキル検索</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/skill/manage">スキル管理</a>
                            </li>

                            <li class="nav-item">
                                <a href="#" class="nav-link" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    ログアウト
                                </a>
                                <form id="logout-form" action="/logout" method="POST" style="display: none;">
                                    @csrf
                                </form>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <main class="py-4 pt-5 mt-5"> {{-- メインコンテンツの上下に余白 --}}
        <div class="container"> {{-- コンテンツを中央寄せ --}}
            {{-- フラッシュメッセージ --}}
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
            @yield('content')
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/app.js') }}" ></script>
    {{-- Service Workerの登録とWeb Push購読スクリプト --}}
    <script src="{{ asset('js/push-notifications.js') }}"></script>
    <script src="{{ asset('js/notification-updater.js') }}"></script>
    @stack('scripts')
</body>
</html>

@push('styles')
<style>
/* ヘッダーのアイコンとバッジの位置調整 */
.navbar-nav .nav-item .nav-link.position-relative {
    display: flex; /* アイコンとテキストを横並びにする */
    align-items: center; /* 垂直方向中央揃え */
    padding-right: 25px; /* バッジのための右パディング */
}

.navbar-nav .nav-item .nav-link.position-relative .bi {
    margin-right: 5px; /* アイコンとテキストの間の余白 */
}

/* バッジの微調整 */
.navbar-nav .nav-item .nav-link .badge {
    font-size: 0.7em; /* バッジの文字サイズを小さく */
    padding: 0.3em 0.5em; /* バッジのパディング */
    position: absolute; /* 親要素に対して絶対配置 */
    top: 5px; /* 上からの位置 */
    right: 0px; /* 右からの位置 */
    transform: translate(-50%, -50%); /* 中央寄せの微調整 */
    white-space: nowrap; /* 数字が改行されないように */
}

/* 通知有効化・テスト通知ボタンのスタイル調整 */
#enable-notifications,
#test-notification {
    /* 必要に応じて追加のスタイルをここに記述 */
    margin-right: 10px; /* 他のナビアイテムとの間に余白 */
}


@media (max-width: 991.98px) { /* Bootstrapのlgブレイクポイント以下 */
    /* ナビバーのトグルボタンが開いた時のメニュー項目調整 */
    .navbar-collapse .nav-item .nav-link .badge { /* .position-relative を削除してより汎用的に */
        position: static !important; /* 絶対配置を解除し、通常フローに沿って配置 */
        margin-left: 5px !important; /* テキストとの間に余白を追加 */
        transform: none !important; /* transformをリセット */
        display: inline-block !important; /* インラインブロックとして表示 */
        top: auto !important; /* topプロパティもリセット */
        right: auto !important; /* rightプロパティもリセット */
    }
    /* モバイル表示時のボタンのパディング調整 */
    #enable-notifications,
    #test-notification {
        width: 100%; /* 幅をフルにする */
        margin-bottom: 5px; /* ボタン間の余白 */
    }
}

</style>
@endpush
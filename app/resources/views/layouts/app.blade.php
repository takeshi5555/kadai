<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'SkillSwap')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="vapid-public-key" content="{{ config('webpush.vapid.public_key') }}">
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
                    {{-- ゲストユーザーのメニュー (変更なし) --}}
                @else
                    {{-- ログイン中のユーザーのメニュー --}}

                    {{-- ★メッセージ通知アイコンを追加★ --}}
                    <li class="nav-item me-3">
                        <a class="nav-link d-flex align-items-center" href="/messages">
                            <i class="bi bi-chat-dots-fill fs-5"></i>
                            <span>メッセージ</span> {{-- テキストをspanで囲む --}}
                            @if (Auth::user()->unread_message_count > 0)
                                <span class="badge rounded-pill bg-danger ms-1 position-relative" style="top: -5px; left: 0px;"> {{-- ★変更点★ --}}
                                    {{ Auth::user()->unread_message_count }}
                                    <span class="visually-hidden">未読メッセージ</span>
                                </span>
                            @endif
                        </a>
                    </li>

                    {{-- ★マッチング通知アイコンも同様に修正★ --}}
                    <li class="nav-item me-3">
                        <a class="nav-link d-flex align-items-center" href="/matching/requests">
                            <i class="bi bi-hand-index-thumb-fill fs-5"></i>
                            <span>マッチング</span> {{-- テキストをspanで囲む --}}
                            @if (Auth::user()->unconfirmed_matching_count > 0)
                                <span class="badge rounded-pill bg-warning text-dark ms-1 position-relative" style="top: -5px; left: 0px;"> {{-- ★変更点★ --}}
                                    {{ Auth::user()->unconfirmed_matching_count }}
                                    <span class="visually-hidden">未確認マッチング</span>
                                </span>
                            @endif
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
                        <a class="nav-link" href="/matching/history">マッチング履歴</a>
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

@media (max-width: 991.98px) { /* Bootstrapのlgブレイクポイント以下 */
    /* ナビバーのトグルボタンが開いた時のメニュー項目調整 */
    .navbar-collapse .nav-item .nav-link.position-relative .badge {
        position: static !important; /* 絶対配置を解除し、通常フローに沿って配置 */
        margin-left: 5px !important; /* テキストとの間に余白を追加 */
        transform: none !important; /* transformをリセット */
        display: inline-block !important; /* インラインブロックとして表示 */
        top: auto !important; /* topプロパティもリセット */
        right: auto !important; /* rightプロパティもリセット */
    }
}

</style>
@endpush
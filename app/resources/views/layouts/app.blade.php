<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'SkillSwap')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="vapid-public-key" content="{{ config('webpush.vapid.public_key') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
                        <a class="nav-link" href="/signup">新規登録</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/login">ログイン</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/skill/search">スキル検索</a>
                    </li>
                @else
                    {{-- 管理者またはモデレーター用のリンクを分岐して表示 --}}
                    @if (Auth::user()->isAdmin())
                        <li class="nav-item">
                            <a class="nav-link" href="/admin">管理者ページ</a>
                        </li>
                    @elseif (Auth::user()->isModerator())
                        <li class="nav-item">
                            <a class="nav-link" href="/moderator/reports">通報管理</a> {{-- ★ここを変更★ --}}
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
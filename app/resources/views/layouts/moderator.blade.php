<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- タイトルをモデレーター用に固定 --}}
    <title>@yield('title', config('app.name', 'Laravel') . ' | Moderator')</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    @stack('styles')

</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">

        <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
            <div class="container-fluid">
                {{-- モデレーター専用なので、ブランドリンクは直接モデレーターの通報管理インデックスへ --}}
                <a class="navbar-brand" href="{{ route('moderator.reports.index') }}">
                    モデレーターページ
                </a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#moderatorNavbar" aria-controls="moderatorNavbar" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="moderatorNavbar">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        {{-- モデレーターには通報管理のみを表示 --}}
                        <li class="nav-item">
                            <a class="nav-link @if(request()->routeIs('moderator.reports.*')) active @endif" href="{{ route('moderator.reports.index') }}">通報管理</a>
                        </li>
                    </ul>

                    <ul class="navbar-nav ms-auto">
                        {{-- メインページへのリンク --}}
                        <li class="nav-item me-2"> {{-- ★変更点1: メインページリンクとログアウトボタンの間に少しスペースを追加 (me-2) --}}
                            <a class="nav-link" href="/main">メインページへ</a>
                        </li>
                        
                    <li class="nav-item">
                        <a href="#" class="nav-link" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            ログアウト
                        </a>
                        <form id="logout-form" action="/logout" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </li>
                    </ul>
                </div>
            </div>
        </nav>
        {{-- モデレーター用ヘッダー (Navbar) 終了 --}}

        @if (trim($__env->yieldContent('header')))
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    @yield('header')
                </div>
            </header>
        @endif

        <main>
            @yield('content')
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    @stack('scripts')
</body>
</html>
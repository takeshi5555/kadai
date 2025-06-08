<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Laravel') . ' | Admin')</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    @stack('styles')

</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">

        <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
            <div class="container-fluid">
                @auth('admin')
                
                     @if (Auth::user()->isAdmin())
                        <a class="navbar-brand" href="{{ route('admin.index') }}">
                            管理者ページ
                        </a>
                    @elseif (Auth::user()->isModerator())
                        <a class="navbar-brand" href="{{ route('admin.reports.index') }}">
                            モデレーターページ
                        </a>
                    @else
                        {{-- admin/moderator以外のロール（例: 'user'）でadminページにアクセスした場合のフォールバック --}}
                        <a class="navbar-brand" href="/main">
                            メインページへ
                        </a>
                    @endif
                @else
                    {{-- 認証されていない場合のデフォルト表示 --}}
                    <a class="navbar-brand" href="{{ route('admin.index') }}">
                        管理者ページ
                    </a>
                @endauth

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar" aria-controls="adminNavbar" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="adminNavbar">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        {{-- ダッシュボードリンク (管理者のみ表示、モデレーターは非表示) --}}
                        @auth('admin')
                        
                            @if (Auth::guard('admin')->user()->isAdmin())
                                <li class="nav-item">
                                    <a class="nav-link @if(request()->routeIs('admin.index')) active @endif" aria-current="page" href="{{ route('admin.index') }}">ダッシュボード</a>
                                </li>
                            @endif
                        @endauth
                        
                        {{-- ユーザー管理 (管理者のみ) --}}
                        @can('access-admin-page', Auth::guard('admin')->user())
                            <li class="nav-item">
                                <a class="nav-link @if(request()->routeIs('admin.users.*')) active @endif" href="{{ route('admin.users.index') }}">ユーザー管理</a>
                            </li>
                        @endcan

                        {{-- スキル管理 (管理者のみ) --}}
                        @can('access-admin-page', Auth::guard('admin')->user())
                            <li class="nav-item">
                                <a class="nav-link @if(request()->routeIs('admin.skills.*')) active @endif" href="{{ route('admin.skills.index') }}">スキル管理</a>
                            </li>
                        @endcan

                        {{-- 通報管理 (管理者・モデレーター共通) --}}
                        @can('access-moderator-report-management', Auth::guard('admin')->user())
                            <li class="nav-item">
                                <a class="nav-link @if(request()->routeIs('admin.reports.*')) active @endif" href="{{ route('admin.reports.index') }}">通報管理</a>
                            </li>
                        @endcan
                    </ul>

                    <ul class="navbar-nav ms-auto">
                        {{-- メインページへのリンク --}}
                        <li class="nav-item">
                            <a class="nav-link" href="/main">メインページへ</a>
                        </li>
                        
                        @auth('admin')
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarUserDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    {{ Auth::guard('admin')->user()->name }}
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarUserDropdown">
                                    {{-- 管理者プロフィールのルートは、もしあればここに記述。なければ削除。 --}}
                                    {{-- <li><a class="dropdown-item" href="{{ route('admin.profile.edit') }}">プロフィール</a></li> --}}
                                    {{-- 上のコメントアウトを解除する際は、この区切り線も有効にしてください。 --}}
                                    {{-- <li><hr class="dropdown-divider"></li> --}}

                                    <li>
                                        <form method="POST" action="{{ route('admin.logout') }}">
                                            @csrf
                                            <button type="submit" class="dropdown-item">ログアウト</button>
                                        </form>
                                    </li>
                                </ul>
                            </li>
                        @else
                            {{-- 管理者ログインページへのリンク（必要であれば） --}}
                            {{-- ルート定義に admin.login があれば有効にしてください --}}
                            {{-- <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.login') }}">管理者ログイン</a>
                            </li> --}}
                        @endauth
                    </ul>
                </div>
            </div>
        </nav>
        {{-- 管理者用ヘッダー (Navbar) 終了 --}}

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
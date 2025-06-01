<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'SkillSwap')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
 
    <style>
        /* --- ここからボタンのスタイルを追加 --- */
        .main-cta-buttons {
            text-align: center; /* ボタンを中央寄せにする */
            margin-top: 20px;
            margin-bottom: 40px; /* 下に他のコンテンツとの間に余白 */
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            font-size: 16px;
            font-weight: bold;
            text-decoration: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease, color 0.3s ease;
            margin: 5px; /* ボタン間の余白 */
        }

        .btn-primary {
            background-color: #007bff; /* 青系 */
            color: white;
            border: 1px solid #007bff;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .btn-secondary {
            background-color: #6c757d; /* 灰色系 */
            color: white;
            border: 1px solid #6c757d;
        }

        .btn-secondary:hover {
            background-color: #545b62;
        }

        .btn-success {
            background-color: #28a745; /* 緑系 */
            color: white;
            border: 1px solid #28a745;
        }

        .btn-success:hover {
            background-color: #1e7e34;
        }

        .featured-reviews {
            margin-top: 20px;
            display: flex;
            overflow-x: auto; /* 横スクロール */
            padding-bottom: 10px; /* スクロールバーとの間隔 */
        }

        .review-card {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-right: 20px; /* レビュー間の間隔 */
            min-width: 300px; /* カードの最小幅 */
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .skill-offered-info {
            font-size: 1.1em;
            margin-bottom: 10px;
            /* display: flex; */ /* ★ここをコメントアウトまたは削除 */
            /* justify-content: space-between; */ /* ★ここをコメントアウトまたは削除 */
            /* align-items: center; */ /* ★ここをコメントアウトまたは削除 */
            white-space: nowrap; /* テキスト全体が改行されないように */
            overflow: hidden; /* はみ出した部分を隠す */
            text-overflow: ellipsis; /* はみ出した部分を...で表示 */
        }

        .skill-name-highlight {
            color: #007bff;
            font-weight: bold;
        }
        .review-text {
            font-style: italic;
            margin-bottom: 10px;
            color: #555; /* コメントの文字色を調整 */
        }

        .review-details {
            font-size: 0.9em;
            color: #777;
        }

        .review-author {
            font-weight: bold;
        }

        /* レスポンシブ対応: 小さい画面ではボタンを縦に並べる */
        @media (max-width: 768px) {
            .main-cta-buttons {
                display: flex;
                flex-direction: column; /* 縦並び */
                align-items: center; /* 中央寄せ */
            }
            .btn {
                width: 80%; /* 幅を広げる */
            }
        }
        /* --- ここまでボタンのスタイルを追加 --- */

        body { font-family: sans-serif; margin: 0; }
        /* header, .logo, .menu-toggle, .menu-content の既存スタイルは
           Bootstrap Navbarクラスによって上書きされる可能性が高いですが、
           明示的に残しておきます。 */
        header { /* background: #f0f0f0; padding: 10px 20px; display: flex; justify-content: space-between; align-items: center; */ } /* Bootstrap Navbarで置き換えるためコメントアウト推奨 */
        .logo { /* font-weight: bold; font-size: 20px; */ } /* navbar-brand, fw-bold, fs-5 で置き換え */
        .menu-toggle { /* cursor: pointer; */ } /* navbar-toggler で置き換え */
        /* 初期状態は閉じておく */
        .menu-content { /* display: none; position: absolute; right: 20px; top: 60px; background: white; border: 1px solid #ccc; padding: 10px; z-index: 1000; */ } /* collapse, dropdown-menu で置き換え */
        .menu-content a { /* display: block; margin: 5px 0; text-decoration: none; color: blue; */ } /* nav-link, dropdown-item で置き換え */
        .menu-content button { /* background:none; border:none; padding:0; margin:5px 0; cursor:pointer; color:blue; display: block; width: 100%; text-align: left;*/ } /* btn btn-link nav-link, dropdown-item で置き換え */

        .skill-grid {
            display: flex; /* Flexboxを有効にする */
            flex-wrap: wrap; /* アイテムを折り返して次の行に表示する */
            gap: 20px; /* アイテム間の隙間 */
            justify-content: flex-start; /* アイテムを左寄せにする */
        }

        .skill-card {
            flex: 0 1 calc(33.333% - 20px); /* 1行に3つ並べる計算 */
            box-sizing: border-box; /* paddingとborderを幅に含める */
            min-width: 280px; /* 小さすぎる画面での最小幅（必要に応じて調整） */
        }

        .card { /* 既存のcardクラスに対するスタイル調整（必要であれば） */
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            height: 100%; /* 高さ揃え */
        }

        .card-body {
            padding: 15px;
        }

        /* ★ここから追加するCSS: カテゴリリストのスタイル */
        .category-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px; /* カテゴリアイテム間の隙間 */
            margin-bottom: 20px; /* 下に少し余白 */
        }

        .category-item {
            background-color: #e9e9e9; /* 背景色 */
            padding: 8px 15px;
            border-radius: 20px; /* 角丸 */
            text-decoration: none; /* 下線なし */
            color: #333; /* 文字色 */
            font-weight: bold;
            transition: background-color 0.3s ease; /* ホバー時のアニメーション */
        }

        .category-item:hover {
            background-color: #dcdcdc; /* ホバー時の背景色 */
            color: #000;
        }
        /* ★ここまで追加するCSS */
        /* レスポンシブ対応（画面幅が狭いときに調整） */
        @media (max-width: 992px) { /* Medium (md) よりも小さい画面 */
            .skill-card {
                flex: 0 1 calc(50% - 20px); /* 1行に2つ並べる */
            }
        }

        @media (max-width: 768px) { /* Small (sm) よりも小さい画面 */
            .skill-card {
                flex: 0 1 100%; /* 1行に1つ並べる */
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    {{-- ヘッダー (Bootstrap Navbar) --}}
    <header>
        <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
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
        </nav>
    </header>

    <main class="py-4"> {{-- メインコンテンツの上下に余白 --}}
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
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'SkillSwap')</title>
     <meta name="csrf-token" content="{{ csrf_token() }}">
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
        /* white-space: nowrap; */ /* 親要素で制御するので不要 */
        /* overflow: hidden; */ /* 親要素で制御するので不要 */
        /* text-overflow: ellipsis; */ /* 親要素で制御するので不要 */
        /* max-width: 60%; */ /* 不要な場合が多い */
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
        header { background: #f0f0f0; padding: 10px 20px; display: flex; justify-content: space-between; align-items: center; }
        .logo { font-weight: bold; font-size: 20px; }
        .menu-toggle { cursor: pointer; }
        /* 初期状態は閉じておく */
        .menu-content { display: none; position: absolute; right: 20px; top: 60px; background: white; border: 1px solid #ccc; padding: 10px; z-index: 1000; }
        .menu-content a { display: block; margin: 5px 0; text-decoration: none; color: blue; } 
        .menu-content button { background:none; border:none; padding:0; margin:5px 0; cursor:pointer; color:blue; display: block; width: 100%; text-align: left;}

        .skill-grid {
            display: flex; /* Flexboxを有効にする */
            flex-wrap: wrap; /* アイテムを折り返して次の行に表示する */
            gap: 20px; /* アイテム間の隙間 */
            justify-content: flex-start; /* アイテムを左寄せにする */
        }

        .skill-card {
            flex: 0 1 calc(33.333% - 20px); /* 1行に3つ並べる計算 */
            /* flex-grow: 0 (拡大しない), flex-shrink: 1 (縮小する), flex-basis: calc(33.333% - 20px) (基本幅) */
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
</head>
<body>
    <header>
        <div class="logo">
            <a href="/main">SkillSwap</a>
        </div>

        <p>{{ Auth::check() ? 'ログイン中' : 'ログアウト中' }}</p>

        <div class="menu">
            <div class="menu-toggle">☰ メニュー</div>
            <div class="menu-content" id="menu-content">
                @guest
                    <a href="/signup">新規登録</a>
                    <a href="/login">ログイン</a>
                    <a href="/skill/search">スキル検索</a>
                @else
                    <a href="/mypage">マイページ</a>
                    <a href="/skill/search">スキル検索</a>
                    <a href="/skill/manage">スキル管理</a>
                    <a href="/matching/history">マッチング履歴</a>
                    <form method="POST" action="/logout" style="margin: 0;">
                        @csrf
                        <button type="submit">ログアウト</button>
                    </form>
                @endguest
            </div>
        </div>
    </header>

    <main style="padding: 20px;">
        @yield('content')
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.querySelector('.menu-toggle');
            const menuContent = document.getElementById('menu-content');

            // メニュー開閉機能
            menuToggle.addEventListener('click', function(event) {
                event.stopPropagation();
                menuContent.style.display = (menuContent.style.display === 'block') ? 'none' : 'block';
            });

            // メニューの外側をクリックしたときに閉じるやつやつ
            document.addEventListener('click', function(event) {
                if (!menuContent.contains(event.target) && !menuToggle.contains(event.target)) {
                    menuContent.style.display = 'none';
                }
            });
        });
    </script>
     <script src="{{ asset('js/app.js') }}" defer></script> @stack('scripts')
</body>
</html>
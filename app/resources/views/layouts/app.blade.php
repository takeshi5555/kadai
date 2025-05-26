<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'SkillSwap')</title>
    <style>
        body { font-family: sans-serif; margin: 0; }
        header { background: #f0f0f0; padding: 10px 20px; display: flex; justify-content: space-between; align-items: center; }
        .logo { font-weight: bold; font-size: 20px; }
        .menu-toggle { cursor: pointer; }
        /* 初期状態は閉じておく */
        .menu-content { display: none; position: absolute; right: 20px; top: 60px; background: white; border: 1px solid #ccc; padding: 10px; z-index: 1000; }
        .menu-content a { display: block; margin: 5px 0; text-decoration: none; color: blue; } 
        .menu-content button { background:none; border:none; padding:0; margin:5px 0; cursor:pointer; color:blue; display: block; width: 100%; text-align: left;}
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <a href="/">SkillSwap</a>
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
</body>
</html>
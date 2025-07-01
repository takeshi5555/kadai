@extends('layouts.app') {{-- レイアウトを継承 --}}

@section('title', 'ログイン - スキル検索') {{-- ページタイトル --}}

@section('content') {{-- content セクションを開始 --}}

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg p-4">
                <div class="card-body">
                    <h1 class="card-title text-center mb-4">ログイン</h1>

                    @if (session('status'))
                        <div class="alert alert-success text-center mb-4" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger fade show" role="alert">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- フォーム --}}
                    <form method="POST" action="{{ url('/login') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label">メールアドレス</label>
                            <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">パスワード</label>
                            <input type="password" class="form-control" id="password" name="password" required autocomplete="current-password">
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">ログイン状態を保持する</label>
                        </div>
                        <div class="d-grid gap-2 mb-3">
                            {{-- ログインボタンのクラスを修正 --}}
                            <button type="submit" class="btn btn-primary btn-skillswap btn-lg">ログイン</button>
                        </div>
                    </form>
                    <div class="text-center mt-4">
                        {{-- Googleログインボタンのクラスを修正 --}}
                        <a href="{{ route('google.redirect') }}" class="btn btn-google btn-lg">
                            <i class="bi bi-google me-2"></i>Googleでログイン
                        </a>
                    </div>
                    <div class="text-center mt-4">
                        <a href="{{ url('/password/reset') }}" class="text-decoration-none text-skillswap-link">パスワードを忘れた方</a>
                    </div>

                    <div class="text-center mt-2">
                        <a href="{{ url('/signup') }}" class="text-decoration-none text-skillswap-link">新規登録はこちら</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ここからCSSを追加 --}}
<style>
    /* CSSカスタムプロパティ（変数）の導入 */
    :root {
        --skillswap-primary: #4A90E2; /* 明るいブルー */
        --skillswap-primary-dark: #357ABD; /* 少し濃いブルー（ホバー用） */
        --skillswap-text-light: #ffffff; /* 明るいテキスト */
        --skillswap-text-dark: #333333; /* 暗いテキスト */
        --skillswap-link: #4A90E2; /* リンクカラー */
    }

    /* ログインボタンの調整 */
    .btn-skillswap {
        background-color: var(--skillswap-primary);
        border-color: var(--skillswap-primary);
        color: var(--skillswap-text-light);
        transition: background-color 0.2s ease-in-out, border-color 0.2s ease-in-out;
    }

    .btn-skillswap:hover {
        background-color: var(--skillswap-primary-dark);
        border-color: var(--skillswap-primary-dark);
        color: var(--skillswap-text-light); /* ホバー時も白を維持 */
    }

    /* Googleログインボタンの調整 */
    .btn-google {
        background-color: #DB4437; /* Google Red */
        border-color: #DB4437;
        color: var(--skillswap-text-light);
        transition: background-color 0.2s ease-in-out, border-color 0.2s ease-in-out;
        display: flex; /* アイコンとテキストを中央に揃えるため */
        align-items: center;
        justify-content: center;
        font-weight: bold; /* 少し太字にする */
    }

    .btn-google:hover {
        background-color: #C23326; /* Darker Google Red */
        border-color: #C23326;
        color: var(--skillswap-text-light);
    }

    /* リンクカラーの調整 */
    .text-skillswap-link {
        color: var(--skillswap-link) !important; /* !important でBootstrapのスタイルを上書き */
    }

    .text-skillswap-link:hover {
        color: var(--skillswap-primary-dark) !important;
    }

    /* カードの調整 */
    .card {
        border-radius: 10px; /* 角を少し丸くする */
        border: none; /* デフォルトの境界線をなくす */
    }

    .card-body {
        padding: 2.5rem; /* パディングを少し広めにする */
    }

    /* フォームラベルのフォントサイズ調整 */
    .form-label {
        font-size: 0.95rem;
        color: var(--skillswap-text-dark);
    }

    /* アラートメッセージの微調整 */
    .alert-success {
        background-color: #e6ffe6; /* 薄い緑 */
        color: #1a731a; /* 濃い緑のテキスト */
        border-color: #b3ffb3; /* 緑のボーダー */
    }

    .alert-danger {
        background-color: #ffe6e6; /* 薄い赤 */
        color: #731a1a; /* 濃い赤のテキスト */
        border-color: #ffb3b3; /* 赤のボーダー */
    }
</style>
@endsection {{-- content セクションの終了 --}}
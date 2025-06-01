@extends('layouts.app')

@section('title', '新規登録')

@section('content')

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            <div class="card shadow-lg p-4">
                <div class="card-body">
                    <h1 class="card-title text-center mb-4">新規登録</h1>

                    {{-- エラーメッセージ表示 --}}
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
                    {{-- 修正ボタンで戻ってきた場合は、GETリクエストになるため、actionを修正する必要がある場合があります。
                         もし、確認ページからGETでsignupに戻るルートが定義されているなら、formのactionはそのまま'/signup'で問題ありません。
                         POSTで戻す場合は、signup_confirmコントローラーでredirect()->back()->withInput()などを使う必要がありますが、
                         通常はGETで戻ってold()を使うのがシンプルです。 --}}
                    <form method="POST" action="/signup">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">ユーザー名</label>
                            {{-- ここに old('name') を追加 --}}
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>
                            @error('name')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">メールアドレス</label>
                            {{-- ここに old('email') を追加 --}}
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required autocomplete="email">
                            @error('email')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">パスワード</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required autocomplete="new-password">
                            @error('password')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label">パスワード確認</label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required autocomplete="new-password">
                        </div>

                        <div class="d-grid gap-2 mb-3">
                            <button type="submit" class="btn btn-primary btn-lg">入力確認</button>
                        </div>
                    </form>

                    <div class="text-center mt-4">
                        既にアカウントをお持ちですか？ <a href="{{ url('/login') }}" class="text-decoration-none">ログインはこちら</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
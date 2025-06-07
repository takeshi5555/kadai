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
                            <button type="submit" class="btn btn-primary btn-lg">ログイン</button>
                        </div>
                    </form>
                    <div class="text-center mt-4">
                    <a href="{{ route('google.redirect') }}" class="btn btn-danger">Googleでログイン</a>
                    </div>
                    <div class="text-center mt-4">
                        <a href="{{ url('/password/reset') }}" class="text-decoration-none">パスワードを忘れた方</a>
                    </div>

                    <div class="text-center mt-2">
                        <a href="{{ url('/signup') }}" class="text-decoration-none">新規登録はこちら</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
{{-- resources/views/auth/pwd_reset.blade.php --}}
@extends('layouts.app')

@section('title', 'パスワード再設定')

@section('content')

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            <div class="card shadow-lg p-4">
                <div class="card-body">
                    <h1 class="card-title text-center mb-4">パスワード再設定</h1>

                    {{-- フラッシュメッセージ表示 --}}
                    @if (session('message'))
                        <div class="alert alert-success text-center mb-4" role="alert">
                            {{ session('message') }}
                        </div>
                    @endif

                    {{-- エラーメッセージ表示 (バリデーションエラーなど) --}}
                    @if ($errors->any())
                        <div class="alert alert-danger fade show" role="alert">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="/password/reset">
                        @csrf
                        <div class="mb-3">
                            <label for="email" class="form-label">メールアドレス</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                            @error('email')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="d-grid gap-2 mb-3">
                            <button type="submit" class="btn btn-primary btn-lg">再設定メールを送信</button>
                        </div>
                    </form>

                    <div class="text-center mt-3">
                        <a href="{{ url('/login') }}" class="text-decoration-none">ログイン画面に戻る</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
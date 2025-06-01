{{-- resources/views/auth/pwd_form.blade.php --}}
@extends('layouts.app')

@section('title', '新しいパスワード設定')

@section('content')

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            <div class="card shadow-lg p-4">
                <div class="card-body">
                    <h1 class="card-title text-center mb-4">新しいパスワード設定</h1>

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

                    <form method="POST" action="/password/form">
                        @csrf
                        {{-- URLから取得したtokenとemailをhiddenフィールドで送信 --}}
                        <input type="hidden" name="token" value="{{ request()->get('token') }}">
                        <input type="hidden" name="email" value="{{ request()->get('email') }}">

                        <div class="mb-3">
                            <label for="password" class="form-label">新しいパスワード</label>
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
                            <button type="submit" class="btn btn-primary btn-lg">パスワードを登録</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
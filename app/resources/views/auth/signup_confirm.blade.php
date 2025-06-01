@extends('layouts.app')

@section('title', '新規登録確認')

@section('content')

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            <div class="card shadow-lg p-4">
                <div class="card-body">
                    <h1 class="card-title text-center mb-4">新規登録確認</h1>

                    <p class="lead text-center mb-4">以下の内容で登録します。よろしければ「登録」ボタンを押してください。</p>

                    <div class="mb-3">
                        <label class="form-label fw-bold">ユーザー名:</label>
                        <p class="form-control-plaintext">{{ session('name') }}</p>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">メールアドレス:</label>
                        <p class="form-control-plaintext">{{ session('email') }}</p>
                    </div>

                    {{-- 最終登録フォーム --}}
                    <form method="POST" action="/signup/confirm">
                        @csrf
                        {{-- パスワードはセッションから再利用するため、hiddenで直接渡さない --}}
                        <div class="d-grid gap-2 mb-3">
                            <button type="submit" class="btn btn-primary btn-lg">登録</button>
                        </div>
                    </form>

                    <div class="text-center mt-3">
                        {{-- 修正するボタンをPOSTフォームに変更 --}}
                        <form method="POST" action="/signup/back"> {{-- 新しいルートを定義する --}}
                            @csrf
                            {{-- 修正時に元の入力値をhiddenフィールドとして送信 --}}
                            <input type="hidden" name="name" value="{{ session('name') }}">
                            <input type="hidden" name="email" value="{{ session('email') }}">
                            {{-- パスワードは再入力を求めるか、セッションに保持している場合のみ送信（非推奨） --}}
                            {{-- <input type="hidden" name="password" value="{{ session('signup_password') }}"> --}} {{-- 安全でないので注意 --}}
                            <button type="submit" class="btn btn-secondary mt-2">修正する</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
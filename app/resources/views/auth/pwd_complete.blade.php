{{-- resources/views/auth/pwd_complete.blade.php --}}
@extends('layouts.app')

@section('title', 'パスワード再設定完了')

@section('content')

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            <div class="card shadow-lg p-4">
                <div class="card-body text-center">
                    <h1 class="card-title mb-4">パスワード再設定が完了しました</h1>

                    <p class="lead mb-4">新しいパスワードでログインしてください。</p>

                    <a href="/login" class="btn btn-primary btn-lg mt-3">ログイン画面へ</a>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>マイページ</h1>
        <p>こんにちは、{{ Auth::user()->name }}さん！</p>
        <p>メールアドレス: {{ Auth::user()->email }}</p>
        {{-- ここにユーザーのスキルや設定などを表示 --}}
    </div>
@endsection
<!-- resources/views/auth/login.blade.php -->
@extends('layouts.app')

@section('title', 'スキル検索')

@section('content')


    <h1>ログインページ</h1>

        @if ($errors->any())
        <div style="color:red;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form method="POST" action="{{ url('/login') }}">
        @csrf
        <label>メールアドレス: <input type="email" name="email" required></label><br>
        <label>パスワード: <input type="password" name="password" required></label><br>
        <button type="submit">ログイン</button>
    </form>
    <a href="{{ url('/password/reset') }}">パスワードを忘れた方</a><br>
    <a href="{{ url('/signup') }}">新規登録</a>
</body>
</html>

@endsection
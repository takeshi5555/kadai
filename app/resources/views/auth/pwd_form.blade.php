<!DOCTYPE html> 
<html>
<head><title>パスワード変更</title></head>
<body>
    <h1>新しいパスワード設定</h1>

    @if ($errors->any())
        <div style="color:red;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="/password/form">
        @csrf
        <input type="hidden" name="token" value="{{ request()->get('token') }}">
        <input type="hidden" name="email" value="{{ request()->get('email') }}">

        <label>新しいパスワード: <input type="password" name="password" required></label><br>
        <label>パスワード確認: <input type="password" name="password_confirmation" required></label><br>
        <button type="submit">登録</button>
    </form>
</body>
</html>

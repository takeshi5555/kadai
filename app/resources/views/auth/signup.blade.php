<!-- resources/views/auth/signup.blade.php -->
<!DOCTYPE html>
<html>
<head><title>新規登録</title></head>
<body>
    <h1>新規登録ページ</h1>
    @if ($errors->any())
    <div style="color:red;">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif
    <form method="POST" action="/signup">
        @csrf
        <label>ユーザー名: <input type="text" name="name" required></label><br>
        <label>メールアドレス: <input type="email" name="email" required></label><br>
        <label>パスワード: <input type="password" name="password" required></label><br>
        <label>パスワード確認: <input type="password" name="password_confirmation" required></label><br>
        <button type="submit">入力確認</button>
    </form>
</body>
</html>

<!-- resources/views/auth/signup_confirm.blade.php -->
<!DOCTYPE html>
<html>
<head><title>登録確認</title></head>
<body>
    <h1>新規登録確認</h1>
    <p>以下の内容で登録します。</p>
    <p>ユーザー名: {{ session('name') }}</p>
    <p>メールアドレス: {{ session('email') }}</p>

    <form method="POST" action="/signup/confirm">
        @csrf
        <button type="submit">登録</button>
    </form>
</body>
</html>

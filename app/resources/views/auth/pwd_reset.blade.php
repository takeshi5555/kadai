<!DOCTYPE html>
<html>
<head><title>パスワード再設定</title></head>
<body>
    <h1>パスワード再設定</h1>
    @if (session('message'))
        <p style="color:green">{{ session('message') }}</p>
    @endif
    <form method="POST" action="/password/reset">
        @csrf
        <label>メールアドレス: <input type="email" name="email" required></label><br>
        <button type="submit">メール送信</button>
    </form>
</body>
</html>

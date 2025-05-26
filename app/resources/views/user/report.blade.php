<!DOCTYPE html>
<html>
<head><title>メインページ</title></head>
<body>
    <h1>通報</h1>
<form method="POST" action="/report/confirm">
    @csrf
    <label>通報理由:</label>
    <select name="reason">
        <option value="迷惑行為">迷惑行為</option>
        <option value="不適切な内容">不適切な内容</option>
    </select><br>
    <button type="submit" onclick="return confirm('本当に通報しますか？');">通報する</button>
</form>
</body>
</html>
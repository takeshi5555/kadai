<!DOCTYPE html>
<html>
<head><title>メインページ</title></head>
<body>
    <h1>マイページ</h1>
<a href="/skill/manage">スキル管理</a><br>
<a href="/message">メッセージ</a><br>

<!-- インポート機能（ファイルアップロード） -->
<form method="POST" action="/skill/import" enctype="multipart/form-data">
    @csrf
    <input type="file" name="skill_file"><br>
    <button type="submit">インポート</button>
</form>

<!-- ダウンロード -->
<a href="/matching/history/download">マッチング履歴ダウンロード</a>
</body>
</html>
<!DOCTYPE html>
<html>
<head><title>インポート確認</title></head>
<body>
    <h1>インポート内容確認</h1>

    @if (session('import_has_error'))
        <p style="color:red;">※ 一部の行にエラーがあります。修正後に再アップロードしてください。</p>
    @endif

    @if (empty($skills))
        <p>データが読み込まれていません。</p>
    @else
        <form method="POST" action="/skill/import/execute">
            @csrf
            <table border="1" cellpadding="5">
                <tr>
                    <th>スキル名</th>
                    <th>カテゴリ</th>
                    <th>説明</th>
                    <th>ステータス</th>
                </tr>
                @foreach ($skills as $row)
                    <tr>
                        <td>{{ $row['title'] }}</td>
                        <td>{{ $row['category'] }}</td>
                        <td>{{ $row['description'] }}</td>
                        <td>
                            @if ($row['error'])
                                <span style="color:red">{{ $row['error'] }}</span>
                            @else
                                OK
                            @endif
                        </td>   
                    </tr>
                @endforeach
            </table>
            <br>
            @if (!session('import_has_error'))
                <button type="submit">登録</button>
            @endif
            <a href="/skill/manage"><button type="button">ファイル再選択</button></a>
        </form>
    @endif
</body>
</html>

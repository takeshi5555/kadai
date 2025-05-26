@extends('layouts.app')

@section('title', 'スキル管理')

@section('content')
    <h1>スキル管理</h1>

@if (session('message'))
    <p style="color:green">{{ session('message') }}</p>
@endif

<h2>新規スキル登録</h2>
<form method="POST" action="/skill">
    @csrf
    <input type="text" name="title" placeholder="スキル名" required><br>
    <input type="text" name="category" placeholder="カテゴリ" required><br>
    <textarea name="description" placeholder="説明" required></textarea><br>
    <button type="submit">登録</button>
</form>

<h2>登録済みスキル</h2>
<table border="1">
<tr><th>スキル名</th><th>カテゴリ</th><th>操作</th></tr>
    @foreach ($skills as $skill)
        <tr id="skill-row-{{ $skill->id }}">
            <td colspan="3">
                <div id="view-{{ $skill->id }}">
                    <strong>{{ $skill->title }}</strong>（{{ $skill->category }}）<br>
                    {{ $skill->description }}
                    <br>
                    <button onclick="toggleEdit({{ $skill->id }})">編集</button>
                    <form method="POST" action="/skill/{{ $skill->id }}/delete" style="display:inline">
                        @csrf
                        <button type="submit" onclick="return confirm('削除しますか？')">削除</button>
                    </form>
                </div>

                <div id="edit-{{ $skill->id }}" style="display:none;">
                    <form method="POST" action="/skill/{{ $skill->id }}/update">
                        @csrf
                        <input type="text" name="title" value="{{ $skill->title }}" required><br>
                        <input type="text" name="category" value="{{ $skill->category }}" required><br>
                        <textarea name="description" required>{{ $skill->description }}</textarea><br>
                        <button type="submit">更新</button>
                        <button type="button" onclick="toggleEdit({{ $skill->id }})">キャンセル</button>
                    </form>
                </div>
            </td>
        </tr>
    @endforeach
</table>
<script>
function toggleEdit(id) {
    const view = document.getElementById('view-' + id);
    const edit = document.getElementById('edit-' + id);

    if (view.style.display === 'none') {
        view.style.display = 'block';
        edit.style.display = 'none';
    } else {
        view.style.display = 'none';
        edit.style.display = 'block';
    }
}
</script>





<!-- 新規登録 -->
<button onclick="alert('モーダルでスキル登録')">スキル新規登録</button>

<!-- インポート -->
@if ($errors->any())
    <div style="color:red;">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<form method="POST" action="/skill/import" enctype="multipart/form-data">
    @csrf
    <input type="file" name="skill_file" accept=".csv,.xlsx" required>
    <button type="submit">ファイル読み込み</button>
</form>

@endsection
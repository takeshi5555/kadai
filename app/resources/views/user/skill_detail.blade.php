@extends('layouts.app')

@section('title', 'スキル検索')

@section('content')
<h1>スキル詳細</h1>
<p><strong>スキル名：</strong>{{ $skill->title }}</p>
<p><strong>カテゴリ：</strong>{{ $skill->category }}</p>
<p><strong>説明：</strong>{{ $skill->description }}</p>

<a href="/matching/apply/{{ $skill->id }}">マッチング申し込み</a><br>
<a href="/skill/search">戻る</a>
</body>
</html>

@endsection
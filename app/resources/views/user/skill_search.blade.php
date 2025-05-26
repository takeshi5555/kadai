@extends('layouts.app')

@section('title', 'スキル検索')

@section('content')
    <h1>スキル検索</h1>

    <form method="GET" action="/skill/search">
        <input type="text" name="keyword" placeholder="キーワード" value="{{ request('keyword') }}"><br>
        <select name="category">
            <option value="">カテゴリを選択</option>
            <option value="IT" {{ request('category') == 'IT' ? 'selected' : '' }}>IT</option>
            <option value="語学" {{ request('category') == '語学' ? 'selected' : '' }}>語学</option>
        </select><br>
        <button type="submit">検索</button>
    </form>

    <h2>検索結果</h2>
    <ul>
    @foreach ($skills as $skill)
        <li>
            <strong>{{ $skill->title }}</strong>（{{ $skill->category }}）<br>
            <a href="/skill/detail/{{ $skill->id }}">詳細を見る</a>
        </li>
    @endforeach
    </ul>
@endsection


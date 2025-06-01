@extends('layouts.app') {{-- アプリの基本レイアウトを継承 --}}

@section('title', 'ページが見つかりません')

@section('content')
    <div class="container text-center py-5">
        <h1 class="display-4 text-danger">404</h1>
        <p class="lead">お探しのページは見つかりませんでした。</p>
        <p class="mb-4">URLが間違っているか、ページが削除された可能性があります。</p>
        <a href="{{ url('/main') }}" class="btn btn-primary">メインページに戻る</a>
    </div>
@endsection
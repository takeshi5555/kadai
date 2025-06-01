@extends('layouts.app') {{-- layouts/app.blade.php を継承 --}}

@section('title', 'インポート内容確認') {{-- このページのタイトルを設定 --}}

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h1 class="mb-0 text-center">インポート内容確認</h1>
                    </div>
                    <div class="card-body">
                        @if (session('import_has_error'))
                            <div class="alert alert-danger text-center" role="alert">
                                ※ 一部の行にエラーがあります。修正後に再アップロードしてください。
                            </div>
                        @endif

                        @if (empty($skills))
                            <div class="alert alert-info text-center" role="alert">
                                データが読み込まれていません。
                            </div>
                        @else
                            <form method="POST" action="{{ url('/skill/import/execute') }}">
                                @csrf
                                <div class="table-responsive"> {{-- テーブルが横にはみ出す場合にスクロール可能にする --}}
                                    <table class="table table-bordered table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>スキル名</th>
                                                <th>カテゴリ</th>
                                                <th>説明</th>
                                                <th>ステータス</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($skills as $row)
                                                <tr>
                                                    <td>{{ $row['title'] }}</td>
                                                    <td>{{ $row['category'] }}</td>
                                                    <td>{{ $row['description'] }}</td>
                                                    <td>
                                                        @if ($row['error'])
                                                            <span class="text-danger fw-bold">{{ $row['error'] }}</span>
                                                        @else
                                                            OK
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div> {{-- .table-responsive 終了 --}}

                                <div class="d-flex justify-content-center gap-3 mt-4"> {{-- ボタンを中央に配置し、隙間を設ける --}}
                                    @if (!session('import_has_error'))
                                        <button type="submit" class="btn btn-primary btn-lg">登録</button>
                                    @endif
                                    <a href="{{ url('/skill/manage') }}" class="btn btn-secondary btn-lg">ファイル再選択</a>
                                </div>
                            </form>
                        @endif
                    </div> {{-- .card-body 終了 --}}
                </div> {{-- .card 終了 --}}
            </div> {{-- .col-md-8 終了 --}}
        </div> {{-- .row 終了 --}}
    </div> {{-- .container 終了 --}}
@endsection

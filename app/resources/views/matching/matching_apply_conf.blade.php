@extends('layouts.app')

@section('title', 'マッチング申し込み確認')

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h1 class="mb-4 text-center">マッチング申し込み確認</h1>

                {{-- 相手のスキル情報 --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">相手のスキル</h5>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title"><strong>{{ $receiving->title }}</strong>（{{ $receiving->category }}）</h5>
                        <p class="card-text">{{ $receiving->description }}</p>
                    </div>
                </div>

                {{-- あなたのスキル情報 --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">あなたのスキル</h5>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title"><strong>{{ $offering->title }}</strong>（{{ $offering->category }}）</h5>
                        <p class="card-text">{{ $offering->description }}</p>
                    </div>
                </div>

                {{-- 予約日時 --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">予約日時</h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text fs-5">
                            <strong>{{ \Carbon\Carbon::parse($scheduledAt)->format('Y年m月d日 H時i分') }}</strong>
                        </p>
                    </div>
                </div>

                <div class="d-grid gap-2 mt-4">
                    <form method="POST" action="/matching/apply/execute">
                        @csrf
                        <input type="hidden" name="offering_skill_id" value="{{ $offeringId }}">
                        <input type="hidden" name="receiving_skill_id" value="{{ $receivingId }}">
                        <input type="hidden" name="scheduled_at" value="{{ $scheduledAt }}">

                        <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">申し込みを確定</button>
                        {{-- 「キャンセル」ボタンはtype="button"にし、フォームの外に置くのが良い --}}
                    </form>
                    <a href="/skill/detail/{{ $receiving->id }}" class="btn btn-secondary btn-lg w-100">キャンセルして戻る</a>
                </div>
            </div>
        </div>
    </div>
@endsection


<style>
    .card {
        --bs-card-height: auto;
        height: auto !important;
    }

    .skill-detail-card {
        min-height: 200px; 

    }

    .skill-provider-card {
        min-height: 200px;
    }

</style>
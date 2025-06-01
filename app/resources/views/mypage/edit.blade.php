@extends('layouts.app')

@section('title', 'プロフィール')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            {{-- プロフィール更新セクション（既存） --}}
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            {{-- パスワード更新セクション（既存） --}}
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            {{-- ユーザー削除セクション（既存） --}}
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>

            {{-- ★追加: マッチング履歴エクスポートセクション --}}
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900">
                                マッチング履歴のエクスポート
                            </h2>
                            <p class="mt-1 text-sm text-gray-600">
                                期間を指定してマッチング履歴をCSV形式でエクスポートします。就職活動や学習の振り返りにご活用ください。
                            </p>
                        </header>

                        <button type="button" class="mt-4 btn btn-primary" data-bs-toggle="modal" data-bs-target="#exportHistoryModal">
                            履歴をエクスポート
                        </button>
                    </section>
                </div>
            </div>
        </div>
    </div>

    {{-- ★追加: エクスポートモーダル --}}
    <div class="modal fade" id="exportHistoryModal" tabindex="-1" aria-labelledby="exportHistoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exportHistoryModalLabel">マッチング履歴のエクスポート設定</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('profile.export.execute') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="start_date" class="form-label">開始日 (任意):</label>
                            <input type="date" class="form-control" id="start_date" name="start_date">
                        </div>
                        <div class="mb-3">
                            <label for="end_date" class="form-label">終了日 (任意):</label>
                            <input type="date" class="form-control" id="end_date" name="end_date">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">ステータスで絞り込む (任意):</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="status_filter[]" value="0" id="statusPending" checked>
                                <label class="form-check-label" for="statusPending">
                                    申請中
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="status_filter[]" value="1" id="statusApproved" checked>
                                <label class="form-check-label" for="statusApproved">
                                    承認済み
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="status_filter[]" value="2" id="statusCompleted" checked>
                                <label class="form-check-label" for="statusCompleted">
                                    完了
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="status_filter[]" value="3" id="statusRejected">
                                <label class="form-check-label" for="statusRejected">
                                    拒否
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">閉じる</button>
                        <button type="submit" class="btn btn-primary">エクスポート</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // 管理者のみがアクセスできるGateを定義 (変更なし)
        Gate::define('access-admin-page', function ($user) {
            return $user->isAdmin();
        });

        Gate::define('can-ban-users', function ($user) {
    return $user->isAdmin() || $user->isModerator(); // 管理者またはモデレーター
});

        // 「通報管理」に管理者またはモデレーターがアクセスできるGateを定義
        // Gate名をより汎用的なものに変更することも検討できます (例: 'manage-reports')
        Gate::define('access-moderator-report-management', function ($user) {
            return $user->isAdmin() || $user->isModerator(); // ★ここを変更しました★
        });
    }
}

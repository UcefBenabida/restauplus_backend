<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Gate::define('isAdmin', function (User $user) {
            return $user->role == 'admin';
        });

        Gate::define('isServer', function (User $user) {
            return $user->role == 'server';
        });

        Gate::define('isCook', function (User $user) {
            return $user->role == 'cook';
        });
    }
}

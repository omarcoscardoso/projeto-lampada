<?php

namespace App\Providers;

use App\Filament\Responses\LoginResponse;
use App\Filament\Responses\RegistrationResponse;
use App\Policies\PermissionPolicy;
use App\Policies\RolePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \Filament\Auth\Http\Responses\Contracts\LoginResponse::class,
            LoginResponse::class
        );

        $this->app->bind(
            \Filament\Auth\Http\Responses\Contracts\RegistrationResponse::class,
            RegistrationResponse::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Permission::class, PermissionPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}

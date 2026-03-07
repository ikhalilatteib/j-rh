<?php

namespace Ikay\JRh;

use Ikay\JRh\Models\Advance;
use Ikay\JRh\Models\Employee;
use Ikay\JRh\Models\Salary;
use Ikay\JRh\Policies\AdvancePolicy;
use Ikay\JRh\Policies\EmployeePolicy;
use Ikay\JRh\Policies\SalaryPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class JRhServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/j-rh.php', 'j-rh');
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'j-rh');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'j-rh');

        Gate::policy(Employee::class, EmployeePolicy::class);
        Gate::policy(Salary::class, SalaryPolicy::class);
        Gate::policy(Advance::class, AdvancePolicy::class);

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/j-rh.php' => config_path('j-rh.php'),
            ], 'j-rh-config');

            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'j-rh-migrations');

            $this->publishes([
                __DIR__.'/../resources/lang' => $this->app->langPath('vendor/j-rh'),
            ], 'j-rh-translations');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/j-rh'),
            ], 'j-rh-views');
        }
    }
}

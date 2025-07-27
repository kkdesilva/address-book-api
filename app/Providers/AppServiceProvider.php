<?php

namespace App\Providers;

use App\Contracts\ContactRepository;
use App\Repositories\JsonFileContactRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bindIf(ContactRepository::class, fn() => new JsonFileContactRepository());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}

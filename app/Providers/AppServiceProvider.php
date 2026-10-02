<?php

namespace App\Providers;

use App\Services\CartService;
use App\Services\Payment\PaymentService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PaymentService::class);
        $this->app->singleton(CartService::class);
    }

    public function boot(): void
    {
        //
    }
}

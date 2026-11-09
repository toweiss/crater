<?php

namespace Crater\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class CustomRouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->routes(function () {
            // Früh laden, damit unser Match zuerst greift
            Route::middleware('web')->group(base_path('routes/custom.php'));
        });
    }
}

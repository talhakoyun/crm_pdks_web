<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\ShiftFollowReportServiceInterface;
use App\Services\ShiftFollowReportService;
use Illuminate\Support\ServiceProvider;

class ShiftFollowServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(ShiftFollowReportServiceInterface::class, ShiftFollowReportService::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(): void
    {
        //
    }
}

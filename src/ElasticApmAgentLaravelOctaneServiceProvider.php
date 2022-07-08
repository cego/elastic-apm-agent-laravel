<?php

namespace Cego\ElasticApmAgentLaravel;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Container\BindingResolutionException;
use Cego\ElasticApmAgentLaravel\Middleware\ApmTransactionGlobalMiddleware;

class ElasticApmAgentLaravelOctaneServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
    }

    /**
     * Bootstrap any application services.
     *
     * @throws BindingResolutionException
     *
     * @return void
     */
    public function boot(): void
    {
        // Push Middleware to global middleware stack
        $kernel = $this->app->make(Kernel::class);
        $kernel->pushMiddleware(ApmTransactionGlobalMiddleware::class);
    }
}

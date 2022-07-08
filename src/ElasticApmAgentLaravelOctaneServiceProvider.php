<?php

namespace Cego\ElasticApmAgentLaravel;

use GenericInstrumenter;
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
        $this->genericAutoInstrumentation('cache', 'cache');
        $this->genericAutoInstrumentation('redis', 'redis');
    }

    /**
     * Adds generic auto instrumentation to a given object in the app
     *
     * @param string $key
     * @param string $type
     *
     * @return void
     */
    private function genericAutoInstrumentation(string $key, string $type): void
    {
        $this->app->extend($key, fn ($object) => new GenericInstrumenter($object, $type));
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

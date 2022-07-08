<?php

namespace Cego\ElasticApmAgentLaravel\Middleware;

use Closure;
use Throwable;
use Elastic\Apm\ElasticApm;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Router;

class ApmTransactionGlobalMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $transaction = ElasticApm::getCurrentTransaction();

        // Set transaction name
        $transaction->setName(sprintf('%s %s', $request->method(), $this->getRouteUri($request)));

        // Add span wrapping the actual application code
        $response = ElasticApm::getCurrentExecutionSegment()->captureChildSpan('Application', 'application', function () use ($next, $request) {
            return $next($request);
        });

        // Set transaction result
        return tap($response, fn (Response $response) => $transaction->setResult($this->getHttpResult($response)));
    }

    /**
     * Returns the request route uri
     *
     * @param Request $request
     *
     * @return string
     */
    private function getRouteUri(Request $request): string
    {
        try {
            /** @var Router $router */
            $router = app()->make('router');

            return $router->getRoutes()->match($request)->uri();
        } catch (Throwable $throwable) {
            // If the route does not exist, then simply return the path
            return $request->path();
        }
    }

    /**
     * Returns the HTTP Transaction Result
     *
     * @param Response $response
     *
     * @return string
     */
    private function getHttpResult(Response $response): string
    {
        $code = (string) $response->getStatusCode();

        return 'HTTP ' . $code[0] . str_repeat('x', strlen($code) - 1);
    }
}

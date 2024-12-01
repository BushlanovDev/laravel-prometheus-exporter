<?php

declare(strict_types=1);

namespace BushlanovDev\LaravelPrometheusExporter\Middleware;

use Closure;
use BushlanovDev\LaravelPrometheusExporter\PrometheusExporter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Route;

abstract class PrometheusMiddlewareAbstract
{
    /**
     * @param Request $request
     *
     * @return Route
     */
    abstract public function getMatchedRoute(Request $request): Route;

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse) $next
     * @return Response|\Illuminate\Http\RedirectResponse
     * @throws \Prometheus\Exception\MetricsRegistrationException
     */
    public function handle(Request $request, Closure $next)
    {
        $matchedRoute = $this->getMatchedRoute($request);

        $start = microtime(true);
        /** @var Response $response */
        $response = $next($request);
        $duration = microtime(true) - $start;

        /** @var PrometheusExporter $exporter */
        $exporter = app('prometheus');
        $histogram = $exporter->getOrRegisterHistogram(
            'response_time_seconds',
            'It observes response time.',
            [
                'method',
                'route',
                'status_code',
            ],
            config('prometheus.routes_buckets') ?? null
        );

        $histogram->observe(
            $duration,
            [
                $request->method(),
                $matchedRoute->getName() ?? $matchedRoute->uri(),
                (string)$response->getStatusCode(),
            ]
        );

        $counter = $exporter->getOrRegisterCounter(
            'response_count',
            'It observes response count.',
            [
                'method',
                'route',
                'status_code',
            ],
        );

        $counter->inc(
            [
                $request->method(),
                $matchedRoute->getName() ?? $matchedRoute->uri(),
                (string)$response->getStatusCode(),
            ],
        );

        return $response;
    }
}

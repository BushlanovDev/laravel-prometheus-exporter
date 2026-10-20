<?php

declare(strict_types=1);

namespace BushlanovDev\LaravelPrometheusExporter\Middleware;

use Closure;
use BushlanovDev\LaravelPrometheusExporter\PrometheusExporter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

abstract class PrometheusMiddlewareAbstract
{
    /**
     * Return the label of the route matched for the request.
     *
     * Typically the route name, with a uri or request path fallback.
     *
     * @param Request $request
     *
     * @return string
     */
    abstract protected function getRouteLabel(Request $request): string;

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse) $next
     *
     * @return Response|\Illuminate\Http\RedirectResponse
     * @throws \Prometheus\Exception\MetricsRegistrationException
     */
    public function handle(Request $request, Closure $next)
    {
        $start = microtime(true);
        /** @var Response $response */
        $response = $next($request);
        $duration = microtime(true) - $start;

        $routeLabel = $this->getRouteLabel($request);

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
                $routeLabel,
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
                $routeLabel,
                (string)$response->getStatusCode(),
            ],
        );

        return $response;
    }
}

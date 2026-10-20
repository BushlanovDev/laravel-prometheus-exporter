<?php

declare(strict_types=1);

namespace BushlanovDev\LaravelPrometheusExporter\Middleware;

use Illuminate\Http\Request;

class PrometheusLumenMiddleware extends PrometheusMiddlewareAbstract
{
    /**
     * @param Request $request
     *
     * @return string
     */
    protected function getRouteLabel(Request $request): string
    {
        $routeInfo = $request->route();

        $action = is_array($routeInfo) && is_array($routeInfo[1] ?? null) ? $routeInfo[1] : [];
        $name = $action['as'] ?? null;

        return is_string($name) ? $name : $request->getPathInfo();
    }
}

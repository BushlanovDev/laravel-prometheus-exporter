<?php

declare(strict_types=1);

namespace BushlanovDev\LaravelPrometheusExporter\Middleware;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;

class PrometheusLaravelMiddleware extends PrometheusMiddlewareAbstract
{
    /**
     * @param Request $request
     *
     * @return string
     */
    protected function getRouteLabel(Request $request): string
    {
        $route = $request->route();

        if ($route instanceof Route) {
            return $route->getName() ?? $route->uri();
        }

        return $request->getPathInfo();
    }
}

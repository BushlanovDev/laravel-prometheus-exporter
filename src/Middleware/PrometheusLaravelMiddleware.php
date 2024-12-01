<?php

declare(strict_types=1);

namespace BushlanovDev\LaravelPrometheusExporter\Middleware;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;

class PrometheusLaravelMiddleware extends PrometheusMiddlewareAbstract
{
    /**
     * @param Request $request
     *
     * @return Route
     */
    public function getMatchedRoute(Request $request): Route
    {
        $routeCollection = RouteFacade::getRoutes();

        return $routeCollection->match($request);
    }
}

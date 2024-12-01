<?php

declare(strict_types=1);

namespace BushlanovDev\LaravelPrometheusExporter\Middleware;

use Illuminate\Routing\Route;
use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Facades\Route as RouteFacade;
use Illuminate\Http\Request;

class PrometheusLumenMiddleware extends PrometheusMiddlewareAbstract
{
    /**
     * @param Request $request
     * @return Route
     */
    public function getMatchedRoute(Request $request): Route
    {
        $routeCollection = new RouteCollection();
        $routes = RouteFacade::getRoutes();

        foreach ($routes as $route) {
            $routeCollection->add(
                new Route(
                    $route['method'],
                    str_replace(['[', '}]'], ['', '?}'], $route['uri']),
                    $route['action'],
                )
            );
        }

        return $routeCollection->match($request);
    }
}

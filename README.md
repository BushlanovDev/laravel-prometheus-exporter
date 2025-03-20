# Laravel and Lumen Prometheus Exporter

[![Packagist Version](https://img.shields.io/packagist/v/bushlanov-dev/laravel-prometheus-exporter.svg?style=flat-square)](https://packagist.org/packages/bushlanov-dev/laravel-prometheus-exporter)
[![PHP Version](https://img.shields.io/packagist/php-v/bushlanov-dev/laravel-prometheus-exporter.svg?style=flat-square)]()
[![Laravel Version](https://img.shields.io/badge/Laravel-9.x,%2010.x,%2011.x,%2012.x-brightgreen.svg?style=flat-square)]()
[![Lumen Version](https://img.shields.io/badge/Lumen-9.x,%2010.x,%2011.x-brightgreen.svg?style=flat-square)]()
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

A prometheus exporter for the Laravel and the Lumen.

This package is a wrapper bridging [promphp/prometheus_client_php](https://github.com/promphp/prometheus_client_php)

## Installation

```bash
composer require bushlanov-dev/laravel-prometheus-exporter
```

### Laravel

Laravel 11+ register the service provider in `bootstrap/providers.php`:
```php
return [
    // ...
    BushlanovDev\LaravelPrometheusExporter\Providers\PrometheusServiceProvider::class,
];
```

Old versions of laravel register the service provider in `config/app.php`:
```php
'providers' => [
    // ...
    BushlanovDev\LaravelPrometheusExporter\Providers\PrometheusServiceProvider::class,
];
```

Laravel 11+ register the middleware in `bootstrap/app.php`:
```php
// ...
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'prometheus' => BushlanovDev\LaravelPrometheusExporter\Middleware\PrometheusLaravelMiddleware::class,
    ]);
})
```

Old versions of laravel register the middleware in `app/Http/Kernel.php`:
```php
protected $routeMiddleware = [
    // ...
    'prometheus' => BushlanovDev\LaravelPrometheusExporter\Middleware\PrometheusLaravelMiddleware::class,
];
```

Register metrics route `routes/web.php`:
```php
Route::get('metrics', [BushlanovDev\LaravelPrometheusExporter\Controllers\LaravelMetricsController::class, 'metrics']);
```

Add middleware to routes. It is advisable to give names to all routes:
```php
Route::get('/', function () {
// ...
})->middleware('prometheus')->name('route_name');
```

### Lumen

Register the service provider and middleware in `bootstrap/app.php`:
```php
$app->register(BushlanovDev\LaravelPrometheusExporter\Providers\PrometheusServiceProvider::class);
```

```php
$app->routeMiddleware([
    'prometheus' => BushlanovDev\LaravelPrometheusExporter\Middleware\PrometheusLumenMiddleware::class,
]);
```

Register metrics route:
```php
$app->router->group(['namespace' => '\BushlanovDev\LaravelPrometheusExporter\Controllers'], function ($router) {
    $router->get('metrics', ['as' => 'metrics', 'uses'=> 'LumenMetricsController' . '@metrics']);
});
```

Add middleware to routes. It is advisable to give names to all routes:
```php
$router->get('/', ['middleware' => 'prometheus', 'as' => 'route_name', function () use ($router) {/*...*/}]);
```

## Guzzle metrics

To observe Guzzle metrics, you should register the following provider:
```php
BushlanovDev\LaravelPrometheusExporter\Providers\GuzzleServiceProvider::class
```

And register the middleware for Http facade or Guzzle client:
```php
Http::globalMiddleware(function ($handler) {
    return $this->app['prometheus.guzzle.middleware']($handler);
});

$this->app->alias('prometheus.guzzle.client', Client::class);
```

## Configuration

The package has a default configuration which uses the following environment variables.
```environment
PROMETHEUS_NAMESPACE=app
PROMETHEUS_STORAGE_ADAPTER=redis
PROMETHEUS_REDIS_HOST=localhost
PROMETHEUS_REDIS_PORT=6379
PROMETHEUS_REDIS_DATABASE=0
PROMETHEUS_REDIS_TIMEOUT=0.1
PROMETHEUS_REDIS_READ_TIMEOUT=10
PROMETHEUS_REDIS_PERSISTENT_CONNECTIONS=0
PROMETHEUS_REDIS_PREFIX=PROMETHEUS_
```

To customize the configuration file, publish the package configuration using Artisan:
```bash
php artisan vendor:publish --provider="BushlanovDev\LaravelPrometheusExporter\Providers\PrometheusServiceProvider"
```

### Protect metrics endpoint

If you need to prevent others from accessing your /metrics routes, you can enable the corresponding setting. 
Currently, only basic_auth is supported to secure your metrics endpoint.
```environment
PROMETHEUS_ROUTE_AUTH_ENABLED=true
PROMETHEUS_ROUTE_AUTH_USERNAME=username
PROMETHEUS_ROUTE_AUTH_PASSWORD=password
```

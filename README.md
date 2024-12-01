# Laravel and Lumen Prometheus Exporter

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Packagist Version](https://img.shields.io/packagist/v/bushlanov-dev/laravel-prometheus-exporter.svg?style=flat-square)](https://packagist.org/packages/bushlanov-dev/laravel-prometheus-exporter)

A prometheus exporter for the Laravel and the Lumen.

This package is a wrapper bridging [promphp/prometheus_client_php](https://github.com/promphp/prometheus_client_php)

## Installation

```bash
composer require digift-group/laravel-prometheus-exporter
```

### Laravel

Register the service provider in `config/app.php`

```php
'providers' => [
    // ...
    DigiftGroup\LaravelPrometheusExporter\PrometheusServiceProvider::class,
];
```

Register the middleware in `app/Http/Kernel.php`

```php
protected $routeMiddleware = [
    // ...
    'prometheus' => DigiftGroup\LaravelPrometheusExporter\Middleware\PrometheusLaravelMiddleware::class,
];
```

Register metrics route.

```php
Route::get('metrics', DigiftGroup\LaravelPrometheusExporter\Controllers\LaravelMetricsController::class . '@metrics');
```

Add middleware to routes. It is advisable to give names to all routes.

```php
Route::get('/', function () {
// ...
})->middleware('prometheus')->name('route_name');
```

### Lumen

Register the service provider and middleware in `bootstrap/app.php`

```php
$app->register(DigiftGroup\LaravelPrometheusExporter\PrometheusServiceProvider::class);
```

```php
$app->routeMiddleware([
    'prometheus' => DigiftGroup\LaravelPrometheusExporter\Middleware\PrometheusLumenMiddleware::class,
]);
```

Register metrics route

```php
$app->router->group(['namespace' => 'DigiftGroup\LaravelPrometheusExporter\Controllers'], function ($router) {
    $router->get('metrics', ['as' => 'metrics', 'uses'=> 'LumenMetricsController' . '@metrics']);
});
```

Add middleware to routes. It is advisable to give names to all routes

```php
$router->get('/', ['middleware' => 'prometheus', 'as' => 'route_name', function () use ($router) {/*...*/}]);
```

## Configuration

The package has a default configuration which uses the following environment variables

```
PROMETHEUS_NAMESPACE=app
PROMETHEUS_STORAGE_ADAPTER=redis
PROMETHEUS_REDIS_HOST=localhost
PROMETHEUS_REDIS_PORT=6379
PROMETHEUS_REDIS_TIMEOUT=0.1
PROMETHEUS_REDIS_READ_TIMEOUT=10
PROMETHEUS_REDIS_PERSISTENT_CONNECTIONS=0
PROMETHEUS_REDIS_PREFIX=PROMETHEUS_
```

To customize the configuration file, publish the package configuration using Artisan

```bash
php artisan vendor:publish --provider="DigiftGroup\LaravelPrometheusExporter\PrometheusServiceProvider"
```

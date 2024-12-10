<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Namespace
    |--------------------------------------------------------------------------
    |
    | The namespace to use as a prefix for all metrics.
    |
    | This will typically be the name of your project, eg: 'search'.
    |
    */

    'namespace' => env('PROMETHEUS_NAMESPACE', 'app'),

    /*
    |--------------------------------------------------------------------------
    | Storage Adapter
    |--------------------------------------------------------------------------
    |
    | The storage adapter to use.
    |
    | Supported: "memory", "redis", "apc", "apcng"
    |
    */

    'storage_adapter' => env('PROMETHEUS_STORAGE_ADAPTER', 'redis'),

    /*
    |--------------------------------------------------------------------------
    | Storage Adapters
    |--------------------------------------------------------------------------
    |
    | The storage adapter configs.
    |
    */

    'storage_adapters' => [

        'redis' => [
            'host' => env('PROMETHEUS_REDIS_HOST', 'localhost'),
            'port' => env('PROMETHEUS_REDIS_PORT', 6379),
            'database' => env('PROMETHEUS_REDIS_DATABASE', 0),
            'timeout' => env('PROMETHEUS_REDIS_TIMEOUT', 0.1),
            'read_timeout' => env('PROMETHEUS_REDIS_READ_TIMEOUT', 10),
            'persistent_connections' => env('PROMETHEUS_REDIS_PERSISTENT_CONNECTIONS', false),
            'prefix' => env('PROMETHEUS_REDIS_PREFIX', 'PROMETHEUS_'),
            'prefix_dynamic' => env('PROMETHEUS_REDIS_PREFIX_DYNAMIC', true),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Collectors
    |--------------------------------------------------------------------------
    |
    | The collectors specified here will be auto-registered in the exporter.
    |
    */

    'collectors' => [
        // \Your\ExporterClass::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Buckets config
    |--------------------------------------------------------------------------
    |
    | The buckets config specified here will be passed to the histogram generator
    | in the prometheus client. You can configure it as an array of time bounds.
    | Default value is null.
    |
    */

    'routes_buckets' => null,
    'guzzle_buckets' => null,

    /*
    |--------------------------------------------------------------------------
    | Protect metrics endpoint
    |--------------------------------------------------------------------------
    |
    | If you need to prevent others from accessing your /metrics routes,
    | you can enable the corresponding setting.
    | Currently, only basic_auth is supported to secure your metrics endpoint.
    |
    */

    'metrics_route_auth' => [
        'enabled' => env('PROMETHEUS_ROUTE_AUTH_ENABLED', false),
        'basic_auth' => [
            'username' => env('PROMETHEUS_ROUTE_AUTH_USERNAME'),
            'password' => env('PROMETHEUS_ROUTE_AUTH_PASSWORD'),
        ],
    ],

];

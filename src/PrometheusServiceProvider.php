<?php

declare(strict_types=1);

namespace BushlanovDev\LaravelPrometheusExporter;

use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use Prometheus\CollectorRegistry;
use Prometheus\Storage\Adapter;

class PrometheusServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/prometheus.php' => $this->app->configPath('prometheus.php'),
        ]);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/prometheus.php', 'prometheus');

        $this->app->singleton(PrometheusExporter::class, function ($app) {
            $adapter = $app['prometheus.storage_adapter'];
            $prometheus = new CollectorRegistry($adapter);

            return new PrometheusExporter(config('prometheus.namespace'), $prometheus);
        });
        $this->app->alias(PrometheusExporter::class, 'prometheus');

        $this->app->bind(StorageAdapterFactory::class, function () {
            return new StorageAdapterFactory();
        });
        $this->app->alias(StorageAdapterFactory::class, 'prometheus.storage_adapter_factory');

        $this->app->bind(Adapter::class, function ($app) {
            /** @var StorageAdapterFactory $factory */
            $factory = $app['prometheus.storage_adapter_factory'];
            $driver = $app['config']['prometheus']['storage_adapter'];
            $configs = $app['config']['prometheus']['storage_adapters'];
            $config = Arr::get($configs, $driver, []);

            return $factory->make($driver, $config);
        });
        $this->app->alias(Adapter::class, 'prometheus.storage_adapter');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<string>
     */
    public function provides(): array
    {
        return [
            'prometheus',
            'prometheus.storage_adapter_factory',
            'prometheus.storage_adapter',
        ];
    }
}

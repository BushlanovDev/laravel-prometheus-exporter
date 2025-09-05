<?php

declare(strict_types=1);

namespace Tests;

use BushlanovDev\LaravelPrometheusExporter\PrometheusExporter;
use BushlanovDev\LaravelPrometheusExporter\Providers\PrometheusServiceProvider;
use BushlanovDev\LaravelPrometheusExporter\StorageAdapterFactory;
use Orchestra\Testbench\TestCase;
use Prometheus\Storage\Adapter;
use Prometheus\Storage\InMemory;
use Prometheus\Storage\Redis;

class PrometheusServiceProviderTest extends TestCase
{
    /**
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array<class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            PrometheusServiceProvider::class,
        ];
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('prometheus.storage_adapter', 'memory');
    }

    public function testRegistersPrometheusExporterSingletonWithCorrectNamespace(): void
    {
        config()->set('prometheus.namespace', 'my_awesome_app');

        $exporter1 = $this->app->make(PrometheusExporter::class);
        $exporter2 = $this->app->make('prometheus');

        $this->assertInstanceOf(PrometheusExporter::class, $exporter1);
        $this->assertSame(
            $exporter1,
            $exporter2,
            'The "prometheus" alias should resolve to the same singleton instance.',
        );
        $this->assertEquals('my_awesome_app', $exporter1->getNamespace());
    }

    public function testRegistersStorageAdapterFactory(): void
    {
        $factory1 = $this->app->make(StorageAdapterFactory::class);
        $factory2 = $this->app->make('prometheus.storage_adapter_factory');

        $this->assertInstanceOf(StorageAdapterFactory::class, $factory1);
        $this->assertNotSame($factory1, $factory2, 'StorageAdapterFactory should be a binding, not a singleton.');
    }

    public function testRegistersRedisAdapterWhenConfigured(): void
    {
        if (!extension_loaded('redis')) {
            $this->markTestSkipped('The redis extension is not available.');
        }

        config()->set('prometheus.storage_adapter', 'redis');
        config()->set('prometheus.storage_adapters.redis', ['host' => '127.0.0.1']);

        $adapter = $this->app->make(Adapter::class);
        $aliasedAdapter = $this->app->make('prometheus.storage_adapter');

        $this->assertInstanceOf(Redis::class, $adapter);
        $this->assertNotSame($adapter, $aliasedAdapter, 'Adapter should be a binding, not a singleton.');
    }

    public function testRegistersInMemoryAdapterWhenConfigured(): void
    {
        config()->set('prometheus.storage_adapter', 'memory');

        $adapter = $this->app->make(Adapter::class);
        $aliasedAdapter = $this->app->make('prometheus.storage_adapter');

        $this->assertInstanceOf(InMemory::class, $adapter);
        $this->assertNotSame($adapter, $aliasedAdapter);
    }

    public function testProvidesMethodReturnsCorrectServices(): void
    {
        $provider = new PrometheusServiceProvider($this->app);
        $expected = [
            'prometheus',
            'prometheus.storage_adapter_factory',
            'prometheus.storage_adapter',
        ];

        $provides = $provider->provides();

        $this->assertEquals($expected, $provides);
    }
}

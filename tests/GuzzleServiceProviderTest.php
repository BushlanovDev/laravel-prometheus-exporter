<?php

declare(strict_types=1);

namespace Tests;

use BushlanovDev\LaravelPrometheusExporter\Middleware\GuzzleMiddleware;
use BushlanovDev\LaravelPrometheusExporter\PrometheusExporter;
use BushlanovDev\LaravelPrometheusExporter\Providers\GuzzleServiceProvider;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use Mockery;
use Orchestra\Testbench\TestCase;
use Prometheus\Histogram;
use ReflectionProperty;

class GuzzleServiceProviderTest extends TestCase
{
    /**
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array<class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            GuzzleServiceProvider::class,
        ];
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('prometheus.guzzle_buckets', [0.1, 0.5, 1.0]);

        $histogramMock = Mockery::mock(Histogram::class);
        $prometheusExporterMock = Mockery::mock(PrometheusExporter::class);

        $prometheusExporterMock->shouldReceive('getOrRegisterHistogram')
            ->once()
            ->with(
                'guzzle_response_duration',
                'Guzzle response duration histogram',
                ['method', 'external_endpoint', 'status_code'],
                [0.1, 0.5, 1.0]
            )
            ->andReturn($histogramMock);

        $app->instance('prometheus', $prometheusExporterMock);
    }

    public function testServiceProviderRegistersServicesCorrectly(): void
    {
        $histogram = $this->app->make('prometheus.guzzle.client.histogram');
        $this->assertInstanceOf(Histogram::class, $histogram);

        $handler = $this->app->make('prometheus.guzzle.handler');
        $this->assertInstanceOf(CurlHandler::class, $handler);

        $middleware = $this->app->make('prometheus.guzzle.middleware');
        $this->assertInstanceOf(GuzzleMiddleware::class, $middleware);

        $reflection = new ReflectionProperty(GuzzleMiddleware::class, 'histogram');
        $reflection->setAccessible(true);
        $this->assertSame($histogram, $reflection->getValue($middleware));

        $stack = $this->app->make('prometheus.guzzle.handler-stack');
        $this->assertInstanceOf(HandlerStack::class, $stack);

        $client = $this->app->make('prometheus.guzzle.client');
        $this->assertInstanceOf(Client::class, $client);
        $this->assertSame($stack, $client->getConfig('handler'));
    }
}

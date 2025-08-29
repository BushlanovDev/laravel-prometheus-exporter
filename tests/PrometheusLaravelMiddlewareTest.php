<?php

declare(strict_types=1);

namespace Tests;

use BushlanovDev\LaravelPrometheusExporter\Middleware\PrometheusLaravelMiddleware;
use BushlanovDev\LaravelPrometheusExporter\PrometheusExporter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route as RouteFacade;
use Mockery;
use Orchestra\Testbench\TestCase;
use Prometheus\Counter;
use Prometheus\Histogram;

class PrometheusLaravelMiddlewareTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('prometheus.routes_buckets', [0.1, 0.2, 0.3]);
    }

    public function testHandleIncrementsCounterAndObservesHistogramWithRouteName(): void
    {
        $histogram = Mockery::mock(Histogram::class);
        $histogram->shouldReceive('observe')
            ->once()
            ->with(
                Mockery::type('float'),
                ['GET', 'test.route', '200'],
            );

        $counter = Mockery::mock(Counter::class);
        $counter->shouldReceive('inc')
            ->once()
            ->with(['GET', 'test.route', '200']);

        $exporter = Mockery::mock(PrometheusExporter::class);
        $exporter->shouldReceive('getOrRegisterHistogram')
            ->once()
            ->with(
                'response_time_seconds',
                'It observes response time.',
                ['method', 'route', 'status_code'],
                [0.1, 0.2, 0.3],
            )
            ->andReturn($histogram);
        $exporter->shouldReceive('getOrRegisterCounter')
            ->once()
            ->with(
                'response_count',
                'It observes response count.',
                ['method', 'route', 'status_code']
            )
            ->andReturn($counter);

        $this->app->instance('prometheus', $exporter);

        RouteFacade::get('/test', fn() => new Response())->name('test.route');

        $middleware = new PrometheusLaravelMiddleware();
        $request = Request::create('/test', 'GET');
        $next = fn() => new Response('OK', 200);

        $response = $middleware->handle($request, $next);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testHandleUsesUriAsFallbackWhenRouteHasNoName(): void
    {
        // Arrange
        $histogram = Mockery::mock(Histogram::class);
        $histogram->shouldReceive('observe')
            ->once()
            ->with(
                Mockery::type('float'),
                ['POST', 'test-no-name', '201']
            );

        $counter = Mockery::mock(Counter::class);
        $counter->shouldReceive('inc')
            ->once()
            ->with(['POST', 'test-no-name', '201']);

        $exporter = Mockery::mock(PrometheusExporter::class);
        $exporter->shouldReceive('getOrRegisterHistogram')->andReturn($histogram);
        $exporter->shouldReceive('getOrRegisterCounter')->andReturn($counter);

        $this->app->instance('prometheus', $exporter);

        RouteFacade::post('/test-no-name', fn() => new Response('', 201));

        $middleware = new PrometheusLaravelMiddleware();
        $request = Request::create('/test-no-name', 'POST');
        $next = fn() => new Response('Created', 201);

        $response = $middleware->handle($request, $next);

        $this->assertEquals(201, $response->getStatusCode());
    }
}

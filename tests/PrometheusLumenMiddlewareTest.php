<?php

declare(strict_types=1);

namespace Tests;

use BushlanovDev\LaravelPrometheusExporter\Middleware\PrometheusLumenMiddleware;
use BushlanovDev\LaravelPrometheusExporter\PrometheusExporter;
use BushlanovDev\LaravelPrometheusExporter\Providers\PrometheusServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route as RouteFacade;
use Mockery;
use Orchestra\Testbench\TestCase;
use Prometheus\Counter;
use Prometheus\Histogram;

class PrometheusLumenMiddlewareTest extends TestCase
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
        $app->register(PrometheusServiceProvider::class);
        $app['config']->set('prometheus.routes_buckets', [0.1, 0.2, 0.3]);
    }

    public function testHandleIncrementsCounterAndObservesHistogramWithRouteName(): void
    {
        $histogram = Mockery::mock(Histogram::class);
        $histogram->shouldReceive('observe')->once()->with(Mockery::type('float'), ['GET', 'test.lumen.route', '200']);

        $counter = Mockery::mock(Counter::class);
        $counter->shouldReceive('inc')->once()->with(['GET', 'test.lumen.route', '200']);

        $exporter = Mockery::mock(PrometheusExporter::class);
        $exporter->shouldReceive('getOrRegisterHistogram')->andReturn($histogram);
        $exporter->shouldReceive('getOrRegisterCounter')->andReturn($counter);

        $this->app->instance('prometheus', $exporter);

        RouteFacade::shouldReceive('getRoutes')
            ->once()
            ->andReturn([
                [
                    'method' => 'GET',
                    'uri' => '/test',
                    'action' => ['as' => 'test.lumen.route', 'uses' => fn() => new Response('OK')],
                ],
            ]);

        $middleware = new PrometheusLumenMiddleware();
        $request = Request::create('/test', 'GET');
        $next = fn() => new Response('OK', 200);

        $response = $middleware->handle($request, $next);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testHandleUsesUriAsFallbackWhenRouteHasNoName(): void
    {
        $histogram = Mockery::mock(Histogram::class);
        $histogram->shouldReceive('observe')->once()->with(Mockery::type('float'), ['POST', 'test-no-name', '201']);

        $counter = Mockery::mock(Counter::class);
        $counter->shouldReceive('inc')->once()->with(['POST', 'test-no-name', '201']);

        $exporter = Mockery::mock(PrometheusExporter::class);
        $exporter->shouldReceive('getOrRegisterHistogram')->andReturn($histogram);
        $exporter->shouldReceive('getOrRegisterCounter')->andReturn($counter);

        $this->app->instance('prometheus', $exporter);

        RouteFacade::shouldReceive('getRoutes')
            ->once()
            ->andReturn([
                [
                    'method' => 'POST',
                    'uri' => '/test-no-name',
                    'action' => ['uses' => fn() => new Response('Created', 201)],
                ],
            ]);

        $middleware = new PrometheusLumenMiddleware();
        $request = Request::create('/test-no-name', 'POST');
        $next = fn() => new Response('Created', 201);

        $response = $middleware->handle($request, $next);

        $this->assertEquals(201, $response->getStatusCode());
    }
}

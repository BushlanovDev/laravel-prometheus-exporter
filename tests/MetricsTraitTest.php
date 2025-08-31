<?php

declare(strict_types=1);

namespace Tests;

use BushlanovDev\LaravelPrometheusExporter\Controllers\MetricsTrait;
use BushlanovDev\LaravelPrometheusExporter\PrometheusExporter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\ResponseFactory;
use Mockery;
use Mockery\MockInterface;
use Orchestra\Testbench\TestCase;
use Prometheus\RenderTextFormat;

class MetricsTraitTest extends TestCase
{
    private MockInterface|PrometheusExporter $exporter;
    private MockInterface|ResponseFactory $responseFactory;
    private ConcreteControllerWithMetricsTrait $controller;
    private Response $mockResponse;

    protected function setUp(): void
    {
        parent::setUp();
        $this->exporter = Mockery::mock(PrometheusExporter::class);
        $this->responseFactory = Mockery::mock(ResponseFactory::class);
        $this->mockResponse = new Response();
        $this->controller = new ConcreteControllerWithMetricsTrait($this->responseFactory, $this->exporter);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testMetricsReturnsSuccessWhenAuthIsDisabled(): void
    {
        config()->set('prometheus.metrics_route_auth.enabled', false);

        $this->exporter->shouldReceive('export')->once()->andReturn([]);
        $this->responseFactory
            ->shouldReceive('make')
            ->once()
            ->with("\n", Response::HTTP_OK, ['Content-Type' => RenderTextFormat::MIME_TYPE])
            ->andReturn($this->mockResponse);

        $response = $this->controller->metrics(new Request());

        $this->assertSame($this->mockResponse, $response);
    }

    public function testMetricsReturnsSuccessWhenInTestingEnvironmentEvenIfAuthIsEnabled(): void
    {
        config()->set('prometheus.metrics_route_auth.enabled', true);

        $this->exporter->shouldReceive('export')->once()->andReturn([]);
        $this->responseFactory
            ->shouldReceive('make')
            ->once()
            ->with("\n", Response::HTTP_OK, ['Content-Type' => RenderTextFormat::MIME_TYPE])
            ->andReturn($this->mockResponse);

        $response = $this->controller->metrics(new Request());

        $this->assertSame($this->mockResponse, $response);
    }

    public function testMetricsReturnsUnauthorizedWhenAuthIsEnabledAndFails(): void
    {
        $originalEnv = $_ENV['APP_ENV'] ?? 'testing';
        $_ENV['APP_ENV'] = 'production';

        try {
            config()->set('prometheus.metrics_route_auth.enabled', true);
            config()->set('prometheus.metrics_route_auth.basic_auth.username', 'user');
            config()->set('prometheus.metrics_route_auth.basic_auth.password', 'pass');

            $this->exporter->shouldReceive('export')->never();
            $this->responseFactory
                ->shouldReceive('make')
                ->once()
                ->with('Unauthorized.', Response::HTTP_UNAUTHORIZED, ['Content-Type' => RenderTextFormat::MIME_TYPE])
                ->andReturn($this->mockResponse);

            $response = $this->controller->metrics(new Request());

            $this->assertSame($this->mockResponse, $response);
        } finally {
            $_ENV['APP_ENV'] = $originalEnv;
        }
    }

    public function testMetricsReturnsSuccessWhenAuthIsEnabledAndSucceeds(): void
    {
        $originalEnv = $_ENV['APP_ENV'] ?? 'testing';
        $_ENV['APP_ENV'] = 'production';

        try {
            config()->set('prometheus.metrics_route_auth.enabled', true);
            config()->set('prometheus.metrics_route_auth.basic_auth.username', 'user');
            config()->set('prometheus.metrics_route_auth.basic_auth.password', 'pass');

            $this->exporter->shouldReceive('export')->once()->andReturn([]);
            $this->responseFactory
                ->shouldReceive('make')
                ->once()
                ->with("\n", Response::HTTP_OK, ['Content-Type' => RenderTextFormat::MIME_TYPE])
                ->andReturn($this->mockResponse);

            $request = new Request([], [], [], [], [], [
                'PHP_AUTH_USER' => 'user',
                'PHP_AUTH_PW' => 'pass',
            ]);

            $response = $this->controller->metrics($request);

            $this->assertSame($this->mockResponse, $response);
        } finally {
            $_ENV['APP_ENV'] = $originalEnv;
        }
    }
}

class ConcreteControllerWithMetricsTrait
{
    use MetricsTrait;

    public function __construct(
        protected ResponseFactory $responseFactory,
        protected PrometheusExporter $prometheusExporter,
    ) {
    }
}

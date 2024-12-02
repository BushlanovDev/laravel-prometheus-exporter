<?php

declare(strict_types=1);

namespace Tests;

use BushlanovDev\LaravelPrometheusExporter\Controllers\LaravelMetricsController;
use BushlanovDev\LaravelPrometheusExporter\Controllers\LumenMetricsController;
use BushlanovDev\LaravelPrometheusExporter\PrometheusExporter;
use Illuminate\Http\Response;
use Illuminate\Routing\ResponseFactory as ResponseFactoryLaravel;
use Laravel\Lumen\Http\ResponseFactory as ResponseFactoryLumen;
use Mockery;
use PHPUnit\Framework\TestCase;
use Prometheus\RenderTextFormat;

class MetricsControllerTest extends TestCase
{
    public function testLaravelMetrics(): void
    {
        $responseFactory = Mockery::mock(ResponseFactoryLaravel::class);
        $exporter = Mockery::mock(PrometheusExporter::class);
        $controller = new LaravelMetricsController($responseFactory, $exporter);

        $mockResponse = Mockery::mock(Response::class);
        $responseFactory->shouldReceive('make')
            ->once()
            ->withArgs(["\n", 200, ['Content-Type' => RenderTextFormat::MIME_TYPE]])
            ->andReturn($mockResponse);

        $exporter->shouldReceive('export')
            ->once()
            ->andReturn([]);

        $actualResponse = $controller->metrics();
        $this->assertSame($mockResponse, $actualResponse);
    }

    public function testLumenMetrics(): void
    {
        $responseFactory = Mockery::mock(ResponseFactoryLumen::class);
        $exporter = Mockery::mock(PrometheusExporter::class);
        $controller = new LumenMetricsController($responseFactory, $exporter);

        $mockResponse = Mockery::mock(Response::class);
        $responseFactory->shouldReceive('make')
            ->once()
            ->withArgs(["\n", 200, ['Content-Type' => RenderTextFormat::MIME_TYPE]])
            ->andReturn($mockResponse);

        $exporter->shouldReceive('export')
            ->once()
            ->andReturn([]);

        $actualResponse = $controller->metrics();
        $this->assertSame($mockResponse, $actualResponse);
    }
}

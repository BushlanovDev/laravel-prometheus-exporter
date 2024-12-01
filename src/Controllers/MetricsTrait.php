<?php

declare(strict_types=1);

namespace BushlanovDev\LaravelPrometheusExporter\Controllers;

use BushlanovDev\LaravelPrometheusExporter\PrometheusExporter;
use Illuminate\Http\Response;
use Prometheus\RenderTextFormat;

trait MetricsTrait
{
    /**
     * @return Response
     */
    public function metrics(): Response
    {
        /** @var PrometheusExporter $exporter */
        $exporter = app('prometheus');
        $metrics = $exporter->export();

        $renderer = new RenderTextFormat();
        $result = $renderer->render($metrics);

        return response($result, 200, ['Content-Type' => RenderTextFormat::MIME_TYPE]);
    }
}

<?php

declare(strict_types=1);

namespace BushlanovDev\LaravelPrometheusExporter\Controllers;

use Illuminate\Http\Response;
use Prometheus\RenderTextFormat;

trait MetricsTrait
{
    /**
     * @return Response
     * @throws \Throwable
     */
    public function metrics(): Response
    {
        return $this->responseFactory->make(
            (new RenderTextFormat())->render($this->prometheusExporter->export()),
            200,
            ['Content-Type' => RenderTextFormat::MIME_TYPE],
        );
    }
}

<?php

declare(strict_types=1);

namespace BushlanovDev\LaravelPrometheusExporter\Controllers;

use BushlanovDev\LaravelPrometheusExporter\PrometheusExporter;
use Illuminate\Routing\Controller;
use Illuminate\Routing\ResponseFactory;

class LaravelMetricsController extends Controller
{
    use MetricsTrait;

    /**
     * @var ResponseFactory
     */
    protected ResponseFactory $responseFactory;

    /**
     * @var PrometheusExporter
     */
    protected PrometheusExporter $prometheusExporter;

    /**
     * @param ResponseFactory $responseFactory
     * @param PrometheusExporter $prometheusExporter
     */
    public function __construct(ResponseFactory $responseFactory, PrometheusExporter $prometheusExporter)
    {
        $this->responseFactory = $responseFactory;
        $this->prometheusExporter = $prometheusExporter;
    }
}

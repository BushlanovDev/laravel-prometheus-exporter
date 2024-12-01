<?php

declare(strict_types=1);

namespace BushlanovDev\LaravelPrometheusExporter\Controllers;

use Laravel\Lumen\Routing\Controller;

class LumenMetricsController extends Controller
{
    use MetricsTrait;
}

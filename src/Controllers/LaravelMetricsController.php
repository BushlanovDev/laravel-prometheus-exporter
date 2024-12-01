<?php

declare(strict_types=1);

namespace BushlanovDev\LaravelPrometheusExporter\Controllers;

use Illuminate\Routing\Controller;

class LaravelMetricsController extends Controller
{
    use MetricsTrait;
}

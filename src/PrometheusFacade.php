<?php

declare(strict_types=1);

namespace BushlanovDev\LaravelPrometheusExporter;

use Illuminate\Support\Facades\Facade;
use Prometheus\CollectorRegistry;
use Prometheus\Counter;
use Prometheus\Gauge;
use Prometheus\Histogram;
use Prometheus\MetricFamilySamples;

/**
 * Laravel facade for PrometheusExporter.
 *
 * @method static string getNamespace()
 * @method static CollectorRegistry getPrometheus()
 * @method static void registerCollector(CollectorInterface $collector)
 * @method static array<string, CollectorInterface> getCollectors()
 * @method static CollectorInterface getCollector(string $name)
 * @method static Counter registerCounter(string $name, string $help, array<string> $labels = [])
 * @method static Counter getCounter(string $name)
 * @method static Counter getOrRegisterCounter(string $name, string $help, array<string> $labels = [])
 * @method static Gauge registerGauge(string $name, string $help, array<string> $labels = [])
 * @method static Gauge getGauge(string $name)
 * @method static Gauge getOrRegisterGauge(string $name, string $help, array<string> $labels = [])
 * @method static Histogram registerHistogram(string $name, string $help, array<string> $labels = [], ?array<mixed> $buckets = null)
 * @method static Histogram getHistogram(string $name)
 * @method static Histogram getOrRegisterHistogram(string $name, string $help, array<string> $labels = [], ?array<mixed> $buckets = null)
 * @method static MetricFamilySamples[] export()
 *
 * @see PrometheusExporter
 * @codeCoverageIgnore
 */
class PrometheusFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'prometheus';
    }
}

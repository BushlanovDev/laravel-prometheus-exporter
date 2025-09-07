<?php

declare(strict_types=1);

namespace BushlanovDev\LaravelPrometheusExporter;

use InvalidArgumentException;
use Prometheus\Storage\Adapter;
use Prometheus\Storage\APC;
use Prometheus\Storage\APCng;
use Prometheus\Storage\InMemory;
use Prometheus\Storage\Redis;

class StorageAdapterFactory
{
    private string $hostname;

    public function __construct()
    {
        $this->hostname = gethostname() !== false ? gethostname() : '';
    }

    /**
     * Factory a storage adapter.
     *
     * @param string $driver
     * @param array<string, mixed> $config
     *
     * @return Adapter
     */
    public function make(string $driver, array $config = []): Adapter
    {
        switch ($driver) {
            case 'memory':
                return new InMemory();
            case 'redis':
                return $this->makeRedisAdapter($config);
            case 'apc':
                return new APC();
            // @codeCoverageIgnoreStart
            case 'apcng':
                return new APCng();
            // @codeCoverageIgnoreEnd
        }

        throw new InvalidArgumentException("The driver $driver is not supported.");
    }

    /**
     * Factory a redis storage adapter.
     *
     * @param array<string, mixed> $config
     *
     * @return Redis
     */
    protected function makeRedisAdapter(array $config): Redis
    {
        if (isset($config['prefix'])) {
            $prefix = !empty($config['prefix_dynamic']) ? sprintf(
                '%s_%s_',
                $config['prefix'],
                $this->hostname
            ) : $config['prefix'];
            Redis::setPrefix($prefix);
        }

        return new Redis($config);
    }
}

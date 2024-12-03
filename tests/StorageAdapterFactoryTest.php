<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use Prometheus\Storage\APC;
use Prometheus\Storage\InMemory;
use Prometheus\Storage\Redis;
use BushlanovDev\LaravelPrometheusExporter\StorageAdapterFactory;

class StorageAdapterFactoryTest extends TestCase
{
    public function testMakeMemoryAdapter(): void
    {
        $factory = new StorageAdapterFactory();
        $adapter = $factory->make('memory');
        $this->assertInstanceOf(InMemory::class, $adapter);
    }

    public function testMakeApcAdapter(): void
    {
        $factory = new StorageAdapterFactory();
        $adapter = $factory->make('apc');
        $this->assertInstanceOf(APC::class, $adapter);
    }

    public function testMakeRedisAdapter(): void
    {
        $factory = new StorageAdapterFactory();
        $adapter = $factory->make('redis', ['connection' => 'special', 'prefix' => 'app_']);
        $this->assertInstanceOf(Redis::class, $adapter);
    }

    public function testMakeInvalidAdapter(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The driver moo is not supported.');

        $factory = new StorageAdapterFactory();
        $factory->make('moo');
    }
}

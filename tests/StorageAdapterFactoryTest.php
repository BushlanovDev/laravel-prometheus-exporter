<?php

declare(strict_types=1);

namespace Tests;

use ReflectionProperty;
use PHPUnit\Framework\TestCase;
use Prometheus\Storage\APC;
use Prometheus\Storage\InMemory;
use Prometheus\Storage\Redis;
use Prometheus\Storage\RedisClients\PHPRedis;
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
        if (!extension_loaded('apcu')) {
            $this->markTestSkipped('The apcu extension is not available.');
        }

        $factory = new StorageAdapterFactory();
        $adapter = $factory->make('apc');
        $this->assertInstanceOf(APC::class, $adapter);
    }

    public function testMakeRedisAdapter(): void
    {
        if (!extension_loaded('redis')) {
            $this->markTestSkipped('The redis extension is not available.');
        }

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

    public function testMakeRedisAdapterNormalizesPersistentConnections(): void
    {
        if (!extension_loaded('redis')) {
            $this->markTestSkipped('The redis extension is not available.');
        }

        $factory = new StorageAdapterFactory();

        $disabled = $factory->make('redis', ['persistent_connections' => '0', 'prefix' => 'app_']);
        $enabled = $factory->make('redis', ['persistent_connections' => '1']);

        $this->assertFalse($this->getPersistentConnectionsOption($disabled));
        $this->assertTrue($this->getPersistentConnectionsOption($enabled));
    }

    private function getPersistentConnectionsOption(Redis $adapter): bool
    {
        $redisProperty = new ReflectionProperty(Redis::class, 'redis');
        $phpRedis = $redisProperty->getValue($adapter);

        $optionsProperty = new ReflectionProperty(PHPRedis::class, 'options');
        $options = $optionsProperty->getValue($phpRedis);

        return $options['persistent_connections'];
    }
}

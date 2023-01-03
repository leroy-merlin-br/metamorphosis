<?php

namespace Tests\Unit;

use Metamorphosis\ConsumerConfigManager;
use Metamorphosis\Middlewares\JsonDecode;
use Metamorphosis\Middlewares\Log;
use Tests\LaravelTestCase;

class ManagerTest extends LaravelTestCase
{
    public function testManagerHandleConfigurations(): void
    {
        // Set
        $manager = new ConsumerConfigManager();
        $manager->set([
            'topic' => 'products',
            'middlewares' => [
                Log::class,
                app(JsonDecode::class),
            ],
            'other' => 'config',
        ]);

        // Actions
        $other = $manager->get('other');
        $middlewares = $manager->middlewares();

        // Assertions
        $this->assertSame('config', $other);
        $this->assertInstanceOf(Log::class, $middlewares[0]);
        $this->assertInstanceOf(JsonDecode::class, $middlewares[1]);
    }

    public function testShouldRemoveOldMiddlewareBeforeAddOthers(): void
    {
        // Set
        $manager = new ConsumerConfigManager();
        $firstConfig = [
            'topic' => 'products',
            'middlewares' => [
                Log::class,
                app(JsonDecode::class),
            ],
            'other' => 'config',
        ];
        $secondConfig = [
            'topic' => 'products',
            'middlewares' => [
                JsonDecode::class,
            ],
            'other' => 'config',
        ];

        // Actions
        $manager->set($firstConfig);
        $manager->set($secondConfig);
        $other = $manager->get('other');
        $middlewares = $manager->middlewares();

        // Assertions
        $this->assertSame('config', $other);
        $this->assertInstanceOf(JsonDecode::class, $middlewares[0]);
        $this->assertCount(1, $middlewares);
    }
}

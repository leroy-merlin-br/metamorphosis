<?php
namespace Tests\Unit;

use Metamorphosis\Manager;
use Metamorphosis\Middlewares\JsonDecode;
use Metamorphosis\Middlewares\Log;
use Tests\LaravelTestCase;

class ManagerTest extends LaravelTestCase
{
    public function testManagerHandleConfigurations(): void
    {
        // Set
        $manager = new Manager();
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
}

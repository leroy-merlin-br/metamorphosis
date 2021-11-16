<?php

namespace Tests\Integration;

use Metamorphosis\Consumer;
use Metamorphosis\TopicHandler\BaseConfigOptions;
use Tests\LaravelTestCase;
use Tests\Unit\Dummies\MiddlewareDummy;

class ConsumerTest extends LaravelTestCase
{
    public function testItShouldSetup(): void
    {
        // Set
        $configOptions = new BaseConfigOptions(
            'kafka-override',
            ['connections' => 'kafka:9092'],
            '',
            null,
            'default',
            [],
            [MiddlewareDummy::class],
            200,
            false,
            true,
            200,
            1,
            false,
            true
        );

        $consumer = $this->app->make(Consumer::class, ['configOptions' => $configOptions]);

        // Actions
        $result = $consumer->consume();
    }
}

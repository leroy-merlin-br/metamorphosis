<?php

namespace Tests\Integration;

use Metamorphosis\Consumer;
use Metamorphosis\TopicHandler\ConfigOptions\Auth\None;
use Metamorphosis\TopicHandler\ConfigOptions\Broker;
use Metamorphosis\TopicHandler\ConfigOptions\Consumer as ConsumerConfigOptions;
use Tests\LaravelTestCase;
use Tests\Unit\Dummies\MiddlewareDummy;

class ConsumerTest extends LaravelTestCase
{
    public function testItShouldSetup(): void
    {
        // Set
        $brokerOptions = new Broker('kafka:9092', new None());
        $configOptions = new ConsumerConfigOptions(
            'kafka-override',
            $brokerOptions,
            null,
            null,
            null,
            'default',
            null,
            [MiddlewareDummy::class],
            200,
            false,
            true,
            'smallest'
        );


        $consumer = $this->app->make(Consumer::class, ['configOptions' => $configOptions]);

        // Actions
        $result = $consumer->consume();
    }
}

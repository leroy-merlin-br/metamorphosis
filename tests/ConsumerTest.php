<?php
namespace Tests;

use Metamorphosis\Config;
use Metamorphosis\Consumer;
use Metamorphosis\Middlewares\AvroDecode;
use Metamorphosis\Middlewares\Log;
use Tests\Dummies\ConsumerHandlerDummy;

class ConsumerTest extends LaravelTestCase
{
    /** @test */
    public function it_should_run()
    {
        config([
            'kafka' => [
                'brokers' => [
                    'default' => [
                        'connection' => '',
                    ],
                ],
                'topics' => [
                    'topic-key' => [
                        'topic' => 'topic-name',
                        'broker' => 'default',
                        'consumer-groups' => [
                            'default' => [
                                'offset' => 'earliest',
                                'consumer' => ConsumerHandlerDummy::class,
                            ],
                            'consumer-id' => [
                                'offset' => 'earliest',
                                'consumer' => ConsumerHandlerDummy::class,
                                'middlewares' => [
                                ],
                            ],
                        ],
                        'middlewares' => [
                            AvroDecode::class,
                        ],
                    ],
                ],
                'middlewares' => [
                    'consumer' => [
                        Log::class,
                    ],
                ],
            ],
        ]);

        $topicKey = 'topic-key';
        $consumerGroup = 'consumer-id';
        $config = new Config($topicKey, $consumerGroup);

        $consumer = new Consumer($config);

        $consumer->run();
    }
}

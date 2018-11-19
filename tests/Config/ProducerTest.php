<?php
namespace Tests\Config;

use Metamorphosis\Config\Producer;
use Tests\LaravelTestCase;

class ProducerTest extends LaravelTestCase
{
    public function setUp()
    {
        parent::setUp();

        config([
            'kafka' => [
                'brokers' => [
                    'default' => [
                        'connections' => '',
                        'auth' => [
                            'protocol' => 'ssl',
                            'ca' => '/path/to/ca',
                            'certificate' => '/path/to/certificate',
                            'key' => '/path/to/key',
                        ],
                    ],
                ],
                'topics' => [
                    'topic-key-basic' => [
                        'topic' => 'topic-name',
                        'broker' => 'default',
                        'middlewares' => [
                            'first_global_middleware',
                            'first_topic_middleware',
                        ],
                    ],
                    'topic-key-partial' => [
                        'topic' => 'topic-name',
                        'broker' => 'default',
                        'producer' => [
                            'timeout-responses' => 80,
                        ],
                        'middlewares' => [
                            'first_global_middleware',
                            'first_topic_middleware',
                        ],
                    ],
                    'topic-key-full' => [
                        'topic' => 'topic-name',
                        'broker' => 'default',
                        'producer' => [
                            'middlewares' => [
                                'first_producer_middleware',
                            ],
                            'timeout-responses' => 75,
                        ],
                        'middlewares' => [
                            'first_global_middleware',
                            'first_topic_middleware',
                        ],
                    ],
                ],
                'middlewares' => [
                    'producer' => [
                        'first_global_middleware',
                        'second_global_middleware',
                    ],
                ],
            ],
        ]);
    }

    public function testItParsesBasicConfigurationFromFile()
    {
        $topicKey = 'topic-key-basic';
        $config = new Producer($topicKey);

        $this->assertSame([
            'first_global_middleware',
            'second_global_middleware',
            'first_topic_middleware',
        ], $config->getMiddlewares());

        $this->assertSame(1, $config->getTimeoutResponse());
    }

    public function testItParsesPartialConfigurationFromFile()
    {
        $topicKey = 'topic-key-partial';
        $config = new Producer($topicKey);

        $this->assertSame([
            'first_global_middleware',
            'second_global_middleware',
            'first_topic_middleware',
        ], $config->getMiddlewares());

        $this->assertSame(80, $config->getTimeoutResponse());
    }

    public function testItParsesFullConfigurationFromFile()
    {
        $topicKey = 'topic-key-full';
        $config = new Producer($topicKey);

        $this->assertSame([
            'first_global_middleware',
            'second_global_middleware',
            'first_topic_middleware',
            'first_producer_middleware',
        ], $config->getMiddlewares());

        $this->assertSame(75, $config->getTimeoutResponse());
    }
}

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
                    'topic-key' => [
                        'topic' => 'topic-name',
                        'broker' => 'default',
                        'producer' => [
                            'middlewares' => [
                                'first_producer_middleware',
                            ],
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

    /** @test */
    public function it_parses_configuration_from_file()
    {
        $topicKey = 'topic-key';
        $config = new Producer($topicKey);

        $this->assertSame([
            'first_global_middleware',
            'second_global_middleware',
            'first_topic_middleware',
            'first_producer_middleware',
        ], $config->getMiddlewares());
    }
}

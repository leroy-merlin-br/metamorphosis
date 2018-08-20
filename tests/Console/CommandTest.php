<?php
namespace Tests\Console;

use Metamorphosis\Exceptions\ConfigurationException;
use Tests\Dummies\ConsumerHandlerDummy;
use Tests\LaravelTestCase;

class CommandTest extends LaravelTestCase
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
                        'consumer-groups' => [
                            'default' => [
                                'offset' => 'initial',
                                'consumer' => ConsumerHandlerDummy::class,
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    /** @test */
    public function it_calls_command_with_invalid_topic()
    {
        $command = 'kafka:consume';
        $parameters = [
            'topic' => 'some-topic',
        ];

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('Topic \'some-topic\' not found');

        $this->artisan($command, $parameters);
    }
}

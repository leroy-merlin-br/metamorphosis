<?php
namespace Tests\Console;

use Tests\LaravelTestCase;

class ConsumerMakeCommandTest extends LaravelTestCase
{
    /** @test */
    public function it_should_generate_consumer_handler()
    {
        $command = 'make:kafka-consumer';
        $parameters = [
            'name' => 'SampleConsumer',
        ];

        $statusCode = $this->artisan($command, $parameters);

        $this->assertSame(0, $statusCode);
    }
}

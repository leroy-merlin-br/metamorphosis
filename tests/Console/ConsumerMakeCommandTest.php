<?php
namespace Tests\Console;

use Tests\LaravelTestCase;

class ConsumerMakeCommandTest extends LaravelTestCase
{
    public function testItShouldGenerateConsumerHandler()
    {
        $this->markTestSkipped();
        $command = 'make:kafka-consumer';
        $parameters = [
            'name' => str_random(8),
        ];

        $statusCode = $this->artisan($command, $parameters);

        $this->assertSame(0, $statusCode);
    }
}

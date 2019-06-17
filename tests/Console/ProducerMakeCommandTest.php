<?php
namespace Tests\Console;

use Tests\LaravelTestCase;

class ProducerMakeCommandTest extends LaravelTestCase
{
    public function testItShouldGenerateProducerHandler()
    {
        $this->markTestSkipped();
        $command = 'make:kafka-producer';
        $parameters = [
            'name' => str_random(8),
        ];

        $statusCode = $this->artisan($command, $parameters);

        $this->assertSame(0, $statusCode);
    }
}

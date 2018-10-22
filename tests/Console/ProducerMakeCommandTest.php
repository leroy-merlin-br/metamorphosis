<?php
namespace Tests\Console;

use Tests\LaravelTestCase;

class ProducerMakeCommandTest extends LaravelTestCase
{
    /** @test */
    public function it_should_generate_producer_handler()
    {
        $command = 'make:kafka-producer';
        $parameters = [
            'name' => str_random(8),
        ];

        $statusCode = $this->artisan($command, $parameters);

        $this->assertSame(0, $statusCode);
    }
}

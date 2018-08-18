<?php
namespace Tests\Console;

use Tests\LaravelTestCase;

class MiddlewareMakeCommandTest extends LaravelTestCase
{
    /** @test */
    public function it_should_generate_middleware()
    {
        $command = 'make:kafka-middleware';
        $parameters = [
            'name' => 'LogMiddleware',
        ];

        $statusCode = $this->artisan($command, $parameters);

        $this->assertSame(0, $statusCode);
    }
}

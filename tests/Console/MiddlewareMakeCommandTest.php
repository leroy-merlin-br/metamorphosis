<?php
namespace Tests\Console;

use Tests\LaravelTestCase;

class MiddlewareMakeCommandTest extends LaravelTestCase
{
    public function testItShouldGenerateMiddleware()
    {
        $this->markTestSkipped();
        $command = 'make:kafka-middleware';
        $parameters = [
            'name' => str_random(8),
        ];

        $statusCode = $this->artisan($command, $parameters);

        $this->assertSame(0, $statusCode);
    }
}

<?php
namespace Tests\Console;

use Illuminate\Support\Facades\Artisan;
use Tests\LaravelTestCase;

class MiddlewareMakeCommandTest extends LaravelTestCase
{
    public function testItShouldGenerateMiddleware(): void
    {
        // Set
        $command = 'make:kafka-middleware';
        $parameters = [
            'name' => str_random(8),
        ];

        // Actions
        $statusCode = Artisan::call($command, $parameters);

        // Assertions
        $this->assertSame(0, $statusCode);
    }
}

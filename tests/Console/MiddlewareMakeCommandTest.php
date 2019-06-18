<?php
namespace Tests\Console;

use Illuminate\Support\Facades\Artisan;
use Tests\LaravelTestCase;

class MiddlewareMakeCommandTest extends LaravelTestCase
{
    public function testItShouldGenerateMiddleware()
    {
        $command = 'make:kafka-middleware';
        $parameters = [
            'name' => str_random(8),
        ];

        $statusCode = Artisan::call($command, $parameters);

        $this->assertSame(0, $statusCode);
    }
}

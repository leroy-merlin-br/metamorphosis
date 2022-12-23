<?php

namespace Tests\Unit\Console;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Tests\LaravelTestCase;

class MiddlewareMakeCommandTest extends LaravelTestCase
{
    public function testItShouldGenerateMiddleware(): void
    {
        // Set
        $command = 'make:kafka-middleware';
        $parameters = [
            'name' => Str::random(8),
        ];

        // Actions
        $statusCode = Artisan::call($command, $parameters);

        // Assertions
        $this->assertSame(0, $statusCode);
    }
}

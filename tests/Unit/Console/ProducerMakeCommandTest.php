<?php

namespace Tests\Unit\Console;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Tests\LaravelTestCase;

class ProducerMakeCommandTest extends LaravelTestCase
{
    public function testItShouldGenerateProducerHandler(): void
    {
        // Set
        $command = 'make:kafka-producer';
        $parameters = [
            'name' => Str::random(8),
        ];

        // Actions
        $statusCode = Artisan::call($command, $parameters);

        // Assertions
        $this->assertSame(0, $statusCode);
    }
}

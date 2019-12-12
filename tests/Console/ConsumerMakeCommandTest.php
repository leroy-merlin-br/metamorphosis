<?php
namespace Tests\Console;

use Illuminate\Support\Facades\Artisan;
use Tests\LaravelTestCase;

class ConsumerMakeCommandTest extends LaravelTestCase
{
    public function testItShouldGenerateConsumerHandler(): void
    {
        // Set
        $command = 'make:kafka-consumer';
        $parameters = [
            'name' => str_random(8),
        ];

        // Actions
        $statusCode = Artisan::call($command, $parameters);

        // Assertions
        $this->assertSame(0, $statusCode);
    }
}

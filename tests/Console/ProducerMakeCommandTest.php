<?php
namespace Tests\Console;

use Illuminate\Support\Facades\Artisan;
use Tests\LaravelTestCase;

class ProducerMakeCommandTest extends LaravelTestCase
{
    public function testItShouldGenerateProducerHandler()
    {
        $command = 'make:kafka-producer';
        $parameters = [
            'name' => str_random(8),
        ];

        $statusCode = Artisan::call($command, $parameters);

        $this->assertSame(0, $statusCode);
    }
}

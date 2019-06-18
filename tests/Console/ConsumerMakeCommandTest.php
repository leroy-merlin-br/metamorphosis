<?php
namespace Tests\Console;

use Illuminate\Support\Facades\Artisan;
use Tests\LaravelTestCase;

class ConsumerMakeCommandTest extends LaravelTestCase
{
    public function testItShouldGenerateConsumerHandler()
    {
        $command = 'make:kafka-consumer';
        $parameters = [
            'name' => str_random(8),
        ];

        $statusCode = Artisan::call($command, $parameters);

        $this->assertSame(0, $statusCode);
    }
}

<?php
namespace Tests\Unit;

use Exception;
use Metamorphosis\Connectors\Consumer\Manager;
use Metamorphosis\Consumers\Runner;
use Mockery as m;
use Tests\LaravelTestCase;

class RunnerTest extends LaravelTestCase
{
    public function testItShouldRun(): void
    {
        // Set
        $manager = m::mock(Manager::class);
        $runner = new Runner($manager);
        $count = 0;

        // Expectations
        $manager->shouldReceive('handleMessage')
            ->times(4)
            ->andReturnUsing(function () use (&$count) {
                if (3 === $count) {
                    $exception = new Exception('Error when consuming.');
                    throw $exception;
                }
                $count++;
                return;
            });

        // Ensure that one message went through the middleware stack
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Error when consuming.');

        // Actions
        $runner->run();
    }

    public function testItShouldRunADeterminedNumberOfTimes(): void
    {
        // Set
        $manager = m::mock(Manager::class);
        $runner = new Runner($manager);

        // Expectations
        $manager->shouldReceive('handleMessage')
            ->times(3)
            ->andReturnUsing(function () use (&$count) {
                return;
            });

        // Actions
        $runner->run(3);
    }

    public function testItShouldRunADeterminedNumberOfTimesButStopsOnException(): void
    {
        // Set
        $manager = m::mock(Manager::class);
        $runner = new Runner($manager);

        // Expectations
        $manager->shouldReceive('handleMessage')
            ->times(1)
            ->andReturnUsing(function () use (&$count) {
                throw new Exception('Error when consuming.');
            });

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Error when consuming.');

        // Actions
        $runner->run(3);
    }
}

<?php
namespace Tests\Connectors\Producer;

use Metamorphosis\Connectors\Producer\Queue;
use Mockery as m;
use RdKafka\Producer;
use Tests\LaravelTestCase;

class QueueTest extends LaravelTestCase
{
    public function testItShouldPoll(): void
    {
        // Set
        $producer = m::mock(Producer::class);
        $queue = new Queue($producer);

        // Expectations
        $producer->expects()
            ->getOutQLen()
            ->andReturn(1);

        $producer->expects()
            ->getOutQLen()
            ->andReturn(0);

        $producer->expects()
            ->poll(50);

        // Actions
        $result = $queue->poll(50);

        // Assertions
        $this->assertNull($result);
    }
}

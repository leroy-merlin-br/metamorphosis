<?php
namespace Tests\Connectors\Producer;

use Metamorphosis\Connectors\Producer\Queue;
use RdKafka\Producer;
use Tests\LaravelTestCase;

class QueueTest extends LaravelTestCase
{
    public function testItShouldPoll()
    {
        $producer = $this->createMock(Producer::class);
        $queue = new Queue($producer);

        $producer->expects($this->exactly(2))
            ->method('getOutQLen')
            ->willReturnOnConsecutiveCalls([1, 0]);

        $producer->expects($this->once())
            ->method('poll')
            ->with(50);

        $voidReturn = $queue->poll(50);

        $this->assertNull($voidReturn);
    }
}

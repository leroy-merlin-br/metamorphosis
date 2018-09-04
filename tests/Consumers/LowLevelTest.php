<?php
namespace Tests\Consumers;

use Metamorphosis\Config;
use Metamorphosis\Consumers\LowLevel;
use RdKafka\ConsumerTopic;
use RdKafka\Message;
use Tests\LaravelTestCase;

class LowLevelTest extends LaravelTestCase
{
    /** @test */
    public function it_should_consume()
    {
        $config = $this->createMock(Config::class);
        $consumerTopic = $this->createMock(ConsumerTopic::class);
        $message = new Message();

        $config->expects($this->exactly(1))
            ->method('getConsumerPartition')
            ->willReturn(1);

        $consumerTopic->expects($this->exactly(1))
            ->method('consume')
            ->with($this->equalTo(1))
            ->willReturn($message);

        $lowLevelConsumer = new LowLevel($config, $consumerTopic);

        $message = $lowLevelConsumer->consume(1);

        $this->assertInstanceOf(Message::class, $message);
    }
}

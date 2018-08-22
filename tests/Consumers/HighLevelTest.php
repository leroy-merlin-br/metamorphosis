<?php
namespace Tests\Consumers;

use Metamorphosis\Consumers\HighLevel;
use RdKafka\KafkaConsumer;
use RdKafka\Message;
use Tests\LaravelTestCase;

class HighLevelTest extends LaravelTestCase
{
    /** @test */
    public function it_should_consume()
    {
        $kafkaConsumer = $this->createMock(KafkaConsumer::class);
        $message = new Message();

        $kafkaConsumer->expects($this->exactly(1))
            ->method('consume')
            ->with($this->equalTo(1))
            ->will($this->returnValue($message));

        $highLevelConsumer = new HighLevel($kafkaConsumer);

        $message = $highLevelConsumer->consume(1);

        $this->assertInstanceOf(Message::class, $message);
    }
}

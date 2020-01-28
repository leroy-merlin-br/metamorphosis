<?php
namespace Tests\Unit\Consumers;

use Metamorphosis\Consumers\HighLevel;
use Metamorphosis\Facades\ConfigManager;
use Mockery as m;
use RdKafka\KafkaConsumer;
use RdKafka\Message;
use Tests\LaravelTestCase;

class HighLevelTest extends LaravelTestCase
{
    public function testItShouldConsume(): void
    {
        // Set
        ConfigManager::set(['timeout' => 1]);
        $kafkaConsumer = m::mock(KafkaConsumer::class);
        $message = new Message();
        $highLevelConsumer = new HighLevel($kafkaConsumer);

        // Expectations
        $kafkaConsumer->expects()
            ->consume(1)
            ->andReturn($message);

        // Actions
        $message = $highLevelConsumer->consume();

        // Assertions
        $this->assertInstanceOf(Message::class, $message);
    }
}

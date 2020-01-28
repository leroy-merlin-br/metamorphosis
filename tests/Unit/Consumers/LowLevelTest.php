<?php
namespace Tests\Unit\Consumers;

use Metamorphosis\Consumers\LowLevel;
use Metamorphosis\Facades\ConfigManager;
use Mockery as m;
use RdKafka\ConsumerTopic;
use RdKafka\Message;
use Tests\LaravelTestCase;

class LowLevelTest extends LaravelTestCase
{
    public function testItShouldConsume(): void
    {
        // Set
        $timeout = 2;
        $partition = 3;
        ConfigManager::set(compact('timeout', 'partition'));

        $consumerTopic = m::mock(ConsumerTopic::class);
        $message = new Message();

        $lowLevelConsumer = new LowLevel($consumerTopic);

        // Expectations
        $consumerTopic->expects()
            ->consume($partition, $timeout)
            ->andReturn($message);

        // Actions
        $message = $lowLevelConsumer->consume();

        // Assertions
        $this->assertInstanceOf(Message::class, $message);
    }
}

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

    public function testItShouldCommit(): void
    {
        // Set
        ConfigManager::set(['timeout' => 1]);
        $kafkaConsumer = m::mock(KafkaConsumer::class);
        $highLevelConsumer = new HighLevel($kafkaConsumer);

        // Expectations
        $kafkaConsumer->expects()
            ->commit()
            ->andReturn();

        // Actions
        $highLevelConsumer->commit();
    }

    public function testItShouldCommitAsynchronously(): void
    {
        // Set
        ConfigManager::set(['timeout' => 1]);
        $kafkaConsumer = m::mock(KafkaConsumer::class);
        $highLevelConsumer = new HighLevel($kafkaConsumer);

        // Expectations
        $kafkaConsumer->expects()
            ->commitAsync()
            ->andReturn();

        // Actions
        $highLevelConsumer->commitAsync();
    }
}

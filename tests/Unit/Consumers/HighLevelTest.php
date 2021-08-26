<?php
namespace Tests\Unit\Consumers;

use Metamorphosis\Consumers\HighLevel;
use Mockery as m;
use RdKafka\KafkaConsumer;
use RdKafka\Message;
use Tests\LaravelTestCase;

class HighLevelTest extends LaravelTestCase
{
    public function testItShouldConsume(): void
    {
        // Set
        $kafkaConsumer = m::mock(KafkaConsumer::class);
        $message = new Message();
        $highLevelConsumer = new HighLevel($kafkaConsumer, 1000);

        // Expectations
        $kafkaConsumer->expects()
            ->consume(1000)
            ->andReturn($message);

        // Actions
        $message = $highLevelConsumer->consume();

        // Assertions
        $this->assertInstanceOf(Message::class, $message);
    }

    public function testItShouldCommit(): void
    {
        // Set
        $kafkaConsumer = m::mock(KafkaConsumer::class);
        $highLevelConsumer = new HighLevel($kafkaConsumer, 1000);

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
        $kafkaConsumer = m::mock(KafkaConsumer::class);
        $highLevelConsumer = new HighLevel($kafkaConsumer, 1000);

        // Expectations
        $kafkaConsumer->expects()
            ->commitAsync()
            ->andReturn();

        // Actions
        $highLevelConsumer->commitAsync();
    }
}

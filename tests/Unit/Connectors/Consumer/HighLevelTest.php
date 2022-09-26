<?php
namespace Tests\Unit\Connectors\Consumer;

use Metamorphosis\Connectors\Consumer\HighLevel;
use Metamorphosis\ConsumerConfigManager;
use Metamorphosis\Consumers\HighLevel as HighLevelConsumer;
use Tests\LaravelTestCase;

class HighLevelTest extends LaravelTestCase
{
    public function testItShouldMakeConnectorSetup(): void
    {
        // Set
        $configManager = new ConsumerConfigManager();
        $configManager->set([
            'connections' => 'kafka:123',
            'consumer_group' => 'some-group',
            'topic_id' => 'some_topic',
            'offset_reset' => 'earliest',
            'timeout' => 1000,
        ]);
        $connector = new HighLevel();

        // Actions
        $result = $connector->getConsumer(false, $configManager);

        // Assertions
        $this->assertInstanceOf(HighLevelConsumer::class, $result);
    }
}

<?php
namespace Tests\Unit\Connectors\Consumer;

use Metamorphosis\Connectors\Consumer\LowLevel;
use Metamorphosis\ConsumerConfigManager;
use Metamorphosis\Consumers\LowLevel as LowLevelConsumer;
use Tests\LaravelTestCase;

class LowLevelTest extends LaravelTestCase
{
    public function testItShouldMakeConnectorSetup(): void
    {
        // Set
        $configManager = new ConsumerConfigManager();
        $configManager->set([
            'connections' => 'kafka:9092',
            'consumer_group' => 'some-group',
            'topic' => 'some_topic',
            'offset_reset' => 'earliest',
            'offset' => 0,
            'partition' => 1,
        ]);
        $connector = new LowLevel();

        // Actions
        $result = $connector->getConsumer(true, $configManager);

        // Assertions
        $this->assertInstanceOf(LowLevelConsumer::class, $result);
    }
}

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
        $connections = env('KAFKA_BROKER_CONNECTIONS', 'kafka:9092');
        $configManager = new ConsumerConfigManager();
        $configManager->set([
            'connections' => $connections,
            'consumer_group' => 'some-group',
            'topic' => 'some_topic',
            'offset_reset' => 'earliest',
            'max_poll_interval_ms' => 900000,
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

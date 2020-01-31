<?php
namespace Test;

use Metamorphosis\Connectors\Consumer\HighLevel;
use Metamorphosis\Consumers\HighLevel as HighLevelConsumer;
use Metamorphosis\Facades\ConfigManager;
use Tests\LaravelTestCase;

class HighLevelTest extends LaravelTestCase
{
    public function testItShouldMakeConnectorSetup(): void
    {
        // Set
        ConfigManager::set([
            'connections' => 'kafka:123',
            'consumer_group' => 'some-group',
            'topic_id' => 'some_topic',
            'offset_reset' => 'earliest',
        ]);
        $connector = new HighLevel();

        // Actions
        $result = $connector->getConsumer(false);

        // Assertions
        $this->assertInstanceOf(HighLevelConsumer::class, $result);
    }
}

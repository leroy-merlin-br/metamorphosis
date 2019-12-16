<?php
namespace Test;

use Metamorphosis\Connectors\Consumer\LowLevel;
use Metamorphosis\Consumers\LowLevel as LowLevelConsumer;
use Metamorphosis\Facades\Manager;
use Tests\LaravelTestCase;

class LowLevelTest extends LaravelTestCase
{
    public function testItShouldMakeConnectorSetup(): void
    {
        // Set
        Manager::set([
            'connections' => 'kafka:123',
            'consumer_group' => 'some-group',
            'topic' => 'some_topic',
            'offset_reset' => 'earliest',
            'offset' => 0,
            'partition' => 1,
        ]);
        $connector = new LowLevel();

        // Actions
        $result = $connector->getConsumer();

        // Assertions
        $this->assertInstanceOf(LowLevelConsumer::class, $result);
    }
}

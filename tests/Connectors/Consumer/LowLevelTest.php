<?php
namespace Test;

use Metamorphosis\Connectors\Consumer\LowLevel;
use Metamorphosis\Consumers\LowLevel as LowLevelConsumer;
use Tests\LaravelTestCase;

class LowLevelTest extends LaravelTestCase
{
    public function testItShouldMakeConnectorSetup(): void
    {
        // Set
        config([
            'kafka.runtime' => [
                'connections' => 'kafka:123',
                'consumer-group' => 'some-group',
                'topic' => 'some-topic',
                'offset_reset' => 'earliest',
                'offset' => 0,
                'partition' => 1,
            ],
        ]);
        $connector = new LowLevel();

        // Actions
        $result = $connector->getConsumer();

        // Assertions
        $this->assertInstanceOf(LowLevelConsumer::class, $result);
    }
}

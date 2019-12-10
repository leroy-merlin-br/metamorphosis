<?php
namespace Test;

use Metamorphosis\Connectors\Consumer\HighLevel;
use Metamorphosis\Consumers\HighLevel as HighLevelConsumer;
use Tests\LaravelTestCase;

class HighLevelTest extends LaravelTestCase
{
    public function testItShouldMakeConnectorSetup(): void
    {
        // Set
        config([
            'kafka.runtime' => [
                'connections' => 'kafka:123',
                'consumer-group' => 'some-group',
                'topic' => 'some-topic',
                'offset-reset' => 'earliest',
            ],
        ]);
        $connector = new HighLevel();

        // Actions
        $result = $connector->getConsumer();

        // Assertions
        $this->assertInstanceOf(HighLevelConsumer::class, $result);
    }
}

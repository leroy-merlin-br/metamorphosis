<?php

namespace Tests\Unit\Connectors\Consumer;

use Metamorphosis\Connectors\Consumer\HighLevel;
use Metamorphosis\Consumers\HighLevel as HighLevelConsumer;
use Metamorphosis\TopicHandler\ConfigOptions\Auth\None;
use Metamorphosis\TopicHandler\ConfigOptions\AvroSchema as AvroSchemaConfigOptions;
use Metamorphosis\TopicHandler\ConfigOptions\Broker;
use Metamorphosis\TopicHandler\ConfigOptions\Consumer as ConsumerConfigOptions;
use Tests\LaravelTestCase;

class HighLevelTest extends LaravelTestCase
{
    public function testItShouldMakeConnectorSetup(): void
    {
        // Set
        $connector = new HighLevel();
        $connections = env('KAFKA_BROKER_CONNECTIONS', 'kafka:9092');
        $brokerOptions = new Broker($connections, new None());
        $consumerConfigOptions = new ConsumerConfigOptions(
            'kafka-test',
            $brokerOptions,
            null,
            1,
            0,
            'some-group',
            new AvroSchemaConfigOptions('http://url.teste')
        );

        // Actions
        $result = $connector->getConsumer(false, $consumerConfigOptions);

        // Assertions
        $this->assertInstanceOf(HighLevelConsumer::class, $result);
    }
}

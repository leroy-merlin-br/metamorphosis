<?php
namespace Tests\Unit\Connectors\Consumer;

use Metamorphosis\Connectors\Consumer\LowLevel;
use Metamorphosis\Consumers\LowLevel as LowLevelConsumer;
use Metamorphosis\TopicHandler\ConfigOptions\Auth\None;
use Metamorphosis\TopicHandler\ConfigOptions\AvroSchema as AvroSchemaConfigOptions;
use Metamorphosis\TopicHandler\ConfigOptions\Broker;
use Metamorphosis\TopicHandler\ConfigOptions\Consumer as ConsumerConfigOptions;
use Tests\LaravelTestCase;

class LowLevelTest extends LaravelTestCase
{
    public function testItShouldMakeConnectorSetup(): void
    {
        // Set
        $connector = new LowLevel();
        $brokerOptions = new Broker('kafka:9092', new None());
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
        $result = $connector->getConsumer(true, $consumerConfigOptions);

        // Assertions
        $this->assertInstanceOf(LowLevelConsumer::class, $result);
    }
}

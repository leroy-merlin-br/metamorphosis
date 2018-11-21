<?php
namespace Test;

use Metamorphosis\Broker;
use Metamorphosis\Config\Consumer;
use Metamorphosis\Connectors\Consumer\LowLevel;
use Metamorphosis\Consumers\LowLevel as LowLevelConsumer;
use RdKafka\ConsumerTopic;
use Tests\LaravelTestCase;

class LowLevelTest extends LaravelTestCase
{
    public function testItShouldMakeConnectorSetup()
    {
        $config = $this->createMock(Consumer::class);
        $this->app->instance(ConsumerTopic::class, $this->createMock(ConsumerTopic::class));

        $connector = new LowLevel($config);

        $config->expects($this->exactly(2))
            ->method('getBrokerConfig')
            ->will($this->returnValue($this->createMock(Broker::class)));

        $config->expects($this->once())
            ->method('isHighPerformanceEnabled')
            ->will($this->returnValue(true));

        $config->expects($this->once())
            ->method('getConsumerGroupId')
            ->will($this->returnValue('group.id'));

        $config->expects($this->once())
            ->method('getConsumerOffsetReset')
            ->will($this->returnValue('smallest'));

        $config->expects($this->once())
            ->method('getTopic')
            ->will($this->returnValue('some.topic'));

        $result = $connector->getConsumer();

        $this->assertInstanceOf(LowLevelConsumer::class, $result);
    }
}

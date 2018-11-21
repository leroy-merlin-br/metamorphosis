<?php
namespace Test;

use Metamorphosis\Broker;
use Metamorphosis\Config\Consumer;
use Metamorphosis\Connectors\Consumer\HighLevel;
use Metamorphosis\Consumers\HighLevel as HighLevelConsumer;
use RdKafka\ConsumerTopic;
use Tests\LaravelTestCase;

class HighLevelTest extends LaravelTestCase
{
    public function testItShouldMakeConnectorSetup()
    {
        $config = $this->createMock(Consumer::class);
        $this->app->instance(ConsumerTopic::class, $this->createMock(ConsumerTopic::class));

        $connector = new HighLevel($config);

        $config->expects($this->once())
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

        $this->assertInstanceOf(HighLevelConsumer::class, $result);
    }
}

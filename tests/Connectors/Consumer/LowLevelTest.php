<?php
namespace Test;

use Metamorphosis\Broker;
use Metamorphosis\Config;
use Metamorphosis\Connectors\Consumer\LowLevel;
use Metamorphosis\Consumers\LowLevel as LowLevelConsumer;
use RdKafka\ConsumerTopic;
use Tests\LaravelTestCase;

class LowLevelTest extends LaravelTestCase
{
    /** @test */
    public function it_should_make_connector_setup()
    {
        $config = $this->createMock(Config::class);
        $this->app->instance(ConsumerTopic::class, $this->createMock(ConsumerTopic::class));

        $connector = new LowLevel($config);

        $config->expects($this->once())
            ->method('getBrokerConfig')
            ->will($this->returnValue($this->createMock(Broker::class)));

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

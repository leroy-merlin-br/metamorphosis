<?php
namespace Test;

use Metamorphosis\Broker;
use Metamorphosis\Config;
use Metamorphosis\Connector;
use RdKafka\Conf;
use RdKafka\ConsumerTopic;
use RdKafka\KafkaConsumer;
use Tests\LaravelTestCase;

class ConnectorTest extends LaravelTestCase
{
    /** @test */
    public function it_should_make_connector_setup()
    {
        $config = $this->createMock(Config::class);
        $this->app->instance(ConsumerTopic::class, $this->createMock(ConsumerTopic::class));

        $connector = new Connector($config);

        $config->expects($this->once())
            ->method('getBrokerConfig')
            ->will($this->returnValue($this->createMock(Broker::class)));

        $config->expects($this->once())
            ->method('getConsumerGroupOffset')
            ->will($this->returnValue('smallest'));

        $result = $connector->getConsumer();

        $this->assertInstanceOf(ConsumerTopic::class, $result);
    }
}

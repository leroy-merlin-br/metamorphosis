<?php
namespace Test;

use Metamorphosis\Broker;
use Metamorphosis\Config;
use Metamorphosis\Connector;
use RdKafka\Conf;
use RdKafka\KafkaConsumer;
use Tests\LaravelTestCase;

class ConnectorTest extends LaravelTestCase
{
    /** @test */
    public function it_should_make_connector_setup()
    {
        $config = $this->createMock(Config::class);
        $conf = $this->app->instance(Conf::class, $this->createMock(Conf::class));
        $consumer = $this->app->instance(KafkaConsumer::class, $this->createMock(KafkaConsumer::class));

        $connector = new Connector($config);

        $config->expects($this->once())
            ->method('getBrokerConfig')
            ->will($this->returnValue($this->createMock(Broker::class)));

        $result = $connector->getConsumer();

        $this->assertInstanceOf(KafkaConsumer::class, $result);
    }
}

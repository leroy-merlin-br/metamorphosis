<?php
namespace Tests\Connectors\Producer;

use Metamorphosis\Config;
use Metamorphosis\Connectors\Producer\Connector;
use RdKafka\ProducerTopic;
use Tests\LaravelTestCase;

class ConnectorTest extends LaravelTestCase
{
    /** @test */
    public function it_should_make_setup()
    {
        $config = $this->createMock(Config::class);

        $connector = new Connector($config);

        $producer = $connector->getProducer();

        $this->assertInstanceOf(ProducerTopic::class, $producer);
    }
}

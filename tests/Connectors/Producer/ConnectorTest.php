<?php
namespace Tests\Connectors\Producer;

use Metamorphosis\Config\Producer;
use Metamorphosis\Connectors\Producer\Connector;
use RdKafka\ProducerTopic;
use Tests\LaravelTestCase;

class ConnectorTest extends LaravelTestCase
{
    /** @test */
    public function it_should_make_setup()
    {
        $config = $this->createMock(Producer::class);

        $connector = new Connector();

        $producer = $connector->getProducerTopic($config);

        $this->assertInstanceOf(ProducerTopic::class, $producer);
    }
}

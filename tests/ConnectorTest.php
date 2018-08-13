<?php
namespace Test;

use Metamorphosis\Broker;
use Metamorphosis\Connector;
use RdKafka\Conf;
use Tests\LaravelTestCase;

class ConnectorTest extends LaravelTestCase
{
    /** @test */
    public function it_should_make_connector_setup()
    {
        $broker = new Broker('some-connection');

        $connector = new Connector($broker);

        $conf = $connector->setup();

        $this->assertInstanceOf(Conf::class, $conf);
    }
}

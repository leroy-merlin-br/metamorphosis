<?php
namespace Tests;

use Metamorphosis\Authentication\NoAuthentication;
use Metamorphosis\Broker;
use RdKafka\Conf;

class BrokerTest extends LaravelTestCase
{
    public function testItShouldConstructBrokerWithoutAuthentication()
    {
        $broker = new Broker('some-connection');

        $this->assertSame('some-connection', $broker->getConnections());
        $this->assertInstanceOf(NoAuthentication::class, $broker->getAuthentication());
    }

    public function testItShouldPrepareAuthentication()
    {
        $broker = new Broker('some-connection');

        $this->assertNull($broker->prepareAuthentication(new Conf()));
    }
}

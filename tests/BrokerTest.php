<?php
namespace Tests;

use Metamorphosis\Authentication\NoAuthentication;
use Metamorphosis\Broker;
use RdKafka\Conf;

class BrokerTest extends LaravelTestCase
{
    /** @test */
    public function it_should_construct_broker_without_authentication()
    {
        $broker = new Broker('some-connection');

        $this->assertSame('some-connection', $broker->getConnection());
        $this->assertInstanceOf(NoAuthentication::class, $broker->getAuthentication());
    }

    /** @test */
    public function it_should_authenticate()
    {
        $broker = new Broker('some-connection');

        $this->assertNull($broker->authenticate(new Conf()));
    }
}

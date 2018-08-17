<?php
namespace Metamorphosis;

use RdKafka\Conf;

class Connector
{
    /**
     * @var Broker
     */
    protected $broker;

    public function __construct(Broker $broker)
    {
        $this->broker = $broker;
    }

    public function setup(): Conf
    {
        $conf = new Conf();

        $conf->set('metadata.broker.list', $this->broker->getConnections());

        $this->broker->authentication($conf);

        return $conf;
    }
}

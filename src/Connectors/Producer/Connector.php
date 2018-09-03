<?php
namespace Metamorphosis\Connectors\Producer;

use Metamorphosis\Config;
use RdKafka\Conf;
use RdKafka\ProducerTopic;

class Connector
{
    /**
     * @var Config
     */
    protected $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function getProducer(): ProducerTopic
    {
        $broker = $this->config->getBrokerConfig();

        $conf = resolve(Conf::class);

        $conf->set('metadata.broker.list', $broker->getConnections());

        $broker->authenticate($conf);

        $producer = new \RdKafka\Producer($conf);

        return $producer->newTopic($this->config->getTopic());
    }
}

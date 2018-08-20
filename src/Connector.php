<?php
namespace Metamorphosis;

use RdKafka\Conf;
use RdKafka\KafkaConsumer;

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

    public function getConsumer(): KafkaConsumer
    {
        $conf = $this->getConf();

        $conf->set('group.id', $this->config->getConsumerGroupId());
        $conf->set('auto.offset.reset', $this->config->getConsumerGroupOffset());

        $consumer = resolve(KafkaConsumer::class, ['conf' => $conf]);
        $consumer->subscribe([$this->config->getTopic()]);

        return $consumer;
    }

    protected function getConf(): Conf
    {
        $broker = $this->config->getBrokerConfig();

        $conf = resolve(Conf::class);

        $conf->set('metadata.broker.list', $broker->getConnections());

        $broker->authenticate($conf);

        return $conf;
    }
}

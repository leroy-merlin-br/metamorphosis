<?php
namespace Metamorphosis\Connectors\Producer;

use Metamorphosis\Config;
use RdKafka\Conf;
use RdKafka\Producer as KafkaProducer;
use RdKafka\ProducerTopic;

class Connector
{
    public function getProducer(Config $config): ProducerTopic
    {
        $broker = $config->getBrokerConfig();

        $conf = resolve(Conf::class);

        $conf->set('metadata.broker.list', $broker->getConnections());

        $broker->authenticate($conf);

        $producer = app(KafkaProducer::class, ['conf' => $conf]);

        return $producer->newTopic($config->getTopic());
    }
}

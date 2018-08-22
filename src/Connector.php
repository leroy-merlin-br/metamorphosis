<?php
namespace Metamorphosis;

use RdKafka\Conf;
use RdKafka\ConsumerTopic;
use RdKafka\TopicConf;

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

    public function getConsumer(): ConsumerTopic
    {
        $conf = $this->getConf();

        $conf->set('group.id', $this->config->getConsumerGroupId());

        $broker = $this->config->getBrokerConfig();

        $broker->authenticate($conf);

        $consumer = new \RdKafka\Consumer($conf);

        $consumer->addBrokers($broker->getConnections());

        $topicConf = new TopicConf();

        $topicConf->set('offset.store.method', 'broker');
        //$topicConf->set('offset.store.method', 'broker');
        // make this configurable ? config/kafka.php

        // Set where to start consuming messages when there is no initial offset in
        // offset store or the desired offset is out of range.
        // 'smallest': start from the beginning
        $topicConf->set('auto.offset.reset', $this->config->getConsumerGroupOffset());

        $topicConsumer = $consumer->newTopic($this->config->getTopic(), $topicConf);

        $topicConsumer->consumeStart(0, 4000);

        return $topicConsumer;
    }

    protected function getConf(): Conf
    {
        return resolve(Conf::class);
    }
}

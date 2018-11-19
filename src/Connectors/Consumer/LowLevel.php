<?php
namespace Metamorphosis\Connectors\Consumer;

use Metamorphosis\Config\Consumer as ConfigConsumer;
use Metamorphosis\Connectors\AbstractConnector;
use Metamorphosis\Consumers\ConsumerInterface;
use Metamorphosis\Consumers\LowLevel as LowLevelConsumer;
use RdKafka\Consumer;
use RdKafka\TopicConf;

class LowLevel extends AbstractConnector implements ConnectorInterface
{
    public function __construct(ConfigConsumer $config)
    {
        $this->config = $config;
    }

    public function getConsumer(): ConsumerInterface
    {
        $conf = $this->getDefaultConf($this->config);

        $conf->set('group.id', $this->config->getConsumerGroupId());

        $consumer = new Consumer($conf);
        $consumer->addBrokers($this->config->getBrokerConfig()->getConnections());

        $topicConfig = $this->getTopicConfigs();

        $topicConsumer = $consumer->newTopic($this->config->getTopic(), $topicConfig);

        $topicConsumer->consumeStart($this->config->getConsumerPartition(), $this->config->getConsumerOffset());

        return new LowLevelConsumer($this->config, $topicConsumer);
    }

    protected function getTopicConfigs()
    {
        $topicConfig = new TopicConf();

        // Set where to start consuming messages when there is no initial offset in
        // offset store or the desired offset is out of range.
        // 'smallest': start from the beginning
        $topicConfig->set('auto.offset.reset', $this->config->getConsumerOffsetReset());

        return $topicConfig;
    }
}

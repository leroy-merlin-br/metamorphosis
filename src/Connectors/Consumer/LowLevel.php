<?php
namespace Metamorphosis\Connectors\Consumer;

use Metamorphosis\Config;
use Metamorphosis\Consumers\ConsumerInterface;
use Metamorphosis\Consumers\LowLevel as LowLevelConsumer;
use RdKafka\Conf;
use RdKafka\Consumer;
use RdKafka\TopicConf;

class LowLevel implements ConnectorInterface
{
    /**
     * @var Config
     */
    protected $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function getConsumer(): ConsumerInterface
    {
        $conf = $this->getConf();
        $conf->set('group.id', $this->config->getConsumerGroupId());

        $broker = $this->config->getBrokerConfig();
        $broker->authenticate($conf);

        $consumer = new Consumer($conf);
        $consumer->addBrokers($broker->getConnections());

        $topicConfig = $this->getTopicConfigs();

        $topicConsumer = $consumer->newTopic($this->config->getTopic(), $topicConfig);

        // get partition from config/command option and offset for command option/config?
        $topicConsumer->consumeStart($this->config->getPartition(), $this->config->getConsumerOffset());

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

    protected function getConf(): Conf
    {
        return resolve(Conf::class);
    }
}

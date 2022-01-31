<?php

namespace Metamorphosis\Connectors\Consumer;

use Metamorphosis\AbstractConfigManager;
use Metamorphosis\Authentication\Factory;
use Metamorphosis\Consumers\ConsumerInterface;
use Metamorphosis\Consumers\LowLevel as LowLevelConsumer;
use Metamorphosis\TopicHandler\ConfigOptions\Consumer as ConfigOptions;
use RdKafka\Conf;
use RdKafka\Consumer;
use RdKafka\TopicConf;

class LowLevel implements ConnectorInterface
{
    public function getConsumer(bool $autoCommit, ConfigOptions $configOptions): ConsumerInterface
    {
        $conf = $this->getConf();
        $maxPollIntervalMs = (int) $configOptions->getTimeout();
        $conf->set(
            'max.poll.interval.ms',
            $maxPollIntervalMs ?: 300000
        );
        $conf->set('group.id', $configOptions->getConsumerGroup());
        if (!$autoCommit) {
            $conf->set('enable.auto.commit', 'false');
        }

        $broker = $configOptions->getBroker();
        Factory::authenticate($conf, $broker->getAuth());

        $consumer = new Consumer($conf);
        $consumer->addBrokers($broker->getConnections());

        $topicConf = $this->getTopicConfigs($configOptions);
        $topicConsumer = $consumer->newTopic($configOptions->getTopicId(), $topicConf);

        $topicConsumer->consumeStart($configOptions->getPartition(), $configOptions->getOffset());

        return new LowLevelConsumer($topicConsumer, $configOptions);
    }

    protected function getTopicConfigs(ConfigOptions $configOptions)
    {
        $topicConfig = new TopicConf();

        // Set where to start consuming messages when there is no initial offset in
        // offset store or the desired offset is out of range.
        // 'smallest': start from the beginning
        $topicConfig->set('auto.offset.reset', $configOptions->getOffsetReset());

        return $topicConfig;
    }

    protected function getConf(): Conf
    {
        return resolve(Conf::class);
    }
}

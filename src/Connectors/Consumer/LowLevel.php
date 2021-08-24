<?php
namespace Metamorphosis\Connectors\Consumer;

use Metamorphosis\Authentication\Factory;
use Metamorphosis\ConfigManager;
use Metamorphosis\Consumers\ConsumerInterface;
use Metamorphosis\Consumers\LowLevel as LowLevelConsumer;
use RdKafka\Conf;
use RdKafka\Consumer;
use RdKafka\TopicConf;

class LowLevel implements ConnectorInterface
{
    public function getConsumer(bool $autoCommit, ConfigManager $configManager): ConsumerInterface
    {
        $conf = $this->getConf();
        $conf->set('group.id', $configManager->get('consumer_group'));
        if (!$autoCommit) {
            $conf->set('enable.auto.commit', 'false');
        }

        Factory::authenticate($conf, $configManager);

        $consumer = new Consumer($conf);
        $consumer->addBrokers($configManager->get('connections'));

        $topicConf = $this->getTopicConfigs($configManager);
        $topicConsumer = $consumer->newTopic($configManager->get('topic_id'), $topicConf);

        $topicConsumer->consumeStart($configManager->get('partition'), $configManager->get('offset'));

        return new LowLevelConsumer($topicConsumer);
    }

    protected function getTopicConfigs(ConfigManager $configManager)
    {
        $topicConfig = new TopicConf();

        // Set where to start consuming messages when there is no initial offset in
        // offset store or the desired offset is out of range.
        // 'smallest': start from the beginning
        $topicConfig->set('auto.offset.reset', $configManager->get('offset_reset'));

        return $topicConfig;
    }

    protected function getConf(): Conf
    {
        return resolve(Conf::class);
    }
}

<?php
namespace Metamorphosis\Connectors\Consumer;

use Metamorphosis\Authentication\Factory;
use Metamorphosis\Consumers\ConsumerInterface;
use Metamorphosis\Consumers\LowLevel as LowLevelConsumer;
use Metamorphosis\Facades\ConfigManager;
use RdKafka\Conf;
use RdKafka\Consumer;
use RdKafka\TopicConf;

class LowLevel implements ConnectorInterface
{
    public function getConsumer(bool $autoCommit): ConsumerInterface
    {
        $conf = $this->getConf();
        $conf->set('group.id', ConfigManager::get('consumer_group'));
        if (!$autoCommit) {
            $conf->set('enable.auto.commit', 'false');
        }

        Factory::authenticate($conf);

        $consumer = new Consumer($conf);
        $consumer->addBrokers(ConfigManager::get('connections'));

        $topicConf = $this->getTopicConfigs();
        $topicConsumer = $consumer->newTopic(ConfigManager::get('topic_id'), $topicConf);

        $topicConsumer->consumeStart(ConfigManager::get('partition'), ConfigManager::get('offset'));

        return new LowLevelConsumer($topicConsumer);
    }

    protected function getTopicConfigs()
    {
        $topicConfig = new TopicConf();

        // Set where to start consuming messages when there is no initial offset in
        // offset store or the desired offset is out of range.
        // 'smallest': start from the beginning
        $topicConfig->set('auto.offset.reset', ConfigManager::get('offset_reset'));

        return $topicConfig;
    }

    protected function getConf(): Conf
    {
        return resolve(Conf::class);
    }
}

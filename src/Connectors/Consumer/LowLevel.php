<?php
namespace Metamorphosis\Connectors\Consumer;

use Metamorphosis\Authentication\Factory;
use Metamorphosis\Consumers\ConsumerInterface;
use Metamorphosis\Consumers\LowLevel as LowLevelConsumer;
use Metamorphosis\Manager;
use RdKafka\Conf;
use RdKafka\Consumer;
use RdKafka\TopicConf;

class LowLevel implements ConnectorInterface
{
    public function getConsumer(): ConsumerInterface
    {
        $conf = $this->getConf();
        $conf->set('group.id', Manager::get('consumer_group'));
        Factory::authenticate($conf);

        $consumer = new Consumer($conf);
        $consumer->addBrokers(Manager::get('connections'));

        $topicConsumer = $consumer->newTopic(Manager::get('topic_id'), $this->getTopicConfigs());

        $topicConsumer->consumeStart(Manager::get('partition'), Manager::get('offset'));

        return new LowLevelConsumer($topicConsumer);
    }

    protected function getTopicConfigs()
    {
        $topicConfig = new TopicConf();

        // Set where to start consuming messages when there is no initial offset in
        // offset store or the desired offset is out of range.
        // 'smallest': start from the beginning
        $topicConfig->set('auto.offset.reset', Manager::get('offset_reset'));

        return $topicConfig;
    }

    protected function getConf(): Conf
    {
        return resolve(Conf::class);
    }
}

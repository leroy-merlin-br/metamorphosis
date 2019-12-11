<?php
namespace Metamorphosis\Connectors\Consumer;

use Metamorphosis\Authentication\Factory;
use Metamorphosis\Consumers\ConsumerInterface;
use Metamorphosis\Consumers\LowLevel as LowLevelConsumer;
use RdKafka\Conf;
use RdKafka\Consumer;
use RdKafka\TopicConf;

class LowLevel implements ConnectorInterface
{
    public function getConsumer(): ConsumerInterface
    {
        $conf = $this->getConf();
        $conf->set('group.id', config('kafka.runtime.consumer-group'));
        Factory::authenticate($conf);

        $consumer = new Consumer($conf);
        $consumer->addBrokers(config('kafka.runtime.connections'));

        $topicConsumer = $consumer->newTopic(config('kafka.runtime.topic_id'), $this->getTopicConfigs());

        $topicConsumer->consumeStart(config('kafka.runtime.partition'), config('kafka.runtime.offset'));

        return new LowLevelConsumer($topicConsumer);
    }

    protected function getTopicConfigs()
    {
        $topicConfig = new TopicConf();

        // Set where to start consuming messages when there is no initial offset in
        // offset store or the desired offset is out of range.
        // 'smallest': start from the beginning
        $topicConfig->set('auto.offset.reset', config('kafka.runtime.offset-reset'));

        return $topicConfig;
    }

    protected function getConf(): Conf
    {
        return resolve(Conf::class);
    }
}

<?php
namespace Metamorphosis\Connectors\Consumer;

use Metamorphosis\Authentication\Factory;
use Metamorphosis\Consumers\ConsumerInterface;
use Metamorphosis\Consumers\HighLevel as HighLevelConsumer;
use RdKafka\Conf;
use RdKafka\KafkaConsumer;

class HighLevel implements ConnectorInterface
{
    public function getConsumer(): ConsumerInterface
    {
        $conf = $this->getConf();

        $conf->set('group.id', config('kafka.runtime.consumer-group'));
        $conf->set('auto.offset.reset', config('kafka.runtime.offset_reset'));

        $consumer = app(KafkaConsumer::class, ['conf' => $conf]);
        $consumer->subscribe([config('kafka.runtime.topic_id')]);

        return app(HighLevelConsumer::class, compact('consumer'));
    }

    protected function getConf(): Conf
    {
        $conf = resolve(Conf::class);
        Factory::authenticate($conf);

        $conf->set('metadata.broker.list', config('kafka.runtime.connections'));

        return $conf;
    }
}

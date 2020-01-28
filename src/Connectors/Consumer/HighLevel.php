<?php
namespace Metamorphosis\Connectors\Consumer;

use Metamorphosis\Authentication\Factory;
use Metamorphosis\Consumers\ConsumerInterface;
use Metamorphosis\Consumers\HighLevel as HighLevelConsumer;
use Metamorphosis\Facades\Manager;
use RdKafka\Conf;
use RdKafka\KafkaConsumer;

class HighLevel implements ConnectorInterface
{
    public function getConsumer(): ConsumerInterface
    {
        $conf = $this->getConf();

        $conf->set('group.id', ConfigManager::get('consumer_group'));
        $conf->set('auto.offset.reset', ConfigManager::get('offset_reset'));

        $consumer = app(KafkaConsumer::class, ['conf' => $conf]);
        $consumer->subscribe([ConfigManager::get('topic_id')]);

        return app(HighLevelConsumer::class, compact('consumer'));
    }

    protected function getConf(): Conf
    {
        $conf = resolve(Conf::class);
        Factory::authenticate($conf);

        $conf->set('metadata.broker.list', ConfigManager::get('connections'));

        return $conf;
    }
}

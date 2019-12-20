<?php
namespace Metamorphosis\Connectors\Consumer;

use Kafka\Consumer;
use Kafka\ConsumerConfig;
use Metamorphosis\Authentication\Factory;
use Metamorphosis\Facades\Manager;

class LowLevel implements ConnectorInterface
{
    public function getConsumer(): Consumer
    {
        $config = ConsumerConfig::getInstance();
        $config->setMetadataRefreshIntervalMs(10000);
        $config->setMetadataBrokerList(Manager::get('connections'));
        $config->setGroupId(Manager::get('consumer_group'));
        $config->setBrokerVersion('1.0.0');
        $config->setTopics([Manager::get('topic_id')]);
        $config->setOffsetReset('earliest');
        Factory::authenticate($config);

        return new Consumer();
    }
}

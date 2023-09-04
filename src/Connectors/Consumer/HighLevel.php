<?php

namespace Metamorphosis\Connectors\Consumer;

use Metamorphosis\AbstractConfigManager;
use Metamorphosis\Authentication\Factory;
use Metamorphosis\Consumers\ConsumerInterface;
use Metamorphosis\Consumers\HighLevel as HighLevelConsumer;
use RdKafka\Conf;
use RdKafka\KafkaConsumer;

class HighLevel implements ConnectorInterface
{
    public function getConsumer(bool $autoCommit, AbstractConfigManager $configManager): ConsumerInterface
    {
        $conf = $this->getConf($configManager);
        $maxPollIntervalMs = (int) $configManager->get('max_poll_interval_ms');

        $conf->set('group.id', $configManager->get('consumer_group'));
        $conf->set('auto.offset.reset', $configManager->get('offset_reset'));
        $conf->set(
            'max.poll.interval.ms',
            $maxPollIntervalMs ?: 300000
        );

        if (!$autoCommit) {
            $conf->set('enable.auto.commit', 'false');
        }

        $consumer = app(KafkaConsumer::class, ['conf' => $conf]);
        $consumer->subscribe([$configManager->get('topic_id')]);
        $timeout = $configManager->get('timeout');

        return app(HighLevelConsumer::class, compact('consumer', 'timeout'));
    }

    protected function getConf(AbstractConfigManager $configManager): Conf
    {
        $conf = resolve(Conf::class);
        Factory::authenticate($conf, $configManager);

        $conf->set('metadata.broker.list', $configManager->get('connections'));

        return $conf;
    }
}

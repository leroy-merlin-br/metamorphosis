<?php
namespace Metamorphosis\Connectors\Consumer;

use Metamorphosis\Authentication\Factory;
use Metamorphosis\Consumers\ConsumerInterface;
use Metamorphosis\Consumers\HighLevel as HighLevelConsumer;
use Metamorphosis\TopicHandler\ConfigOptions\Consumer as ConfigOptions;
use RdKafka\Conf;
use RdKafka\KafkaConsumer;

class HighLevel implements ConnectorInterface
{
    public function getConsumer(bool $autoCommit, ConfigOptions $configOptions): ConsumerInterface
    {
        $conf = $this->getConf($configOptions);

        $conf->set('group.id', $configOptions->getConsumerGroup());
        $conf->set('auto.offset.reset', $configOptions->getOffsetReset());
        if (!$autoCommit) {
            $conf->set('enable.auto.commit', 'false');
        }

        $consumer = app(KafkaConsumer::class, ['conf' => $conf]);
        $consumer->subscribe([$configOptions->getTopicId()]);
        $timeout = $configOptions->getTimeout();

        return app(HighLevelConsumer::class, compact('consumer', 'timeout'));
    }

    protected function getConf(ConfigOptions $configOptions): Conf
    {
        $conf = resolve(Conf::class);
        $broker = $configOptions->getBroker();
        Factory::authenticate($conf, $broker->getAuth());

        $conf->set('metadata.broker.list', $broker->getConnections());

        return $conf;
    }
}

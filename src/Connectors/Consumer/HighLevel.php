<?php
namespace Metamorphosis\Connectors\Consumer;

use Metamorphosis\Config\Consumer as ConfigConsumer;
use Metamorphosis\Connectors\AbstractConnector;
use Metamorphosis\Consumers\ConsumerInterface;
use Metamorphosis\Consumers\HighLevel as HighLevelConsumer;
use RdKafka\KafkaConsumer;

class HighLevel extends AbstractConnector implements ConnectorInterface
{
    public function __construct(ConfigConsumer $config)
    {
        $this->config = $config;
    }

    public function getConsumer(): ConsumerInterface
    {
        $conf = $this->getDefaultConf($this->config);

        $conf->set('group.id', $this->config->getConsumerGroupId());
        $conf->set('auto.offset.reset', $this->config->getConsumerOffsetReset());

        $consumer = app(KafkaConsumer::class, ['conf' => $conf]);
        $consumer->subscribe([$this->config->getTopic()]);

        return new HighLevelConsumer($consumer);
    }
}

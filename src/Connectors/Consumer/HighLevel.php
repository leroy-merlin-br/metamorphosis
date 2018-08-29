<?php
namespace Metamorphosis\Connectors\Consumer;

use Metamorphosis\Config;
use Metamorphosis\Consumers\ConsumerInterface;
use Metamorphosis\Consumers\HighLevel as HighLevelConsumer;
use RdKafka\Conf;
use RdKafka\KafkaConsumer;

class HighLevel implements ConnectorInterface
{
    /**
     * @var Config
     */
    protected $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function getConsumer(): ConsumerInterface
    {
        $conf = $this->getConf();

        $conf->set('group.id', $this->config->getConsumerGroupId());
        $conf->set('auto.offset.reset', $this->config->getConsumerOffsetReset());

        $consumer = app(KafkaConsumer::class, ['conf' => $conf]);
        $consumer->subscribe([$this->config->getTopic()]);

        return new HighLevelConsumer($consumer);
    }

    protected function getConf(): Conf
    {
        $broker = $this->config->getBrokerConfig();

        $conf = resolve(Conf::class);

        $conf->set('metadata.broker.list', $broker->getConnections());

        $broker->authenticate($conf);

        return $conf;
    }
}

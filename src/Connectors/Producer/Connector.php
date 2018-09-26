<?php
namespace Metamorphosis\Connectors\Producer;

use Metamorphosis\Config\Producer;
use Metamorphosis\TopicHandler\Producer\Handler;
use RdKafka\Conf;
use RdKafka\Message;
use RdKafka\Producer as KafkaProducer;
use RdKafka\ProducerTopic;

class Connector
{
    /**
     * @var Handler
     */
    private $handler;

    public function setHandler(Handler $handler)
    {
        $this->handler = $handler;
    }

    public function getProducer(Producer $config): ProducerTopic
    {
        $broker = $config->getBrokerConfig();

        $conf = resolve(Conf::class);

        $conf->set('metadata.broker.list', $broker->getConnections());

        $conf->setDrMsgCb(function ($kafka, Message $message) {
            if ($message->err) {
                $this->handler->failed($message);
            } else {
                $this->handler->success($message);
            }
        });

        $broker->authenticate($conf);

        $producer = app(KafkaProducer::class, ['conf' => $conf]);

        return $producer->newTopic($config->getTopic());
    }
}

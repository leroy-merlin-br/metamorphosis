<?php
namespace Metamorphosis\Connectors\Producer;

use Metamorphosis\Authentication\Factory;
use Metamorphosis\Facades\Manager;
use RdKafka\Conf;
use RdKafka\Message;
use RdKafka\Producer as KafkaProducer;
use RdKafka\ProducerTopic;

class Connector
{
    public function getProducerTopic($handler = null): KafkaProducer
    {
        $conf = resolve(Conf::class);

        if ($handler) {
            $conf->setDrMsgCb(function ($kafka, Message $message) {
                if ($message->err) {
                    $this->handler->failed($message);
                } else {
                    $this->handler->success($message);
                }
            });
        }

        $conf->set('metadata.broker.list', Manager::get('connections'));

        $this->setCallbackResponses($conf);

        Factory::authenticate($conf);

        $producer = app(KafkaProducer::class, compact('conf'));

        $this->prepareQueueCallbackResponse($producer);

        return $producer;
    }
}

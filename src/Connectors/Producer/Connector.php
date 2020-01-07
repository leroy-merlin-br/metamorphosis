<?php
namespace Metamorphosis\Connectors\Producer;

use Metamorphosis\Authentication\Factory;
use Metamorphosis\Facades\Manager;
use RdKafka\Conf;
use RdKafka\Message;
use RdKafka\Producer as KafkaProducer;

class Connector
{
    public function getProducerTopic($handler = null): KafkaProducer
    {
        $conf = resolve(Conf::class);

        if ($handler) {
            $conf->setDrMsgCb(function ($kafka, Message $message) use ($handler) {
                if ($message->err) {
                    $handler->failed($message);
                } else {
                    $handler->success($message);
                }
            });
        }

        $conf->set('metadata.broker.list', Manager::get('connections'));

        Factory::authenticate($conf);

        return app(KafkaProducer::class, compact('conf'));
    }
}

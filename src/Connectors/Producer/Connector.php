<?php

namespace Metamorphosis\Connectors\Producer;

use Metamorphosis\Authentication\Factory;
use Metamorphosis\TopicHandler\ConfigOptions\Producer as ProducerConfigOptions;
use Metamorphosis\TopicHandler\Producer\HandleableResponseInterface;
use Metamorphosis\TopicHandler\Producer\HandlerInterface;
use RdKafka\Conf;
use RdKafka\Message;
use RdKafka\Producer as KafkaProducer;

class Connector
{
    public function getProducerTopic(
        HandlerInterface $handler,
        ProducerConfigOptions $producerConfigOptions
    ): KafkaProducer {
        $conf = resolve(Conf::class);

        if ($this->canHandleResponse($handler)) {
            $conf->setDrMsgCb(
                /** @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter */
                function ($kafka, Message $message) use ($handler) {
                    if ($message->err) {
                        $handler->failed($message);
                    } else {
                        $handler->success($message);
                    }
                }
            );
        }

        $broker = $producerConfigOptions->getBroker();
        $conf->set('metadata.broker.list', $broker->getConnections());

        Factory::authenticate($conf, $broker->getAuth());

        return app(KafkaProducer::class, compact('conf'));
    }

    private function canHandleResponse(HandlerInterface $handler): bool
    {
        return $handler instanceof HandleableResponseInterface;
    }
}

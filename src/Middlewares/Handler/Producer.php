<?php
namespace Metamorphosis\Middlewares\Handler;

use Kafka\ProducerConfig;
use Kafka\Producer as KafkaProducer;
use Metamorphosis\Connectors\Producer\Config;
use Metamorphosis\Facades\Manager;
use Metamorphosis\Middlewares\MiddlewareInterface;
use Metamorphosis\Record\RecordInterface;
use Metamorphosis\TopicHandler\Producer\HandleableResponseInterface;
use Metamorphosis\TopicHandler\Producer\HandlerInterface;

class Producer implements MiddlewareInterface
{
    /**
     * @var HandlerInterface
     */
    private $producerHandler;

    /**
     * @var Config
     */
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function process(RecordInterface $record, MiddlewareHandlerInterface $handler): void
    {
        $this->config->setOption($record->getTopicName());

        $config = ProducerConfig::getInstance();
        $config->setMetadataRefreshIntervalMs(10000);
        $config->setMetadataBrokerList(Manager::get('connections'));
        $config->setBrokerVersion('1.0.0');
        $config->setRequiredAck(Manager::get('requiredAcknowledgment') ?: 1);
        $config->setIsAsyn(Manager::get('isASync') ?: false);
        $config->setProduceInterval(500);

        $producer = app(KafkaProducer::class , [
            'producer' => (
                function() use ($record) {
                    return [
                        [
                            'topic' => Manager::get('topic_id'),
                            'value' => $record->getPayload(),
                            'key' => $record->getKey(),
                        ],
                    ];
                }
            )
        ]);

        if ($this->canHandleResponse($this->producerHandler) ) {
            $producer->success(function ($result) {
                $this->producerHandler->success($result);
            });

            $producer->error(function ($errorCode) {
                $this->producerHandler->failed($errorCode);
            });
        }

        $producer->send(true);
    }

    public function setProducerHandler(HandlerInterface $handler)
    {
        $this->producerHandler = $handler;
    }

    private function canHandleResponse($handler): bool
    {
        return $handler instanceof HandleableResponseInterface;
    }
}

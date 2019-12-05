<?php
namespace Metamorphosis;

use Metamorphosis\Avro\CachedSchemaRegistryClient;
use Metamorphosis\Avro\MessageSerializer;
use Metamorphosis\Record\ConsumerRecord;
use Metamorphosis\Record\RecordInterface;

class AvroConsumerRunner extends AbstractConsumerRunner
{
    protected function handleConsumerResponse($response): RecordInterface
    {
        $record = new ConsumerRecord($response);
        $this->serializer = new MessageSerializer(new CachedSchemaRegistryClient(
            $this->getConfig()->getBrokerConfig()->getSchemaUri()),
            []
        );

        $record->setPayload($this->serializer->decodeMessage($record->getPayload()));

        return $record;
    }
}

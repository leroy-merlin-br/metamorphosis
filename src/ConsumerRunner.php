<?php
namespace Metamorphosis;

use Metamorphosis\Record\RecordInterface;

class ConsumerRunner extends AbstractConsumerRunner
{
    protected function handleConsumerResponse($response): RecordInterface
    {
        return new ConsumerRecord($response);
    }
}

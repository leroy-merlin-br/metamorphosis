<?php
namespace Metamorphosis;

use Metamorphosis\Record\ConsumerRecord;
use Metamorphosis\Record\RecordInterface;

class ConsumerRunner extends AbstractConsumerRunner
{
    protected function handleConsumerResponse($response): RecordInterface
    {
        return app(ConsumerRecord::class, compact('response'));
    }
}

<?php
namespace Tests\Dummies;

use Exception;
use Metamorphosis\Record\Record;
use Metamorphosis\TopicHandler\Consumer\AbstractHandler;

class ProducerHandlerDummy extends AbstractHandler
{
    public function handle(Record $data): void
    {
    }

    public function failed(Exception $exception): void
    {
    }
}

<?php
namespace Tests\Unit\Dummies;

use Metamorphosis\Record\RecordInterface;
use Metamorphosis\TopicHandler\Consumer\AbstractHandler;
use Throwable;

class ConsumerHandlerDummy extends AbstractHandler
{
    public function handle(RecordInterface $data): void
    {
    }

    public function failed(Throwable $throwable): void
    {
    }
}

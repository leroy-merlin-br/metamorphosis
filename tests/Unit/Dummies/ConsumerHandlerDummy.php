<?php
namespace Tests\Unit\Dummies;

use Exception;
use Metamorphosis\Record\RecordInterface;
use Metamorphosis\TopicHandler\Consumer\AbstractHandler;

class ConsumerHandlerDummy extends AbstractHandler
{
    public function __construct()
    {
        parent::__construct(['topic_id' => 'kafka-override']);
    }

    public function handle(RecordInterface $data): void
    {
    }

    public function failed(Exception $exception): void
    {
    }
}

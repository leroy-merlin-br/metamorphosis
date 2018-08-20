<?php
namespace Tests\Dummies;

use Exception;
use Metamorphosis\Message;
use Metamorphosis\TopicHandler\Consumer\AbstractHandler;

class ConsumerHandlerDummy extends AbstractHandler
{
    public function handle(Message $data): void
    {
    }

    public function failed(Exception $exception): void
    {
    }
}

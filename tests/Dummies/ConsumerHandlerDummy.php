<?php
namespace Tests\Dummies;

use Exception;
use Metamorphosis\Contracts\ConsumerTopicHandler;
use Metamorphosis\Message;

class ConsumerHandlerDummy implements ConsumerTopicHandler
{
    public function handle(Message $data): bool
    {
        return true;
    }

    public function failed(Exception $exception): void
    {
    }
}

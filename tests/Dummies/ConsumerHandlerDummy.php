<?php
namespace Tests\Dummies;

use Metamorphosis\Contracts\ConsumerTopicHandler;

class ConsumerHandlerDummy implements ConsumerTopicHandler
{
    public function handle(array $data): bool
    {
        return true;
    }
}

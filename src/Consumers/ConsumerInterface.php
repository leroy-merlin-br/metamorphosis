<?php

namespace Metamorphosis\Consumers;

use RdKafka\Message;

interface ConsumerInterface
{
    public function consume(): ?Message;

    public function canCommit(): bool;
}

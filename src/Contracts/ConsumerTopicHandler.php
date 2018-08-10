<?php
namespace Metamorphosis\Contracts;

interface ConsumerTopicHandler
{
    /**
     * Handle payload.
     *
     * @param array $data
     *
     * @return bool
     */
    public function handle(array $data): bool;
}

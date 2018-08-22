<?php
namespace Metamorphosis\Consumers;

use Metamorphosis\Record;

interface ConsumerInterface
{
    /**
     * @param int $timeout
     *
     * @return Record
     */
    public function consume(int $timeout): Record;
}

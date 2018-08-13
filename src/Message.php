<?php
namespace Metamorphosis;

use RdKafka\Message as KafkaMessage;

class Message
{
    /**
     * @var KafkaMessage
     */
    protected $original;

    public function __construct(KafkaMessage $original)
    {
        $this->original = $original;
    }

    protected function hasError(): bool
    {
        return RD_KAFKA_RESP_ERR_NO_ERROR !== $this->original->err;
    }
}

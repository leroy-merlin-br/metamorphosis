<?php

namespace Metamorphosis\Connectors\Producer;

use Metamorphosis\Connectors\AbstractConfig;

class Config extends AbstractConfig
{
    /**
     * @var string[]
     */
    protected array $rules = [
        'topic_id' => 'required',
        'connections' => 'required|string',
        'timeout' => 'int',
        'is_async' => 'boolean',
        'required_acknowledgment' => 'boolean',
        'max_poll_records' => 'int',
        'flush_attempts' => 'int',
        'auth' => 'nullable|array',
        'middlewares' => 'array',
        'ssl_verify' => 'boolean',
    ];

    /**
     * @var mixed[]
     */
    protected array $default = [
        'timeout' => 1000,
        'is_async' => true,
        'required_acknowledgment' => true,
        'max_poll_records' => 500,
        'flush_attempts' => 10,
        'partition' => RD_KAFKA_PARTITION_UA,
        'ssl_verify' => false,
    ];
}

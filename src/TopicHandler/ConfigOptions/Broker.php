<?php

namespace Metamorphosis\TopicHandler\ConfigOptions;

use Metamorphosis\TopicHandler\ConfigOptions\Auth\AuthInterface;

class Broker
{
    /**
     * @example 'kafka:9092'
     *
     */
    private string $connections;

    /**
     * If your broker doest not have authentication, you can
     * remove this configuration, or set as empty.
     * The Authentication types may be "ssl" "sasl_ssl" or "none"
     *
     */
    private AuthInterface $auth;

    public function __construct(string $connections, AuthInterface $auth)
    {
        $this->connections = $connections;
        $this->auth = $auth;
    }

    public function getConnections(): string
    {
        return $this->connections;
    }

    public function getAuth(): AuthInterface
    {
        return $this->auth;
    }

    public function toArray(): array
    {
        return [
            'connections' => $this->getConnections(),
            'auth' => $this->getAuth()->toArray() ?: null,
        ];
    }
}

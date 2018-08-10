<?php
namespace Metamorphosis;

use Metamorphosis\Authentication\Factory;
use RdKafka\Conf;

class Broker
{
    /**
     * @var $connection string
     */
    protected $connection;

    /**
     * @var $authentication \Metamorphosis\Contracts\Authentication
     */
    protected $authentication;

    public function authentication(Conf $conf): void
    {
        $this->authentication->authenticate($conf);
    }

    public function getConnection(): string
{
    return $this->connection;
}

    public function setConnection(string $connection)
    {
        $this->connection = $connection;
    }

    public function setAuthentication($authConfig = null): void
    {
        $this->authentication = Factory::make($authConfig);
    }
}

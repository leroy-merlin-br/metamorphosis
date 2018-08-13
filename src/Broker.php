<?php
namespace Metamorphosis;

use Metamorphosis\Authentication\Factory;
use Metamorphosis\Contracts\Authentication;
use RdKafka\Conf;

class Broker
{
    /**
     * @var $connection string
     */
    protected $connection;

    /**
     * @var $authentication Authentication
     */
    protected $authentication;

    public function __construct(string $connection, array $authentication = null)
    {
        $this->setConnection($connection);
        $this->setAuthentication($authentication);
    }

    public function authentication(Conf $conf): void
    {
        $this->authentication->authenticate($conf);
    }

    public function getConnection(): string
    {
        return $this->connection;
    }

    protected function setConnection(string $connection): void
    {
        $this->connection = $connection;
    }

    protected function setAuthentication($authConfig = null): void
    {
        $this->authentication = Factory::make($authConfig);
    }
}

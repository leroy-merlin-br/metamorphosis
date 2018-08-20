<?php
namespace Metamorphosis;

use Metamorphosis\Authentication\Authentication;
use Metamorphosis\Authentication\Factory;
use RdKafka\Conf;

class Broker
{
    /**
     * @var string
     */
    protected $connection;

    /**
     * @var Authentication
     */
    protected $authentication;

    public function __construct(string $connection, array $authentication = null)
    {
        $this->setConnection($connection);
        $this->setAuthentication($authentication);
    }

    public function authenticate(Conf $conf): void
    {
        $this->authentication->authenticate($conf);
    }

    public function getConnection(): string
    {
        return $this->connection;
    }

    public function getAuthentication(): Authentication
    {
        return $this->authentication;
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

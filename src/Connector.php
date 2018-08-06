<?php
namespace Metamorphosis;

class Connector
{
    public $config;

    public function __construct(string $connection)
    {
        $this->config = config('kafka.'.$connection);
    }

    public function setup()
    {
        $conf = new \RdKafka\Conf();

        $conf->set('metadata.broker.list', $this->config['broker']);

        if ($this->config['auth']['ssl']) {
            $conf->set('security.protocol', 'ssl');
            $conf->set('ssl.ca.location', $this->config['auth']['ca']);
            $conf->set('ssl.certificate.location', $this->config['auth']['certificate']);
            $conf->set('ssl.key.location', $this->config['auth']['key']);
        }

        return $conf;
    }
}

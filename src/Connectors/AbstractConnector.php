<?php
namespace Metamorphosis\Connectors;

use Metamorphosis\Broker;
use RdKafka\Conf;
use Metamorphosis\Config\AbstractConfig;

abstract class AbstractConnector
{
    /**
     * @var AbstractConfig
     */
    protected $config;

    protected function getConf(Broker $broker): Conf
    {
        $conf = resolve(Conf::class);

        $conf->set('metadata.broker.list', $broker->getConnections());

        $broker->prepareAuthentication($conf);

        return $conf;
    }
}

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

    protected function getDefaultConf(Broker $broker): Conf
    {
        $conf = resolve(Conf::class);

        $conf->set('metadata.broker.list', $broker->getConnections());

        $broker->prepareAuthentication($conf);

        $this->setHighPerformance($conf);

        return $conf;
    }

    protected function setHighPerformance(Conf $conf): void
    {
        $conf->set('socket.timeout.ms', 50);

        pcntl_sigprocmask(SIG_BLOCK, [SIGIO]);
        $conf->set('internal.termination.signal', SIGIO);
    }
}

<?php
namespace Metamorphosis\Connectors;

use Metamorphosis\Config\AbstractConfig;
use RdKafka\Conf;

abstract class AbstractConnector
{
    /**
     * @var AbstractConfig
     */
    protected $config;

    protected function getDefaultConf(AbstractConfig $config): Conf
    {
        $conf = resolve(Conf::class);
        $broker = $config->getBrokerConfig();

        $conf->set('metadata.broker.list', $broker->getConnections());

        $broker->prepareAuthentication($conf);

        if ($config->isHighPerformanceEnabled()) {
            $this->setHighPerformance($conf);
        }

        return $conf;
    }

    protected function setHighPerformance(Conf $conf): void
    {
        $conf->set('socket.timeout.ms', 50);

        pcntl_sigprocmask(SIG_BLOCK, [SIGIO]);
        $conf->set('internal.termination.signal', SIGIO);
    }
}

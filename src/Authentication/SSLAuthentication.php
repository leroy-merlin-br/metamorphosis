<?php
namespace Metamorphosis\Authentication;

use Metamorphosis\TopicHandler\ConfigOptions\Auth\Ssl;
use RdKafka\Conf;

class SSLAuthentication implements AuthenticationInterface
{
    /**
     * @var Conf
     */
    private $conf;

    /**
     * @var Ssl
     */
    private $configOptions;

    public function __construct(Conf $conf, Ssl $configOptions)
    {
        $this->conf = $conf;
        $this->configOptions = $configOptions;

        $this->authenticate();
    }

    private function authenticate(): void
    {
        $this->conf->set('security.protocol', $this->configOptions->getType());
        $this->conf->set('ssl.ca.location', $this->configOptions->getCa());
        $this->conf->set('ssl.certificate.location', $this->configOptions->getCertificate());
        $this->conf->set('ssl.key.location', $this->configOptions->getKey());
    }
}

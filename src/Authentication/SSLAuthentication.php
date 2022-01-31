<?php

namespace Metamorphosis\Authentication;

use Metamorphosis\TopicHandler\ConfigOptions\Auth\Ssl;
use RdKafka\Conf;

class SSLAuthentication implements AuthenticationInterface
{
    private Conf $conf;

    private AbstractConfigManager $configManager;

    public function __construct(Conf $conf, Ssl $configSsl)
    {
        $this->conf = $conf;
        $this->configSsl = $configSsl;

        $this->authenticate();
    }

    private function authenticate(): void
    {
        $this->conf->set('security.protocol', $this->configSsl->getType());
        $this->conf->set('ssl.ca.location', $this->configSsl->getCa());
        $this->conf->set('ssl.certificate.location', $this->configSsl->getCertificate());
        $this->conf->set('ssl.key.location', $this->configSsl->getKey());
    }
}

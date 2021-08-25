<?php
namespace Metamorphosis\Authentication;

use Metamorphosis\ConfigManager;
use RdKafka\Conf;

class SSLAuthentication implements AuthenticationInterface
{
    /**
     * @var Conf
     */
    private $conf;

    /**
     * @var ConfigManager
     */
    private $configManager;

    public function __construct(Conf $conf, ConfigManager $configManager)
    {
        $this->conf = $conf;
        $this->configManager = $configManager;

        $this->authenticate();
    }

    private function authenticate(): void
    {
        $this->conf->set('security.protocol', $this->configManager->get('auth.type'));
        $this->conf->set('ssl.ca.location', $this->configManager->get('auth.ca'));
        $this->conf->set('ssl.certificate.location', $this->configManager->get('auth.certificate'));
        $this->conf->set('ssl.key.location', $this->configManager->get('auth.key'));
    }
}

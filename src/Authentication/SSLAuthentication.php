<?php
namespace Metamorphosis\Authentication;

use Metamorphosis\Facades\Manager;
use Kafka\Config;

class SSLAuthentication implements AuthenticationInterface
{
    /**
     * @var Config
     */
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;

        $this->authenticate();
    }

    private function authenticate(): void
    {
        $this->config->setSecurityProtocol(Config::SECURITY_PROTOCOL_SSL);
        $this->config->setSslLocalCert(Manager::get('auth.certificate'));
        $this->config->setSslCafile(Manager::get('auth.ca'));
        $this->config->setSslLocalPk(Manager::get('auth.key'));
    }
}

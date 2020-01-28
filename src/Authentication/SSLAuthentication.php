<?php
namespace Metamorphosis\Authentication;

use Metamorphosis\Facades\Manager;
use RdKafka\Conf;

class SSLAuthentication implements AuthenticationInterface
{
    /**
     * @var Conf
     */
    private $conf;

    public function __construct(Conf $conf)
    {
        $this->conf = $conf;

        $this->authenticate();
    }

    private function authenticate(): void
    {
        $this->conf->set('security.protocol', ConfigManager::get('auth.type'));
        $this->conf->set('ssl.ca.location', ConfigManager::get('auth.ca'));
        $this->conf->set('ssl.certificate.location', ConfigManager::get('auth.certificate'));
        $this->conf->set('ssl.key.location', ConfigManager::get('auth.key'));
    }
}

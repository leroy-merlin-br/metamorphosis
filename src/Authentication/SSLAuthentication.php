<?php
namespace Metamorphosis\Authentication;

use Metamorphosis\Manager;
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
        $this->conf->set('security.protocol', Manager::get('auth.type'));
        $this->conf->set('ssl.ca.location', Manager::get('auth.ca'));
        $this->conf->set('ssl.certificate.location', Manager::get('auth.certificate'));
        $this->conf->set('ssl.key.location', Manager::get('auth.key'));
    }
}

<?php
namespace Metamorphosis\Authentication;

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
        $this->conf->set('security.protocol', config('kafka.runtime.auth.type'));
        $this->conf->set('ssl.ca.location', config('kafka.runtime.auth.ca'));
        $this->conf->set('ssl.certificate.location', config('kafka.runtime.auth.certificate'));
        $this->conf->set('ssl.key.location', config('kafka.runtime.auth.key'));
    }
}

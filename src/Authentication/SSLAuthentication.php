<?php
namespace Metamorphosis\Authentication;

use Metamorphosis\Contracts\Authentication;
use RdKafka\Conf;

class SSLAuthentication implements Authentication
{
    protected $ca;

    protected $certificate;

    protected $key;

    public function __construct(array $authConfig)
    {
        $this->ca = $authConfig['ca'];
        $this->certificate = $authConfig['certificate'];
        $this->key = $authConfig['key'];
    }

    public function authenticate(Conf $conf)
    {
        $conf->set('security.protocol', 'ssl');
        $conf->set('ssl.ca.location', $this->ca);
        $conf->set('ssl.certificate.location', $this->certificate);
        $conf->set('ssl.key.location', $this->key);
    }
}

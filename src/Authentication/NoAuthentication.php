<?php
namespace Metamorphosis\Authentication;

use RdKafka\Conf;

class NoAuthentication implements Authentication
{
    public function authenticate(Conf $conf)
    {
    }
}

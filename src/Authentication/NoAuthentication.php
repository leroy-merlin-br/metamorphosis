<?php
namespace Metamorphosis\Authentication;

use RdKafka\Conf;

class NoAuthentication implements Authentication
{
    public function setAuthentication(Conf $conf)
    {
    }
}

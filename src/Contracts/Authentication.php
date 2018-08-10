<?php
namespace Metamorphosis\Contracts;

use RdKafka\Conf;

interface Authentication
{
    public function authenticate(Conf $conf);
}

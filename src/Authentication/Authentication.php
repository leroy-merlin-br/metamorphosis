<?php
namespace Metamorphosis\Authentication;

use RdKafka\Conf;

interface Authentication
{
    public function setAuthentication(Conf $conf);
}

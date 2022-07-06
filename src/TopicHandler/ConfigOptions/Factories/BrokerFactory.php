<?php

namespace Metamorphosis\TopicHandler\ConfigOptions\Factories;

use Metamorphosis\TopicHandler\ConfigOptions\Broker;

class BrokerFactory
{
    public static function make(array $brokerData): Broker
    {
        $brokerData['auth'] = AuthFactory::make($brokerData['auth'] ?? []);

        return app(Broker::class, $brokerData);
    }
}

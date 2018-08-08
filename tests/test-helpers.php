<?php

function dummyConsumerHandler()
{
}

function config($key)
{
    if ('kafka.brokers.default' == $key) {
        return [
            'broker' => '',
            'auth' => [
                'protocol' => 'ssl',
                'ca' => '/path/to/ca',
                'certificate' => '/path/to/certificate',
                'key' => '/path/to/key',
            ],
        ];
    }

    if ('kafka.topics.invalid-topic-key' == $key) {
        return null;
    }

    if ('kafka.topics.topic-invalid-broker' == $key) {
        return [
            'topic' => 'topic-name',
            'broker' => 'invalid-broker',
            'consumer-groups' => [
                'default-consumer' => [
                    'offset' => 'initial',
                    'consumer' => 'trim',
                ],
                'consumer-id' => [
                    'offset' => 'initial',
                    'consumer' => 'dummyConsumerHandler',
                ],
           ],
        ];
    }

    if ('kafka.brokers.invalid-broker' == $key) {
        return null;
    }

    return [
        'topic' => 'topic-name',
        'broker' => 'default',
        'consumer-groups' => [
            'default-consumer' => [
                'offset' => 'initial',
                'consumer' => 'trim',
            ],
            'consumer-id' => [
                'offset' => 'initial',
                'consumer' => 'dummyConsumerHandler',
            ],
       ],
    ];
}

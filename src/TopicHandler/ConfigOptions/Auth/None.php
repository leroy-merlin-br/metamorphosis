<?php

namespace Metamorphosis\TopicHandler\ConfigOptions\Auth;

class None implements AuthInterface
{
    public function toArray(): array
    {
        return [
            'type' => $this->getType()
        ];
    }

    public function getType(): string
    {
        return 'none';
    }
}

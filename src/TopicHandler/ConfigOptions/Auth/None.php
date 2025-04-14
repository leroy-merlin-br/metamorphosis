<?php

namespace Metamorphosis\TopicHandler\ConfigOptions\Auth;

class None implements AuthInterface
{
    #[\Override]
    public function toArray(): array
    {
        return [];
    }

    #[\Override]
    public function getType(): string
    {
        return EnumType::NONE_TYPE;
    }
}

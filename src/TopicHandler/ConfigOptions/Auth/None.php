<?php
namespace Metamorphosis\TopicHandler\ConfigOptions\Auth;

class None implements AuthInterface
{
    public function toArray(): array
    {
        return [];
    }

    public function getType(): string
    {
        return EnumType::NONE_TYPE;
    }
}

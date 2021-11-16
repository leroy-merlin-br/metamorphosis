<?php

namespace Metamorphosis\TopicHandler\ConfigOptions\Auth;

interface AuthInterface
{
    public function toArray(): array;

    public function getType(): string;
}

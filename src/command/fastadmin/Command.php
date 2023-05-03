<?php

namespace langdonglei\util\command\fastadmin;

class Command extends \Symfony\Component\Console\Command\Command
{
    const ROOT_PATH = __DIR__ . '/../../';
    const DS        = DIRECTORY_SEPARATOR;

    public function __construct(string $name = null)
    {
        parent::__construct($name);
    }
}
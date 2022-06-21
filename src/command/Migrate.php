<?php

namespace okcoder\think\filesystem\command;

class Migrate extends \think\migration\command\Migrate
{
    protected function getPath(): string
    {
        return dirname(__DIR__) . '/database' . DIRECTORY_SEPARATOR . 'migrations';
    }
}
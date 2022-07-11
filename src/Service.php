<?php

namespace okcoder\think\filesystem;

use okcoder\think\filesystem\command\migrate\Run as MigrateRun;

class Service extends \think\Service
{
    public function register()
    {
        $this->app->bind('filesystem', Filesystem::class);
    }
    
    public function boot()
    {
        $this->commands([
            MigrateRun::class
        ]);
    }
}

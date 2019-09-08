<?php

namespace Junges\StackOverflowPTBR\Tests;

use Facade\Ignition\IgnitionServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Junges\StackOverflowPTBR\TabServiceProvider;

abstract class TestCase extends Orchestra
{

    protected function getPackageProviders($app)
    {
        return [
            TabServiceProvider::class,
            IgnitionServiceProvider::class,
        ];
    }
}

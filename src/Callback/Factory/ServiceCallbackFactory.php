<?php

namespace Itseasy\Queue\Callback\Factory;

use Psr\Container\ContainerInterface;
use Itseasy\Queue\Callback\ServiceCallback;
use Symfony\Component\Console\Output\ConsoleOutput;

class ServiceCallbackFactory
{
    public function __invoke(ContainerInterface $container)
    {
        if ( php_sapi_name() == 'cli' ) {
            $output = new ConsoleOutput();
        }
        return new ServiceCallback($container, $output);
    }
}

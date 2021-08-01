<?php

namespace Itseasy\Queue\Callback\Factory;

use Psr\Container\ContainerInterface;
use Itseasy\Queue\Callback\ServiceCallback;

class ServiceCallbackFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new ServiceCallback($container);
    }
}

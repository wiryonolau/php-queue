<?php

namespace Itseasy\Queue\Callback\Factory;

use Psr\Container\ContainerInterface;
use Itseasy\Queue\Callback\ServiceCallback;
use Itseasy\Queue\Logger\DefaultLogger;

class ServiceCallbackFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $serviceCallback = new ServiceCallback($container);

        $serviceCallback->setLogger($container->get(DefaultLogger::class));
        return $serviceCallback;
    }
}

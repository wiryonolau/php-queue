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

        $logger = ($container->has("Logger") ? $container->get("Logger") : $container->get(DefaultLogger::class));
        $serviceCallback->setLogger($logger);
        
        return $serviceCallback;
    }
}

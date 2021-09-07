<?php

namespace Itseasy\Queue\Logger\Factory;

use Psr\Container\ContainerInterface;
use Laminas\Log\Logger;

class DefaultLoggerFactory
{
    public function __invoke() : Logger
    {
        $logger = new Logger();
        $logger->addWriter('stream', null, ['stream' => 'php://stderr']);
        return $logger;
    }
}

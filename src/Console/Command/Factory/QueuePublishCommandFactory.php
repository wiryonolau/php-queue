<?php

namespace Itseasy\Queue\Console\Command\Factory;

use Psr\Container\ContainerInterface;
use Itseasy\Queue\Console\Command\QueuePublishCommand;
use Itseasy\Queue\Service\QueueService;
use Itseasy\Queue\Logger\DefaultLogger;

class QueuePublishCommandFactory
{
    public function __invoke(ContainerInterface $container) : QueuePublishCommand
    {
        $queueService = $container->get(QueueService::class);
        $queuePublishCommand = new QueuePublishCommand($queueService);
        $queuePublishCommand->setLogger($container->get(DefaultLogger::class));

        return $queuePublishCommand;
    }
}

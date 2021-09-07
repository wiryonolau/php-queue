<?php

namespace Itseasy\Queue\Console\Command\Factory;

use Psr\Container\ContainerInterface;
use Itseasy\Queue\Console\Command\QueueConsumeCommand;
use Itseasy\Queue\Service\QueueService;
use Itseasy\Queue\Logger\DefaultLogger;

class QueueConsumeCommandFactory
{
    public function __invoke(ContainerInterface $container) : QueueConsumeCommand
    {
        $queueService = $container->get(QueueService::class);
        $queueConsumeCommand = new QueueConsumeCommand($queueService);
        $queueConsumeCommand->setLogger($container->get(DefaultLogger::class));

        return $queueConsumeCommand;
    }
}

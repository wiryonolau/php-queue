<?php

namespace Itseasy\Queue\Console\Command\Factory;

use Psr\Container\ContainerInterface;
use Itseasy\Queue\Console\Command\QueueConsumeCommand;
use Itseasy\Queue\Service\QueueService;

class QueueConsumeCommandFactory
{
    public function __invoke(ContainerInterface $container) : QueueConsumeCommand
    {
        $queueService = $container->get(QueueService::class);
        return new QueueConsumeCommand($queueService);
    }
}

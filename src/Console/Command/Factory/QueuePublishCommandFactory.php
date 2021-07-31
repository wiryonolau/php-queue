<?php

namespace Itseasy\Queue\Console\Command\Factory;

use Psr\Container\ContainerInterface;
use Itseasy\Queue\Console\Command\QueuePublishCommand;
use Itseasy\Queue\Service\QueueService;

class QueuePublishCommandFactory
{
    public function __invoke(ContainerInterface $container) : QueuePublishCommand
    {
        $queueService = $container->get(QueueService::class);
        return new QueuePublishCommand($queueService);
    }
}

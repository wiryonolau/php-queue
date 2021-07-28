<?php

declare(strict_types = 1);

namespace Itseasy\Queue\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Exception;

class QueueCommand
{
    protected static $defaultName = "queue";

    public function execute(InputInterface $input, OutputInterface $output) : interface
    {
        return Command::SUCCESS;
    }
}

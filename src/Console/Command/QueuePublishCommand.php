<?php

declare(strict_types = 1);

namespace Itseasy\Queue\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Itseasy\Queue\Service\QueueService;
use Exception;

class QueuePublishCommand extends Command
{
    protected static $defaultName = "queue:publish";
    protected $queueService;

    public function __construct(QueueService $queueService)
    {
        parent::__construct();
        $this->queueService = $queueService;
    }

    protected function configure() : void
    {
    }

    public function execute(InputInterface $input, OutputInterface $output) : int
    {
        return Command::SUCCESS;
    }
}

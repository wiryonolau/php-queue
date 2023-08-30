<?php

declare(strict_types=1);

namespace Itseasy\Queue\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Itseasy\Queue\Service\QueueService;
use Laminas\Log\LoggerAwareInterface;
use Laminas\Log\LoggerAwareTrait;
use Exception;

class QueueConsumeCommand extends Command implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected static $defaultName = "queue:consume";
    protected $queueService;

    public function __construct(QueueService $queueService)
    {
        parent::__construct();
        $this->queueService = $queueService;
    }

    protected function configure(): void
    {
        $this->addOption(
            "queue",
            null,
            InputOption::VALUE_OPTIONAL,
            "Queue to consume"
        );
        $this->addOption(
            "exchange",
            null,
            InputOption::VALUE_OPTIONAL,
            "Exchange to use"
        );
        $this->addOption(
            "timeout",
            "t",
            InputOption::VALUE_OPTIONAL,
            "Listen timeout in second"
        );
        $this->addOption(
            "option",
            "opt",
            InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
            "Consume option key=val, pass the option multiple time for multiple option"
        );
    }

    public function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        try {
            $queue = $input->getOption("queue");
            $exchange = $input->getOption("exchange");
            $opts = $input->getOption("option");
            $timeout = $input->getOption("timeout");

            if (is_null($queue) or !$queue) {
                $queue = "default";
            }

            if (is_null($timeout) or !$timeout) {
                $timeout = 0;
            } else {
                $timeout = intval($timeout);
            }

            $message_options = [];
            foreach ($opts as $opt) {
                list($k, $v) = explode("=", $opt);
                $message_options[$k] = $v;
            }

            $output->writeln("Consuming " . $queue);

            $this->queueService->consume(
                $queue,
                $exchange,
                $message_options,
                $timeout,
                null,
            );
            return Command::SUCCESS;
        } catch (Exception $e) {
            $this->logger->debug($e->getMessage());
            $output->writeln($e->getMessage());
            return Command::FAILURE;
        }
    }
}

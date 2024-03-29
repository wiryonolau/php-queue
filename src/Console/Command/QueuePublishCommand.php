<?php

declare(strict_types=1);

namespace Itseasy\Queue\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Itseasy\Queue\Service\QueueService;
use Itseasy\Queue\Message\ServiceMessage;
use Laminas\Log\LoggerAwareInterface;
use Laminas\Log\LoggerAwareTrait;
use Exception;

class QueuePublishCommand extends Command implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected static $defaultName = "queue:publish";
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
            "Queue to publish"
        );
        $this->addOption(
            "exchange",
            null,
            InputOption::VALUE_OPTIONAL,
            "Exchange to use"
        );
        $this->addOption(
            "service",
            null,
            InputOption::VALUE_REQUIRED,
            "Service name"
        );
        $this->addOption(
            "method",
            null,
            InputOption::VALUE_OPTIONAL,
            "Method name"
        );
        $this->addOption(
            "argument",
            "arg",
            InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
            "Method arguments key=value, pass the option multiple time for multiple argument"
        );
        $this->addOption(
            "count",
            null,
            InputOption::VALUE_OPTIONAL,
            "Publish same message multiple time, default to 1"
        );
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $queue = $input->getOption("queue");
            $exchange = $input->getOption("exchange");
            $service = $input->getOption("service");
            $method = $input->getOption("method");
            $args = $input->getOption("argument");
            $count = $input->getOption("count");

            if (is_null($queue) or !$queue) {
                $queue = "default";
            }

            if (is_null($service) or !$service) {
                throw new Exception("Service must be defined");
            }

            $arguments = [];
            foreach ($args as $arg) {
                list($k, $v) = explode("=", $arg);
                $arguments[$k] = $v;
            }

            if (is_null($count) or !$count) {
                $count = 1;
            } else {
                $count = intval($count);
            }

            $message = new ServiceMessage(
                $service,
                $method,
                $arguments
            );

            $output->writeln("Publish to " . $queue);

            for ($i = 0; $i < $count; $i++) {
                $this->queueService->publish(
                    $queue,
                    $message->getAMQPMessage(),
                    [
                        "exchange" => $exchange
                    ],
                    null,
                );
            }

            return Command::SUCCESS;
        } catch (Exception $e) {
            $this->logger->debug($e->getMessage());
            $output->writeln($e->getMessage());
            return Command::FAILURE;
        }
    }
}

<?php

declare(strict_types = 1);

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

    protected function configure() : void
    {
        $this->addOption(
            "queue",
            null,
            InputOption::VALUE_OPTIONAL,
            "Queue to consume"
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
            "qoption",
            "qopt",
            InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
            "Queue option key=val, pass the option multiple time for multiple option"
        );
    }

    public function execute(InputInterface $input, OutputInterface $output) : int
    {
        try {
            $queue = $input->getOption("queue");
            $service = $input->getOption("service");
            $method = $input->getOption("method");
            $args = $input->getOption("argument");
            $qopts = $input->getOption("qoption");

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

            $qoptions = [
                "queue" => $queue
            ];
            foreach ($qopts as $qopt) {
                list($k, $v) = explode("=", $qopt);
                $qoptions[$k] = $v;
            }

            $message = new ServiceMessage(
                $service,
                $method,
                $arguments
            );

            $this->logger->info("Publish to ".$queue);
            $output->writeln("Publish to ".$queue);

            $this->queueService->create($qoptions);
            $this->queueService->publish($queue, $message->getAMQPMessage());
            return Command::SUCCESS;
        } catch (Exception $e) {
            $this->logger->debug($e->getMessage());
            $output->writeln($e->getMessage());
            return Command::FAILURE;
        }
    }
}

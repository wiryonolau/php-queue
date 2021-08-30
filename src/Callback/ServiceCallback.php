<?php

namespace Itseasy\Queue\Callback;

use Psr\Container\ContainerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Itseasy\Queue\Message\ServiceMessage;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Exception;

class ServiceCallback implements QueueCallbackInterface
{
    private $container;
    private $output = null;

    public function __construct(ContainerInterface $container, ?ConsoleOutputInterface $output = null) {
        $this->container = $container;
        $this->output = $output;
    }

    public function __invoke(AMQPMessage $message) {
        try {
            $serviceMessage = ServiceMessage::decode($message->body);
            $this->writeln("Receive ".$serviceMessage);
            $serviceMessage->run($this->container);
        } catch (Exception $e) {
            $this->writeln($e->getMessage());
        } finally {
            $this->writeln("Done");
            $message->ack();
        }
    }

    private function writeln(string $message) {
        if (is_null($this->output)) {
            echo $message;
        } else {
            $this->output->writeln($message);
        }

    }
}

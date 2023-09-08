<?php

namespace Itseasy\Queue\Callback;

use Psr\Container\ContainerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Itseasy\Queue\Message\ServiceMessage;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Laminas\Log\LoggerAwareInterface;
use Laminas\Log\LoggerAwareTrait;
use Exception;
use Throwable;

class EchoCallback implements QueueCallbackInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private $container;

    public function __invoke(AMQPMessage $message)
    {
        try {
            $this->logger->info("Receive " . $message->body);
        } catch (Throwable $t) {
            $this->logger->debug($t->getMessage());
        } finally {
            $this->logger->info("Done");
            $message->ack();
        }
    }
}

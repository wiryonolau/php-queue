<?php

namespace Itseasy\Queue\Callback;

use PhpAmqpLib\Message\AMQPMessage;
use Laminas\Log\LoggerAwareInterface;
use Laminas\Log\LoggerAwareTrait;
use Throwable;

class EchoCallback implements QueueCallbackInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private $container;

    public function __invoke(AMQPMessage $message)
    {
        try {
            $this->logger->info("Receive " . $message->getBody());
        } catch (Throwable $t) {
            $this->logger->debug($t->getMessage());
        } finally {
            $this->logger->info("Done");
            $message->ack();
        }
    }
}

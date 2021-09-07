<?php

namespace Itseasy\Queue\Callback;

use Psr\Container\ContainerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Itseasy\Queue\Message\ServiceMessage;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Laminas\Log\LoggerAwareInterface;
use Laminas\Log\LoggerAwareTrait;
use Exception;

class ServiceCallback implements QueueCallbackInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(AMQPMessage $message)
    {
        try {
            $serviceMessage = ServiceMessage::decode($message->body);
            $serviceMessage->setLogger($this->getLogger());
            $this->logger->info("Receive ".$serviceMessage);
            $serviceMessage->run($this->container);
        } catch (Exception $e) {
            $this->logger->debug($e->getMessage());
        } finally {
            $this->logger->info("Done");
            $message->ack();
        }
    }
}

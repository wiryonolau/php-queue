<?php

namespace Itseasy\Queue\Callback;

use Psr\Container\ContainerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Itseasy\Queue\Message\ServiceMessage;
use Exception;

class ServiceCallback implements QueueCallbackInterface
{
    private $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function __invoke(AMQPMessage $message) {
        try {
            $serviceMessage = ServiceMessage::decode($message->body);
            $serviceMessage->run($this->container);
        } catch (Exception $e) {
            echo $e->getMessage();
        } finally {
            $message->ack();
        }
    }
}

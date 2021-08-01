<?php

namespace Itseasy\Queue\Callback;

use Psr\Container\ContainerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Itseasy\Queue\Message\ServiceMessage;

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
            $message->ack();
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}

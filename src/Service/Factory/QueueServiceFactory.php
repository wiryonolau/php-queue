<?php
declare(strict_types = 1);

namespace Itseasy\Queue\Service\Factory;

use Itseasy\Queue\Service\QueueService;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Psr\Container\ContainerInterface;
use Itseasy\Queue\Message\ServiceMessage;
use PhpAmqpLib\Exception\AMQPIOException;

class QueueServiceFactory
{
    public function __invoke(ContainerInterface $container) : QueueService
    {
        $queue_config = $container->get("Config")->getConfig()["queue"];

        try {
            $connection = AMQPStreamConnection::create_connection($queue_config["hosts"], $queue_config["options"]);
            $connection->set_close_on_destruct($queue_config["set_close_on_destruct"]);

            $channel = $connection->channel();
            $callback = $container->get($queue_config["callback"]);
        } catch (AMQPIOException $e) {
            $channel = null;
            $callback = null;
        }

        return new QueueService($channel, $callback);
    }
}

<?php
declare(strict_types = 1);

namespace Itseasy\Queue\Service\Factory;

use Itseasy\Queue\Service\QueueService;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Psr\Container\ContainerInterface;
use Itseasy\Queue\Message\ServiceMessage;

class QueueServiceFactory
{
    public function __invoke(ContainerInterface $container) : QueueService
    {
        $queue_config = $container->get("Config")->getConfig()["queue"];

        $connection = AMQPStreamConnection::create_connection($queue_config["hosts"], $queue_config["options"]);
        $connection->set_close_on_destruct($queue_config["set_close_on_destruct"]);

        $channel = $connection->channel();
        
        foreach ($queue_config["queue"] as $q) {
            call_user_func_array([$channel, "queue_declare"], $q);
        }

        $consume_callback = function($message) use ($container) {
            $serviceMessage = ServiceMessage::decode($message->body);
            $serviceMessage->run($container);
            $message->ack();
        };

        return new QueueService($channel, $consume_callback);
    }
}

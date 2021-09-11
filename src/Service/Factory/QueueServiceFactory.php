<?php
declare(strict_types = 1);

namespace Itseasy\Queue\Service\Factory;

use Exception;
use Itseasy\Queue\Logger\DefaultLogger;
use Itseasy\Queue\Message\ServiceMessage;
use Itseasy\Queue\Service\QueueService;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPIOException;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use Psr\Container\ContainerInterface;

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
        } catch (AMQPIOException $ampqe) {
            $channel = null;
            $callback = null;
        } catch (Exception $e) {
            $channel = null;
            $callback = null;
        }

        $queueService = new QueueService($channel, $callback);
        $queueService->setLogger($container->get(DefaultLogger::class));

        foreach ($queue_config["channels"] as $channel_config) {
            $queueService->create($channel_config);
        }

        return $queueService;
    }
}

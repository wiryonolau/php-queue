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
        $callback = $container->get($queue_config["callback"]);

        if ($container->has("Logger")) {
            $logger = $container->get("Logger");
        } else {
            $logger = $container->get(DefaultLogger::class);
        }

        $connection = null;

        try {
            $connection = AMQPStreamConnection::create_connection(
                $queue_config["hosts"],
                $queue_config["options"]
            );
            $connection->set_close_on_destruct($queue_config["set_close_on_destruct"]);
        } catch (AMQPIOException $ampqe) {
            $logger->debug(sprintf(
                "Server not ready - %s",
                $ampqe->getMessage()
            ));
        } catch (Exception $e) {
            $logger->debug(sprintf(
                "Server not ready - %s",
                $e->getMessage()
            ));
        }

        $queueService = new QueueService($connection, $callback);
        $queueService->setLogger($logger);

        foreach ($queue_config["channels"] as $channel_config) {
            $queueService->create($channel_config);
        }

        return $queueService;
    }
}

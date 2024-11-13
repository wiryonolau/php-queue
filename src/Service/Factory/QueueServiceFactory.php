<?php

declare(strict_types=1);

namespace Itseasy\Queue\Service\Factory;

use Exception;
// use Itseasy\Queue\Connection\AMQPConnectionFactory;
use Itseasy\Queue\Logger\DefaultLogger;
use Itseasy\Queue\Service\QueueService;
use PhpAmqpLib\Connection\AMQPConnectionConfig;
use PhpAmqpLib\Connection\AMQPConnectionFactory;
use PhpAmqpLib\Exception\AMQPIOException;
use Psr\Container\ContainerInterface;

class QueueServiceFactory
{
    public function __invoke(ContainerInterface $container): QueueService
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
            // New version single host only
            $server_config = $queue_config["hosts"]["default"];

            $amqpConfig = new AMQPConnectionConfig();
            $amqpConfig->setHost($server_config["host"]);
            $amqpConfig->setPort($server_config["port"]);
            $amqpConfig->setUser($server_config["user"] ?? "guest");
            $amqpConfig->setPassword($server_config["password"] ?? "guest");

            if ($server_config["issecure"] ?? false) {
                $amqpConfig->setIsSecure(true);

                // This is path not content
                $amqpConfig->setSslCert($server_config["sslcert"] ?? null);
                $amqpConfig->setSslKey($server_config["sslkey"] ?? null);
                $amqpConfig->setSslCaCert($server_config["sslcacert"] ?? null);
                $amqpConfig->setSslCaPath($server_config["sslcapath"] ?? null);

                $amqpConfig->setSslVerify($server_config["sslverify"] ?? true);
                $amqpConfig->setSslVerifyName($server_config["sslverifyname"] ?? true);
            }
            $amqpConfig->setIoType($queue_config["options"]["io_type"] ?? AMQPConnectionConfig::IO_TYPE_STREAM);
            $amqpConfig->setVhost($queue_config["options"]["vhost"] ?? "/");

            // Always use lazy connection to prevent creation when not used
            $amqpConfig->setIsLazy(true);

            $connection = AMQPConnectionFactory::create($amqpConfig);

            $connection->set_close_on_destruct(
                $queue_config["set_close_on_destruct"]
            );
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

        $queueService = new QueueService(
            $connection,
            $callback,
            $queue_config["channels"]
        );

        $queueService->setLogger($logger);

        return $queueService;
    }
}

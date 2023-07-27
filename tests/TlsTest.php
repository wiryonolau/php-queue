<?php

namespace Itseasy\Queue\Test;

use PHPUnit\Framework\TestCase;
use Itseasy\Queue\Service\QueueService;
use PhpAmqpLib\Message\AMQPMessage;

final class TlsTest extends TestCase
{
    public function testTlsInsecureConnection()
    {
        if (!get_env("TEST_TLS", false)) {
            $this->markTestSkipped("Testing TLS connection skipped");
        }

        $app = new Application([
            "config_path" => [
                __DIR__ . "/../config/*.config.php",
                __DIR__ . "/config/tls.insecure.config.php",
                __DIR__ . "/config/service.config.php",
            ],
        ]);
        $app->build();

        $container = $app->getContainer();
        $queueService = $container->get(QueueService::class);

        $queueService->publish(
            "default",
            new AMQPMessage("test"),
            [
                "exchange" => "transaction"
            ]
        );

        // App build will failed if tls error
        $this->assertEquals(true, true);
    }

    public function testTlsConnection()
    {
        if (!get_env("TEST_TLS", false)) {
            $this->markTestSkipped("Testing TLS connection skipped");
        }

        $app = new Application([
            "config_path" => [
                __DIR__ . "/../config/*.config.php",
                __DIR__ . "/config/tls.config.php",
                __DIR__ . "/config/service.config.php",
            ],
        ]);
        $app->build();

        $container = $app->getContainer();
        $queueService = $container->get(QueueService::class);

        $queueService->publish(
            "default",
            new AMQPMessage("test"),
            [
                "exchange" => "transaction"
            ]
        );

        // App build will failed if tls error
        $this->assertEquals(true, true);
    }
}

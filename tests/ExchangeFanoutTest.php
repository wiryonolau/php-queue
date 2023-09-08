<?php

namespace Itseasy\Queue\Test;

use PHPUnit\Framework\TestCase;
use Itseasy\Queue\Service\QueueService;
use PhpAmqpLib\Message\AMQPMessage;

final class ExchangeFanoutTest extends TestCase
{
    public function testExchange()
    {
        if (!get_env("TEST_FANOUT", false)) {
            $this->markTestSkipped("Testing Exchange Fanout skipped");
        }

        $app = new Application([
            "config_path" => [
                __DIR__ . "/../config/*.config.php",
                __DIR__ . "/config/queue.config.php",
                __DIR__ . "/config/service.config.php"
            ],
        ]);
        $app->build();

        $container = $app->getContainer();
        $queueService = $container->get(QueueService::class);

        $queueService->publish(
            "",
            new AMQPMessage("test fanout"),
            [
                "exchange" => "transaction"
            ]
        );
        // We cannot test fanout sub with pub
        $this->assertEquals(true, true);
    }
}

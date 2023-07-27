<?php

namespace Itseasy\Queue\Test;

use PHPUnit\Framework\TestCase;
use Itseasy\Queue\Service\QueueService;
use Itseasy\Queue\Message\ServiceMessage;
use PhpAmqpLib\Exception\AMQPTimeoutException;

final class ServiceQueueTest extends TestCase
{
    public function testQueue()
    {
        if (!get_env("TEST_SERVICE_QUEUE", false)) {
            $this->markTestSkipped("Testing Queue skipped");
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

        $messages = [
            ["method" => "test", "text" => "this is the text"],
            ["method" => "testThrowable", "text" => ""],
            ["method" => "testNotDefinedArray", "text" => ""],
            ["method" => "testNotArray", "text" => ""],
            ["method" => "test", "text" => "another text"],
        ];

        foreach ($messages as $method => $message) {
            $serviceMessage = new ServiceMessage(
                Service\TestService::class,
                $message["method"],
                [$message["text"]]
            );
            $queueService->publish(
                "default",
                $serviceMessage->getAMQPMessage()
            );
        }

        // give time to publish before consume directly
        sleep(5);

        $result = array_map(function ($msg) {
            return trim($msg["text"]);
        }, $messages);

        debug($result);

        $this->expectOutputString(implode("", $result));
        $queueService->consume("default", "", [], 10);
    }
}

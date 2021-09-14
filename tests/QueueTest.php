<?php
namespace Itseasy\Queue\Test;

use PHPUnit\Framework\TestCase;
use Itseasy\Queue\Service\QueueService;
use Itseasy\Queue\Message\ServiceMessage;
use PhpAmqpLib\Exception\AMQPTimeoutException;

final class QueueTest extends TestCase
{
    public function testQueue()
    {
        // Test will timeout
        $this->expectException(AMQPTimeoutException::class);

        $app = new Application([
            "config_path" => [
                __DIR__."/../config/*.config.php",
                __DIR__."/config/*.config.php"
            ],
        ]);
        $app->build();

        $container = $app->getContainer();
        $queueService = $container->get(QueueService::class);


        $messages = [
            ["method" => "test", "text" => "this is the text\n"],
            ["method" => "test", "text" => "another text\n"],
            ["method" => "testThrowable", "text" => "last text\n"]
        ];

        foreach ($messages as $method => $message) {
            $serviceMessage = new ServiceMessage(Service\TestService::class, $message["method"], [$message["text"]]);
            $queueService->publish("default", $serviceMessage->getAMQPMessage());
        }

        // give time to publish before consume directly
        sleep(5);

        $result = array_map(function($msg) {
            if ($msg["method"] != "test") {
                return "";
            } else {
                return $msg["text"];
            }
        }, $messages);

        $this->expectOutputString(implode("", $result));
        $queueService->consume("default", [], true, 10);

    }
}

<?php
namespace Itseasy\Queue\Test;

use PHPUnit\Framework\TestCase;
use Itseasy\Queue\Service\QueueService;
use Itseasy\Queue\Message\ServiceMessage;

final class QueueTest extends TestCase
{
    public function testQueue()
    {
        $app = new Application([
            "config_path" => [
                __DIR__."/../config/*.config.php",
                __DIR__."/config/*.config.php"
            ]
        ]);
        $app->build();

        $container = $app->getContainer();
        $queueService = $container->get(QueueService::class);

        $text = "this is the text";
        $serviceMessage = new ServiceMessage(Service\TestService::class, "test", [$text]);
        
        $queueService->publish($queueService::createMessage($serviceMessage));
        sleep(5);
        $queueService->consume("default", [], 10);
        $this->expectOutputString($text);
    }
}
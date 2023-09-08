<?php

namespace Itseasy\Queue;

use Laminas\ServiceManager\Factory\InvokableFactory;
use PhpParser\Node\Expr\CallLike;

return [
    "service" => [
        "factories" => [
            Service\QueueService::class => Service\Factory\QueueServiceFactory::class,
            Callback\ServiceCallback::class => Callback\Factory\ServiceCallbackFactory::class,
            Callback\EchoCallback::class => InvokableFactory::class,
            Logger\DefaultLogger::class => Logger\Factory\DefaultLoggerFactory::class
        ]
    ]
];

<?php

namespace Itseasy\Queue;

return [
    "service" => [
        "factories" => [
            Service\QueueService::class => Service\Factory\QueueServiceFactory::class
        ]
    ]
];

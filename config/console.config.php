<?php

namespace Itseasy\Queue\Console;

use DI;

return [
    "console" => [
        "commands" => [
            Command\QueuePublishCommand::class
        ],
        "factories" => [
            Command\QueuePublishCommand::class => Command\Factory\QueuePublishCommandFactory::class
        ]
    ]
];

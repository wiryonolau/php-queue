<?php

namespace Itseasy\Queue\Console;

use DI;

return [
    "console" => [
        "commands" => [
            Command\QueuePublishCommand::class,
            Command\QueueConsumeCommand::class
        ],
        "factories" => [
            Command\QueuePublishCommand::class => Command\Factory\QueuePublishCommandFactory::class,
            Command\QueueConsumeCommand::class => Command\Factory\QueueConsumeCommandFactory::class
        ]
    ]
];

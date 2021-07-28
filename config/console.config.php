<?php

namespace Itseasy;

use DI;

return [
    "console" => [
        "commands" => [
            Queue\Console\Command\QueueCommand::class
        ],
        "factories" => [
            Queue\Console\Command\QueueCommand::class => DI\create()
        ]
    ]
]

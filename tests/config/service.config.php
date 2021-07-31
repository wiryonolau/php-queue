<?php

namespace Itseasy\Queue\Test;

use DI;

return [
    "service" => [
        "factories" => [
            Service\TestService::class => DI\create()
        ]
    ]
];

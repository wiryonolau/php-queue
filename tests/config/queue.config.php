<?php

namespace Itseasy\Queue;

return [
    "queue" => [
        "hosts" => [
            [
                "host" => getenv("RABBITMQ_SERVER") ? :"localhost",
                "port" => getenv("RABBITMQ_PORT") ? : 5672,
                "user" => getenv("RABBITMQ_USER") ? : "guest",
                "password" => getenv("RABBITMQ_PASSWORD") ? : "guest"
            ]
        ],
        "options" => [
        ],
        "callback" => Callback\ServiceCallback::class,
        "set_close_on_destruct" => false
    ]
];

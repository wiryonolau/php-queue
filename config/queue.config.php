<?php

return [
    "queue" => [
        "hosts" => [
            [
                "host" => getenv("RABBITMQ_SERVER") ? :"localhost",
                "port" => getenv("RABBITMQ_PORT") ? : 5672,
                "user" => "guest",
                "password" => "guest"
            ]
        ],
        "options" => [
        ],
        "queue" => [
            [
                "queue" => "default" ,
                "passive" => false,
                "durable" => false,
                "exclusive" => false,
                "auto_delete" => true,
                "nowait" => false,
                "arguments"=> [],
                "ticket" => null
            ]
        ],
        "set_close_on_destruct" => false
    ]
];

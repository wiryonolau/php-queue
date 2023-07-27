<?php

namespace Itseasy\Queue;

use PhpAmqpLib\Exchange\AMQPExchangeType;

return [
    "queue" => [
        "hosts" => [
            [
                "host" => "localhost",
                "port" => 5672,
                "user" => "guest",
                "password" => "guest"
            ]
        ],
        "channels" => [
            [
                "exchange" => [
                    "exchange" => "transaction",
                    "type" => AMQPExchangeType::FANOUT,
                    "passive" => false,
                    "durable" => true,
                    "auto_delete" => false,
                    "internal" => false,
                    "arguments" => [],
                    "ticket" => null
                ]
            ]
        ],
        "options" => [],
        "callback" => Callback\ServiceCallback::class,
        "set_close_on_destruct" => false
    ]
];

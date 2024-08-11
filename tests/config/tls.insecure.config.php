<?php

namespace Itseasy\Queue;

use PhpAmqpLib\Exchange\AMQPExchangeType;

return [
    "queue" => [
        "hosts" => [
            [
                "host" => "localhost",
                "port" => 15671,
                "user" => "principal",
                "password" => "principal",
                "issecure" => true,
                "sslverify" => false,
                "sslverifyname" => false,
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
    ]
];

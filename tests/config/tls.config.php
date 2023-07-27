<?php

namespace Itseasy\Queue;

use PhpAmqpLib\Exchange\AMQPExchangeType;

return [
    "queue" => [
        "hosts" => [
            [
                "host" => "localhost",
                "port" => 5671,
                "user" => "principal",
                "password" => "principal",
                "issecure" => true,
                "sslcacert" => APP_DIR . "/etc/rabbitmq/cert/rabbitmq.ca.crt",
                "sslcert" => APP_DIR . "/etc/rabbitmq/cert/rabbitmq.crt",
                "sslkey" => APP_DIR . "/etc/rabbitmq/cert/rabbitmq.key",
                "sslverify" => true,
                "sslverifyname" => true,
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

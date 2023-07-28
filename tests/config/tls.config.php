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
                "sslcacert" => APP_DIR . "/etc/rabbitmq/cert/ca_certificate.pem",
                "sslcert" => APP_DIR . "/etc/rabbitmq/cert/client_certificate.pem",
                "sslkey" => APP_DIR . "/etc/rabbitmq/cert/client_key.pem",
                "sslverify" => true,
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

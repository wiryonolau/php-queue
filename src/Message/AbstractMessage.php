<?php

namespace Itseasy\Queue\Message;

use PhpAmqpLib\Message\AMQPMessage;
use Laminas\Stdlib\ArrayUtils;

abstract class AbstractMessage
{
    abstract function encode() : string;
    abstract static function decode(string $value);

    public function getAMQPMessage(array $options = []) : AMQPMessage
    {
        $default = [
            "delivery_mode" => 2
        ];
        $options = ArrayUtils::merge($default, $options);
        return new AMQPMessage($this->encode(), $options);
    }
}

<?php

namespace Itseasy\Queue\Message;

use PhpAmqpLib\Message\AMQPMessage;
use Laminas\Stdlib\ArrayUtils;
use Laminas\Log\LoggerAwareInterface;
use Laminas\Log\LoggerAwareTrait;

abstract class AbstractMessage implements LoggerAwareInterface
{
    abstract function encode() : string;
    abstract static function decode(string $value);

    use LoggerAwareTrait;

    public function getAMQPMessage(array $options = []) : AMQPMessage
    {
        $default = [
            "delivery_mode" => 2
        ];
        $options = ArrayUtils::merge($default, $options);
        return new AMQPMessage($this->encode(), $options);
    }
}

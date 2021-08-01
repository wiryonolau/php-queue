<?php

namespace Itseasy\Queue\Service;

use Itseasy\Queue\Message\ServiceMessage;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use Laminas\Stdlib\ArrayUtils;

class QueueService
{
    protected $channel;
    protected $queue;
    protected $callback;

    public function __construct(AMQPChannel $channel, $callback = null)
    {
        $this->channel = $channel;
        $this->callback = $callback;
    }

    public function create(array $options = []) : void
    {
        $default = [
            "queue" => "default" ,
            "passive" => false,
            "durable" => false,
            "exclusive" => false,
            "auto_delete" => true,
            "nowait" => false,
            "arguments"=> [],
            "ticket" => null
        ];

        $options = ArrayUtils::merge($default, $options);
        call_user_func_array([$this->channel, "queue_declare"], $options);
    }

    public function publish(string $queue_name = "default", AMQPMessage $message, array $message_options = [])
    {
        if ($this->channel->getConnection()->isConnected() === false) {
            $this->channel->getConnection()->connect();
        }

        $default = [
            "message" => $message,
            "exchange" => "",
            "routing_key" => $queue_name,
            "mandatory" => false,
            "immediate" => false,
            "ticket" => null
        ];

        $message_options = ArrayUtils::merge($default, $message_options);
        call_user_func_array([$this->channel, "basic_publish"], $message_options);
    }

    public function consume(string $queue_name = "default", array $options = [], $timeout=0) {
        if ($this->channel->getConnection()->isConnected() === false) {
            $this->channel->getConnection()->connect();
        }

        $default = [
            "queue" => $queue_name,
            "consumer_tag" => "",
            "no_local" => false,
            "no_ack" => false,
            "exclusive" => false,
            "nowait"=> false,
            "callback" => $this->callback,
            "ticket" =>  null,
            "arguments" => []
        ];

        $options = ArrayUtils::merge($default, $options);

        $this->channel->basic_qos(null, 1, null);
        call_user_func_array([$this->channel, "basic_consume"], $options);
        $this->channel->wait(null, false, $timeout);
    }

    public function close() {
        $this->channel->close();
        $this->channel->getConnection()->close();
    }
}

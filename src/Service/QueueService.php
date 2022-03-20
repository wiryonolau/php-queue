<?php

namespace Itseasy\Queue\Service;

use Itseasy\Queue\Message\ServiceMessage;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Connection\AbstractConnection;
use Laminas\Stdlib\ArrayUtils;
use Laminas\Log\LoggerAwareInterface;
use Laminas\Log\LoggerAwareTrait;
use Exception;
use Throwable;

class QueueService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected $connection;
    protected $callback;

    public function __construct(
        ?AbstractConnection $connection = null,
        $callback = null
    ) {
        $this->connection = $connection;
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

        try {
            call_user_func_array([$this->connection->channel(),
            "queue_declare"], $options);
        } catch (Throwable $t) {
            $this->logger->debug(sprintf(
                "Creating queue channel \"%s\" failed",
                $options["queue"]
            ));
        }
    }

    public function publish(
        string $queue_name = "default",
        AMQPMessage $message,
        array $message_options = []
    ) : bool {
        try {
            if ($this->connection->isConnected() === false) {
                $this->connection->connect();
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
            call_user_func_array(
                [$this->connection->channel(), "basic_publish"],
                $message_options
            );

            $this->logger->info(sprintf(
                "Message publish to exchange : %s, queue : %s",
                $message_options["exchange"] == "" ? "null" : $message_options["exchange"],
                $message_options["routing_key"]
            ));

            return true;
        } catch (Throwable $t) {
            $this->logger->debug(sprintf("Publish message failed"));
            $this->logger->debug(sprintf($t->getMessage()));
            return false;
        }
    }

    // timeout 0 equal forever
    public function consume(
        string $queue_name = "default",
        array $options = [],
        int $timeout = 0
    ) : void {
        try {
            if ($this->connection->isConnected() === false) {
                $this->connection->connect();
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

            $channel = $this->connection->channel();
            $channel->basic_qos(null, 1, null);

            $this->logger->info(sprintf(
                "Consume queue : %s, no_local : %s, no_ack : %s, exclusive : %s, nowait : %s, timeout : %s",
                $options["queue"] ? 1 : 0,
                $options["no_local"] ? 1 : 0,
                $options["no_ack"] ? 1 : 0,
                $options["exclusive"] ? 1 : 0,
                $options["nowait"] ? 1 : 0,
                $timeout
            ));

            call_user_func_array([$channel, "basic_consume"], $options);

            while ($channel->is_open()) {
                // Will throw exception on timeout > 0 and break while loop
                // TODO: Allow non blocking
                $channel->wait(null, false, $timeout);
            }
        } catch (Throwable $t) {
            $this->logger->debug(sprintf("Consumer failed to start"));
            $this->logger->debug(sprintf($t->getMessage()));
        }
    }

    public function close()
    {
        $this->connection->close();
    }
}

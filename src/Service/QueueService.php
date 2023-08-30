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
use PhpAmqpLib\Exchange\AMQPExchangeType;
use Throwable;

class QueueService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected $connection;
    protected $callback;
    protected $channel_id;
    protected $channel_configs = [];

    public function __construct(
        ?AbstractConnection $connection = null,
        $callback = null,
        $channel_configs = []
    ) {
        $this->connection = $connection;
        $this->callback = $callback;
        $this->channel_configs = $channel_configs;

        $this->channel_id = null;
    }

    public function publish(
        string $queue_name = "default",
        ?AMQPMessage $message = null,
        array $message_options = [],
        string $channel_name = null
    ): bool {
        if (empty($message)) return false;

        try {
            $channel = $this->declareChannel($channel_name);

            // Parameter order is fixed according to basic_publish
            $default = [
                "msg" => $message,
                "exchange" => "",
                "routing_key" => $queue_name,
                "mandatory" => false,
                "immediate" => false,
                "ticket" => null,
            ];

            $message_options = ArrayUtils::merge(
                $default,
                $message_options
            );

            call_user_func_array(
                [$channel, "basic_publish"],
                $message_options
            );

            $this->logger->info(sprintf(
                "Message publish to exchange : %s, queue : %s",
                empty($message_options["exchange"]) ? "" : $message_options["exchange"],
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
        string $exchange_name = "",
        array $queue_options = [],
        int $timeout = 0,
        string $channel_name = null,
        $callback = null
    ): void {
        try {
            $channel = $this->declareChannel($channel_name);

            if (!empty($exchange_name)) {
                $channel->queue_bind($queue_name, $exchange_name);
            }
            $channel->basic_qos(null, 1, null);

            // Parameter order is fixed according to basic_consume
            $default_queue = [
                "queue" => $queue_name,
                "consumer_tag" => "",
                "no_local" => false,
                "no_ack" => false,
                "exclusive" => false,
                "nowait" => false,
                "callback" => $callback ?? $this->callback,
                "ticket" =>  null,
                "arguments" => [],
            ];

            $queue_options = ArrayUtils::merge(
                $default_queue,
                $queue_options
            );


            $this->logger->info(sprintf(
                "Consume queue : %s, Exchange: %s, no_local : %d, no_ack : %d, exclusive : %d, nowait : %d, timeout : %d",
                $queue_name,
                $exchange_name ?? "",
                $queue_options["no_local"] ? 1 : 0,
                $queue_options["no_ack"] ? 1 : 0,
                $queue_options["exclusive"] ? 1 : 0,
                $queue_options["nowait"] ? 1 : 0,
                $timeout
            ));

            call_user_func_array(
                [$channel, "basic_consume"],
                $queue_options
            );

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

    private function declareQueue(
        AMQPChannel $channel,
        array $options = []
    ): void {
        $default = [
            "queue" => "default",
            "passive" => false,
            "durable" => true,
            "exclusive" => false,
            "auto_delete" => true,
            "nowait" => false,
            "arguments" => [],
            "ticket" => null
        ];

        $queue_options = ArrayUtils::merge(
            $default,
            $options
        );

        try {


            call_user_func_array([
                $channel,
                "queue_declare"
            ], $queue_options);
        } catch (Throwable $t) {
            $this->logger->debug($t->getMessage());
            $this->logger->debug(sprintf(
                "Declaring queue channel \"%s\" failed",
                $queue_options["queue"]
            ));
        }
    }

    private function declareExchange(
        AMQPChannel $channel,
        array $options
    ): void {
        $default = [
            "exchange" => "",
            "type" => AMQPExchangeType::DIRECT,
            "passive" => false,
            "durable" => true,
            "auto_delete" => true,
            "internal" => false,
            "arguments" => [],
            "ticket" => null
        ];

        $options = ArrayUtils::merge(
            $default,
            $options
        );

        try {
            $channel = $this->connection->channel($this->channel_id);
            if (!empty($options["exchange"])) {
                call_user_func_array([
                    $channel,
                    "exchange_declare"
                ], $options);
            }
        } catch (Throwable $t) {
            $this->logger->debug($t->getMessage());
            $this->logger->debug(sprintf(
                "Declaring exchange \"%s\" failed",
                $options["exchange"]
            ));
        }
    }

    /**
     * @param $channel_name string Load only this channel, for web request
     */
    private function declareChannel(
        ?string $channel_name = null
    ): AMQPChannel {
        $this->channel_id =  $this->connection->get_free_channel_id();
        $channel = $this->connection->channel($this->channel_id);

        if (!empty($channel_name)) {
            $channel_configs = array_filter(
                $this->channel_configs,
                function ($k) use ($channel_name) {
                    return $k == $channel_name;
                },
                ARRAY_FILTER_USE_KEY
            );
        } else {
            $channel_configs = $this->channel_configs;
        }

        foreach ($channel_configs as $config) {
            $channel_queue_config = [];
            $channel_exchange_config = [];

            if (empty($config["queue"])) {
                $channel_queue_config =  [
                    "queue" => "default",
                    "passive" => false,
                    "durable" => false,
                    "exclusive" => false,
                    "auto_delete" => true,
                    "nowait" => false,
                    "arguments" => [],
                    "ticket" => null
                ];
            } else if (!is_array($config["queue"])) {
                // old compatible config
                $channel_queue_config = $config;
            }

            $channel_exchange_config = $config["exchange"] ?? [];

            $this->declareExchange($channel, $channel_exchange_config);
            $this->declareQueue($channel, $channel_queue_config);
        }

        return $channel;
    }
}

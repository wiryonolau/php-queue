<?php

namespace Itseasy\Queue\Service;

use Laminas\Log\LoggerAwareInterface;
use Laminas\Log\LoggerAwareTrait;
use Laminas\Stdlib\ArrayUtils;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;
use Throwable;

class QueueService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    const OPTIONS_QUEUE = [
        "queue" => "",
        "passive" => false,
        "durable" => true,
        "exclusive" => false,
        "auto_delete" => false,
        "nowait" => false,
        "arguments" => [],
        "ticket" => null
    ];

    const OPTIONS_EXCHANGE = [
        "exchange" => "",
        "type" => AMQPExchangeType::DIRECT,
        "passive" => false,
        "durable" => true,
        "auto_delete" => true,
        "internal" => false,
        "arguments" => [],
        "ticket" => null
    ];

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
        string $queue_name = "",
        ?AMQPMessage $message = null,
        array $publish_options = []
    ): bool {
        if (empty($message)) return false;

        try {
            $channel = $this->declareChannel();

            // Parameter order is fixed according to basic_publish
            $publish_options = ArrayUtils::merge(
                [
                    "msg" => $message,
                    "exchange" => "",
                    "routing_key" => $queue_name,
                    "mandatory" => false,
                    "immediate" => false,
                    "ticket" => null,
                ],
                $publish_options
            );

            call_user_func_array(
                [$channel, "basic_publish"],
                $publish_options
            );

            $this->logger->info(sprintf(
                "Message publish to exchange : %s, queue : %s",
                empty($publish_options["exchange"]) ? "" : $publish_options["exchange"],
                empty($publish_options["routing_key"]) ? "random" : $publish_options["routing_key"]
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
        string $queue_name = "",
        string $exchange_name = "",
        array $consume_options = [],
        int $timeout = 0,
        $callback = null
    ): void {
        try {
            $channel = $this->declareChannel();

            $channel->basic_qos(null, 1, null);

            // Parameter order is fixed according to basic_consume
            $consume_options = ArrayUtils::merge(
                [
                    "queue" => $queue_name,
                    "consumer_tag" => "",
                    "no_local" => false,
                    "no_ack" => false,
                    "exclusive" => false,
                    "nowait" => false,
                    "callback" => $callback ?? $this->callback,
                    "ticket" =>  null,
                    "arguments" => [],
                ],
                $consume_options
            );


            $this->logger->info(sprintf(
                "Consume queue : %s, Exchange: %s, no_local : %d, no_ack : %d, exclusive : %d, nowait : %d, timeout : %d",
                empty($queue_name) ? "random" : $queue_name,
                empty($exchange_name) ? "" : $exchange_name,
                $consume_options["no_local"] ? 1 : 0,
                $consume_options["no_ack"] ? 1 : 0,
                $consume_options["exclusive"] ? 1 : 0,
                $consume_options["nowait"] ? 1 : 0,
                $timeout
            ));

            call_user_func_array(
                [$channel, "basic_consume"],
                $consume_options
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
    ): string {
        $queue_options = ArrayUtils::merge(
            self::OPTIONS_QUEUE,
            $options
        );

        try {
            $queue = call_user_func_array([
                $channel,
                "queue_declare"
            ], $queue_options);


            $this->logger->info(sprintf(
                "Declaring queue \"%s\" succeed",
                $queue[0]
            ));

            return $queue[0] ?? "";
        } catch (Throwable $t) {
            $this->logger->debug($t->getMessage());
            $this->logger->debug(sprintf(
                "Declaring queue \"%s\" failed",
                empty($queue_options["queue"]) ? "random" : $queue_options["queue"]
            ));
        }
    }

    private function declareExchange(
        AMQPChannel $channel,
        array $options
    ): void {
        $options = ArrayUtils::merge(
            self::OPTIONS_EXCHANGE,
            $options
        );

        try {
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

    private function bindQueue(
        AMQPChannel $channel,
        ?string $exchange_name = null,
        ?string $queue_name = null,
        ?string $routing_key = null
    ): void {
        try {
            $channel->queue_bind(
                $queue_name ?? "",
                $exchange_name ?? "",
                empty($routing_key) ? $queue_name : $routing_key
            );
        } catch (Throwable $t) {
            $this->logger->debug($t->getMessage());
            $this->logger->debug(sprintf(
                "Binding queue \"%s\" to exchange \"%s\" failed",
                $queue_name,
                $exchange_name
            ));
        }
    }

    /**
     * Declaring queue
     * Note that queue must be unique, a queue cannot be bind to multiple exchange
     */
    private function declareChannel(): AMQPChannel
    {
        $this->channel_id =  $this->connection->get_free_channel_id();
        $channel = $this->connection->channel($this->channel_id);

        foreach ($this->channel_configs as $config) {
            if (empty($config["queue"])) {
                $channel_queue_config = [];
            } else if (!is_array($config["queue"])) {
                // old compatible config
                $channel_queue_config = $config;
            } else {
                $channel_queue_config = $config["queue"];
            }

            $channel_exchange_config = $config["exchange"] ?? [];

            $this->declareExchange($channel, $channel_exchange_config);
            $declared_queue = $this->declareQueue($channel, $channel_queue_config);
            $this->bindQueue(
                $channel,
                $channel_exchange_config["exchange"],
                $declared_queue ?? ""
            );
        }

        return $channel;
    }
}

<?php

namespace Itseasy\Queue\Callback;

use PhpAmqpLib\Message\AMQPMessage;

interface QueueCallbackInterface
{
    public function __invoke(AMQPMessage $message);
}

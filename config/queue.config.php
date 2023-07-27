<?php

namespace Itseasy\Queue;

return [
    "queue" => [
        "hosts" => [],
        "options" => [],
        "callback" => Callback\ServiceCallback::class,
        "set_close_on_destruct" => false,
        "channels" => []
    ]
];

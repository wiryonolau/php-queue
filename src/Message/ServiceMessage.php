<?php

namespace Itseasy\Queue\Message;

use Psr\Container\ContainerInterface;
use Exception;
use Throwable;

class ServiceMessage extends AbstractMessage
{
    protected $service = null;
    protected $method = null;
    protected $arguments = [];

    public function __construct(?string $service = null, ?string $method = null, array $arguments = [])
    {
        $this->setService($service);
        $this->setMethod($method);
        $this->setArguments($arguments);
    }

    public function setService(string $service) : void
    {
        $this->service = $service;
    }

    public function setMethod(?string $method = null) : void
    {
        $this->method = $method;
    }

    public function setArgument($key, $value) : void
    {
        $this->arguments[$key] = $value;
    }

    public function setArguments(array $arguments) : void
    {
        $this->arguments = $arguments;
    }

    public function getService() : string
    {
        return $this->service;
    }

    public function getMethod() : ?string
    {
        return $this->method;
    }

    public function getArguments() : ?array
    {
        return $this->arguments;
    }

    public function encode() : string
    {
        return base64_encode(json_encode([
            "service" => $this->service,
            "method" => $this->method,
            "arguments" => $this->arguments
        ]));
    }

    public function run(ContainerInterface $container) : void
    {
        try {
            $service = $container->get($this->service);
            if (is_null($this->method)) {
                // invoke
                call_user_func_array($service, $this->arguments);
            }
            call_user_func_array([$service, $this->method], $this->arguments);
        } catch (Throwable $t) {
            $this->logger->debug($t->getMessage());
        }
    }

    public static function decode(string $value) : ServiceMessage
    {
        $value = base64_decode($value);
        $value = json_decode($value, true);

        return new ServiceMessage($value["service"], $value["method"], $value["arguments"]);
    }

    public function __toString() : string
    {
        return sprintf("Service : %s, Method : %s, Arguments : %s", $this->service, $this->method, print_r($this->arguments, true));
    }
}

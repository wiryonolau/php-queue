<?php

namespace Itseasy\Queue\Message;

use Psr\Container\ContainerInterface;

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

    public function setMethod(string $method) : void
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

    public function encode() : string
    {
        return base64_encode(json_encode([
            "service" => $this->service,
            "method" => $this->method,
            "arguments" => $this->arguments
        ]));
    }

    public function run(ContainerInterface $container)
    {
        $service = $container->get($this->service);
        if (is_null($this->method)) {
            // invoke
            call_user_func_array($service, $this->arguments);
        }
        call_user_func_array([$service, $this->method], $this->arguments);
    }

    public static function decode(string $value) : ServiceMessage
    {
        $value = base64_decode($value);
        $value = json_decode($value);

        return new ServiceMessage($value->service, $value->method, $value->arguments);
    }
}

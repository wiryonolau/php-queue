<?php
declare(strict_types = 1);

namespace Itseasy\Queue\Test;

use DI;
use Laminas\Stdlib\ArrayUtils;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application as ConsoleApplication;

class Application
{
    protected $config = null;
    protected $container = null;
    protected $application = null;

    protected $options = [
        "config_path" => [
            __DIR__."/../config/*.config.php"
        ],
        "container_cache_path" => null,
        "console" => [
            "name" => "",
            "version" => ""
        ]
    ];

    public function __construct(array $options = [])
    {
        foreach ($options as $key => $value) {
            switch ($key) {
                case "config_path":
                    $this->setConfigPath($value);
                    break;
                case "container_cache_path":
                    $this->setContainerCachePath($value);
                    break;
                case "console":
                    $this->setConsoleOptions($value);
                    break;
                default:
            }
        }
    }

    public function setConfigPath($path) : self
    {
        if (is_array($path)) {
            $this->options["config_path"] = ArrayUtils::merge($this->options["config_path"], $path);
        } else {
            $this->options["config_path"][] = $path;
        }
        return $this;
    }

    public function addModule(string $class) : self
    {
        $module_config = call_user_func([$class, "getConfigPath"]);
        array_splice($this->options["config_path"], 1, 0, $module_config);
        return $this;
    }

    public function setContainerCachePath(string $path) : self
    {
        $this->options["container_cache_path"] = $path;
        return $this;
    }

    public function setConsoleOptions(array $options = []) : void
    {
        $this->options["console"] = ArrayUtils::merge($this->options["console"], $options);
    }

    public function build() : void
    {
        $this->config = new Config($this->options["config_path"]);
        $containerBuilder = new DI\ContainerBuilder();
        if (!is_null($this->options["container_cache_path"])) {
            $containerBuilder->enableCompiliation($this->options["container_cache_path"]);
        }
        $this->container = $containerBuilder->build();
        $this->buildContainer();

        $this->application = new ConsoleApplication($this->options["console"]["name"], $this->options["console"]["version"]);
        $this->setCommand();
    }

    public function run() : void
    {
        if (is_null($this->config) or is_null($this->container) or is_null($this->application)) {
            $this->build();
        }
        $this->application->run();
    }

    public function getConfig() : array
    {
        return $this->config->getConfig();
    }

    public function getContainer() : ?ContainerInterface
    {
        return $this->container;
    }

    /**
     * @return Slim\App|Symfony\Component\Console\Application|null
     */
    public function getApplication()
    {
        return $this->application;
    }

    private function setCommand() : void
    {
        $commands = [];
        if (!empty($this->getConfig()["console"]["commands"])) {
            foreach ($this->getConfig()["console"]["commands"] as $command) {
                $commands[] = $this->container->get($command);
            }
        }
        $this->application->addCommands($commands);
    }


    private function buildContainer() : void
    {
        $this->addDefinition('Config', $this->config);
        $this->addDefinition('config', $this->config);

        # Build Service
        if (!empty($this->getConfig()["service"]["factories"])) {
            foreach ($this->getConfig()["service"]["factories"] as $service => $factory) {
                $this->addDefinition($service, $factory);
            }
        }

        # Build Console
        if (!empty($this->getConfig()["console"]["factories"])) {
            array_walk($this->getConfig()["console"]["factories"], [$this, "registerCommand"]);
        }

    }

    private function registerCommand($factory, $command) : void
    {
        $this->addDefinition($command, $factory);
    }

    private function addDefinition($name, $class) : void
    {
        if (is_object($class)) {
            $this->container->set($name, $class);
        } else {
            $this->container->set($name, DI\factory($class));
        }
    }
}

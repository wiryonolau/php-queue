<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

define('APP_DIR', realpath(__DIR__ . '/../'));

require __DIR__ . '/../vendor/autoload.php';

function debug($value, bool $halt = false)
{
    print_r($value);
    ob_flush();
}

function get_docker_secret($path, $default = "", bool $path_from_env = false)
{
    if ($path_from_env) {
        $path = getenv($path);
    }

    if (is_string($path) === false) {
        return $default;
    }

    if (file_exists($path) === false) {
        return $default;
    }

    $secret = file_get_contents($path);
    if ($secret === false) {
        return $default;
    }

    return trim($secret);
}

function get_env(string $name, $default = "", bool $local_only = false)
{
    $env = getenv($name, $local_only);

    if (is_string($env)) {
        $env = trim($env);
    }

    if (empty($env)) {
        $env = $default;
    }

    return $env;
}

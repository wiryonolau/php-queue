<?php

declare(strict_types=1);

namespace Itseasy\Queue\Connection;

use Laminas\Stdlib\ArrayUtils;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Connection\AMQPConnectionConfig;
use PhpAmqpLib\Connection\AMQPConnectionFactory as ConnectionFactory;
use PhpAmqpLib\Connection\AMQPSSLConnection;

/**
 * For ssl conection only, other connection pass to original class
 * Adding additional ssl option not set in original class
 */
class AMQPConnectionFactory extends ConnectionFactory
{
    public static function create(
        AMQPConnectionConfig $config,
        array $ssl_options = []
    ): AbstractConnection {
        if ($config->getIoType() === AMQPConnectionConfig::IO_TYPE_STREAM) {
            if ($config->isSecure()) {
                $connection = new AMQPSSLConnection(
                    $config->getHost(),
                    $config->getPort(),
                    $config->getUser(),
                    $config->getPassword(),
                    $config->getVhost(),
                    self::getSslOptions($config, $ssl_options),
                    [
                        'insist' => $config->isInsist(),
                        'login_method' => $config->getLoginMethod(),
                        'login_response' => $config->getLoginResponse(),
                        'locale' => $config->getLocale(),
                        'connection_timeout' => $config->getConnectionTimeout(),
                        'read_write_timeout' => self::getReadWriteTimeout($config),
                        'keepalive' => $config->isKeepalive(),
                        'heartbeat' => $config->getHeartbeat(),
                    ],
                    $config->getNetworkProtocol(),
                    $config
                );
            } else {
                $connection = parent::create($config);
            }
        } else {
            $connection = parent::create($config);
        }

        return $connection;
    }

    private static function getReadWriteTimeout(AMQPConnectionConfig $config): float
    {
        return min($config->getReadTimeout(), $config->getWriteTimeout());
    }

    /**
     * @param AMQPConnectionConfig $config
     * @return mixed[]
     */
    private static function getSslOptions(
        AMQPConnectionConfig $config,
        array $ssl_options = []
    ): array {
        $ssl_config = ArrayUtils::merge([
            'cafile' => $config->getSslCaCert(),
            'capath' => $config->getSslCaPath(),
            'local_cert' => $config->getSslCert(),
            'local_pk' => $config->getSslKey(),
            'verify_peer' => $config->getSslVerify(),
            'verify_peer_name' => $config->getSslVerifyName(),
            'passphrase' => $config->getSslPassPhrase(),
            'ciphers' => $config->getSslCiphers(),
            'security_level' => $config->getSslSecurityLevel()
        ], $ssl_options);

        return array_filter($ssl_config, static function ($value) {
            return null !== $value;
        });
    }
}

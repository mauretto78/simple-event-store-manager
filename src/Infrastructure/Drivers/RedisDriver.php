<?php
/**
 * This file is part of the EventStoreManager package.
 *
 * (c) Mauro Cassani<https://github.com/mauretto78>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SimpleEventStoreManager\Infrastructure\Drivers;

use SimpleEventStoreManager\Infrastructure\Drivers\Contracts\DriverInterface;
use SimpleEventStoreManager\Infrastructure\Drivers\Exceptions\MalformedDriverConfigException;
use SimpleEventStoreManager\Infrastructure\Drivers\Exceptions\ManageAggregateIndexException;
use SimpleEventStoreManager\Infrastructure\Drivers\Exceptions\NotInstalledDriverCheckException;
use Predis\Client as Redis;

class RedisDriver implements DriverInterface
{
    /**
     * @var
     */
    private $config;

    /**
     * @var Redis
     */
    private $instance;

    /**
     * RedisDriver constructor.
     *
     * @codeCoverageIgnore
     *
     * @param array $config
     *
     * @throws NotInstalledDriverCheckException
     */
    public function __construct(array $config = [])
    {
        $this->setConfig($config);
        if (!$this->check()) {
            throw new NotInstalledDriverCheckException('PRedis Client is not loaded.');
        }
        $this->connect();
    }

    /**
     * @param $config
     *
     * @throws MalformedDriverConfigException
     */
    private function setConfig($config)
    {
        $allowedConfigKeys = [
            'alias',
            'async',
            'database',
            'host',
            'iterable_multibulk',
            'options',
            'password',
            'path',
            'port',
            'persistent',
            'profile',
            'timeout',
            'read_write_timeout',
            'scheme',
            'throw_errors',
            'weight',
        ];

        foreach ($config as $param => $server) {
            if (is_array($server)) {
                foreach (array_keys($server) as $key) {
                    if (!in_array($key, $allowedConfigKeys)) {
                        throw new MalformedDriverConfigException('Redis Driver: malformed config parameters');
                    }
                }
            }

            if (!is_array($server) && !in_array($param, $allowedConfigKeys)) {
                throw new MalformedDriverConfigException('Redis Driver: malformed config parameters');
            }
        }

        $this->config = $config;
    }

    /**
     * @codeCoverageIgnore
     *
     * @return bool
     */
    public function check()
    {
        if (extension_loaded('Redis')) {
            trigger_error('The native Redis extension is installed, you should use Redis instead of Predis to increase performances', E_USER_NOTICE);
        }

        return class_exists('Predis\Client');
    }

    /**
     * @return bool
     */
    public function connect()
    {
        $servers = $this->config ?: [];

        if (count($servers) === 1) {
            $servers = $servers[0];
        }

        if (count($servers) === 0) {
            $servers = [
                'host' => '127.0.0.1',
                'port' => 6379,
                'password' => null,
                'database' => null,
            ];
        }

        $database = (isset($this->config['database'])) ?: 0;

        $this->instance = new Redis($servers);
        $this->instance->select($database);

        return true;
    }

    /**
     * @return mixed
     */
    public function instance()
    {
        return $this->instance;
    }
}

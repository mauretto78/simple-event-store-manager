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
use SimpleEventStoreManager\Infrastructure\Drivers\Exceptions\NotInstalledDriverCheckException;

class PDODriver implements DriverInterface
{
    /**
     * @var
     */
    private $config;

    /**
     * @var \PDO
     */
    private $instance;

    /**
     * PDODriver constructor.
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
            throw new NotInstalledDriverCheckException('PDO is not loaded.');
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
            'database',
            'driver',
            'host',
            'options',
            'password',
            'port',
            'table',
            'username',
        ];

        foreach (array_keys($config) as $key) {
            if (!in_array($key, $allowedConfigKeys)) {
                throw new MalformedDriverConfigException('PDO Driver: malformed config parameters');
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
        return class_exists('\PDO');
    }

    /**
     * @return bool
     */
    public function connect()
    {
        $dsn = $this->config['driver'].':dbname='.$this->config['database'].';host='.$this->config['host'];
        $this->instance = new \PDO($dsn, $this->config['username'], $this->config['password']);

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

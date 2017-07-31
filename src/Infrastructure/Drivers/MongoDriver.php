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

use MongoDB\Client;
use SimpleEventStoreManager\Infrastructure\Drivers\Contracts\DriverInterface;
use SimpleEventStoreManager\Infrastructure\Drivers\Exceptions\ManageAggregateIndexException;
use SimpleEventStoreManager\Infrastructure\Drivers\Exceptions\NotInstalledDriverCheckException;

class MongoDriver implements DriverInterface
{
    /**
     * @var
     */
    private $config;

    /**
     * @var \Client
     */
    private $instance;

    /**
     * PdoDriver constructor.
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
            throw new NotInstalledDriverCheckException('Pdo is not loaded.');
        }

        $this->connect();
    }

    /**
     * @param $config
     *
     * @throws ManageAggregateIndexException
     */
    private function setConfig($config)
    {
        $allowedConfigKeys = [
            'database',
            'host',
            'password',
            'port',
            'username',
        ];

        foreach (array_keys($config) as $key) {
            if (!in_array($key, $allowedConfigKeys)) {
                throw new ManageAggregateIndexException('Mongo Driver: malformed config parameters');
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
        return class_exists('\MongoDB\Client');
    }

    /**
     * @return bool
     */
    public function connect()
    {
        $connectionString = 'mongodb://';
        $connectionString .= ($this->config['username']  && $this->config['password']) ? $this->config['username'].':'.$this->config['password'].'@' : '';
        $connectionString .= $this->config['host'].':'.$this->config['port'];

        $this->instance = (new Client($connectionString))->selectDatabase($this->config['database']);

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

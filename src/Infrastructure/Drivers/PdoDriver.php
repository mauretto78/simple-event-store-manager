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
use SimpleEventStoreManager\Infrastructure\Drivers\Exceptions\ManageAggregateIndexException;
use SimpleEventStoreManager\Infrastructure\Drivers\Exceptions\NotInstalledDriverCheckException;

class PdoDriver implements DriverInterface
{
    const EVENTSTORE_TABLE_NAME = 'eventstore';

    /**
     * @var
     */
    private $config;

    /**
     * @var \PDO
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
                throw new ManageAggregateIndexException('Pdo Driver: malformed config parameters');
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
        $this->createSchema();

        return true;
    }

    /**
     * create schema.
     */
    private function createSchema()
    {
        $query = "CREATE TABLE IF NOT EXISTS `".self::EVENTSTORE_TABLE_NAME."` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `uuid` char(36) COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:guid)',
          `version` int(10) unsigned NOT NULL,
          `type` varchar(255) DEFAULT NULL,
          `body` longtext,
          `occurred_on` datetime(6),
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

        $this->instance->exec($query);
    }

    /**
     * @return mixed
     */
    public function instance()
    {
        return $this->instance;
    }
}

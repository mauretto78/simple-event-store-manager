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

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\DriverManager;
use SimpleEventStoreManager\Infrastructure\Drivers\Contracts\DriverInterface;
use SimpleEventStoreManager\Infrastructure\Drivers\Exceptions\DriverConnectionException;
use SimpleEventStoreManager\Infrastructure\Drivers\Exceptions\MalformedDriverConfigException;
use SimpleEventStoreManager\Infrastructure\Drivers\Exceptions\NotInstalledDriverCheckException;

class DbalDriver implements DriverInterface
{
    /**
     * @var
     */
    private $config;

    /**
     * @var Connection
     */
    private $instance;

    /**
     * DbalDriver constructor.
     *
     * @codeCoverageIgnore
     *
     * @param array $config
     *
     * @throws NotInstalledDriverCheckException
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;

        if (!$this->check()) {
            throw new NotInstalledDriverCheckException('Dbal is not loaded.');
        }

        $this->connect();
    }

    /**
     * @return bool
     */
    public function check()
    {
        return class_exists('Doctrine\DBAL\Connection');
    }

    /**
     * @return bool
     *
     * @throws DriverConnectionException
     */
    public function connect()
    {
        try {
            $this->instance = DriverManager::getConnection($this->config, new Configuration());
            $this->createSchema();

            return true;
        } catch (DBALException $e) {
            throw new DriverConnectionException($e->getMessage());
        }
    }

    /**
     * create schema.
     */
    private function createSchema()
    {
        $query = "CREATE TABLE IF NOT EXISTS `".PdoDriver::EVENTSTORE_TABLE_NAME."` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `uuid` char(36) COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:guid)',
          `version` int(10) unsigned NOT NULL,
          `payload` varchar(255) DEFAULT NULL,
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
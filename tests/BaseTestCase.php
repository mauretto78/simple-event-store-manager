<?php
/**
 * This file is part of the EventStoreManager package.
 *
 * (c) Mauro Cassani<https://github.com/mauretto78>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SimpleEventStoreManager\Tests;

use MongoDB\Client as MongoClient;
use MongoDB\Database;
use PHPUnit\Framework\TestCase;
use Predis\Client as RedisClient;

abstract class BaseTestCase extends TestCase
{
    /**
     * @var null|\PDO
     */
    static private $pdo = null;

    /**
     * @var null|Database
     */
    static private $mongo = null;

    /**
     * @var null|Redis
     */
    static private $redis = null;

    protected $mongo_parameters;
    protected $pdo_parameters;
    protected $redis_parameters;

    /**
     * setup configuration.
     */
    public function setUp()
    {
        $config = require __DIR__.'/../app/bootstrap.php';

        $this->mongo_parameters = $config['mongo'];
        $this->pdo_parameters = $config['pdo'];
        $this->redis_parameters = $config['redis'];

        $this->createConnections();
        $this->createMySQLSchema();
    }

    private function createMySQLSchema()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `events` (
          `id` varchar(255) NOT NULL DEFAULT \'\',
          `name` varchar(255) DEFAULT NULL,
          `body` longtext,
          `occurred_on` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;';

        self::$pdo->query($sql);
    }

    final public function createConnections()
    {
        // PDO connection
        if (self::$pdo == null) {
            try {
                $dsn = $this->pdo_parameters['driver'].':dbname='.$this->pdo_parameters['database'].';host='.$this->pdo_parameters['host'];
                $username = $this->pdo_parameters['username'];
                $password = $this->pdo_parameters['password'];

                self::$pdo = new \PDO($dsn, $username, $password);
            } catch (\PDOException $e) {
                die( 'PDO Error: ' . $e->getMessage() );
            }
        }

        // Mongo connection
        if (self::$mongo == null) {
            try {
                $connectionString = 'mongodb://';
                $connectionString .= ($this->mongo_parameters['username']  && $this->mongo_parameters['password']) ? $this->mongo_parameters['username'].':'.$this->mongo_parameters['password'].'@' : '';
                $connectionString .= $this->mongo_parameters['host'].':'.$this->mongo_parameters['port'];

                self::$mongo = (new MongoClient($connectionString))->selectDatabase($this->mongo_parameters['database']);
            } catch (\Exception $e) {
                die( 'MongoDb Error: ' . $e->getMessage() );
            }
        }

        // Redis connection
        if (self::$redis == null) {
            try {
                $servers = $this->redis_parameters ?: [];
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

                self::$redis = new RedisClient($servers);
            } catch (\Exception $e) {
                die('Redis Error: ' . $e->getMessage());
            }
        }
    }

    /**
     * tearDown
     */
    public function tearDown()
    {
        $this->destroyMySQLSchema();
        $this->destroyMongoDb();
        $this->destroyRedis();
    }

    /**
     * destroyMySQLSchema
     */
    private function destroyMySQLSchema()
    {
        self::$pdo->query('DROP TABLE `events`;');
    }

    /**
     * destroyMongoDb
     */
    private function destroyMongoDb()
    {
        self::$mongo->events->drop();
    }

    /**
     * destroyRedis
     */
    private function destroyRedis()
    {
        self::$redis->flushall();
    }
}

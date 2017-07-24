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

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use MongoDB\Client as MongoClient;
use MongoDB\Database;
use PHPUnit\Framework\TestCase;
use Predis\Client as RedisClient;

abstract class BaseTestCase extends TestCase
{
    /**
     * @var null|\PDO
     */
    private static $pdo = null;

    /**
     * @var null|Database
     */
    private static $mongo = null;

    /**
     * @var null|Redis
     */
    private static $redis = null;

    /**
     * @var null|Client
     */
    private static $elastic = null;

    /**
     * @var array
     */
    protected $mongo_parameters;

    /**
     * @var array
     */
    protected $pdo_parameters;

    /**
     * @var array
     */
    protected $redis_parameters;

    /**
     * @var array
     */
    protected $elastic_parameters;

    /**
     * setup configuration.
     */
    public function setUp()
    {
        $config = require __DIR__.'/../app/bootstrap.php';

        $this->mongo_parameters = $config['mongo'];
        $this->pdo_parameters = $config['pdo'];
        $this->redis_parameters = $config['redis'];
        $this->elastic_parameters = $config['elastic'];

        $this->createConnections();
        $this->createMySQLSchema();
    }

    /**
     * createMySQLSchema
     */
    private function createMySQLSchema()
    {
        $sqlArray = [];
        $sqlArray[] = 'CREATE TABLE IF NOT EXISTS `event_aggregates` (
          `id` varchar(255) NOT NULL DEFAULT \'\',
          `name` varchar(255) UNIQUE,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;';

        $sqlArray[] = 'CREATE TABLE IF NOT EXISTS `events` (
          `id` varchar(255) NOT NULL DEFAULT \'\',
          `aggregate_id` varchar(255),
          `aggregate_name` varchar(255),
          `name` varchar(255) DEFAULT NULL,
          `body` longtext,
          `occurred_on` datetime(6) NULL DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;';

        foreach ($sqlArray as $sql){
            self::$pdo->query($sql);
        }
    }

    /**
     * createConnections
     */
    final public function createConnections()
    {
        // Pdo connection
        if (self::$pdo == null) {
            try {
                $dsn = $this->pdo_parameters['driver'].':dbname='.$this->pdo_parameters['database'].';host='.$this->pdo_parameters['host'];
                $username = $this->pdo_parameters['username'];
                $password = $this->pdo_parameters['password'];

                self::$pdo = new \PDO($dsn, $username, $password);
            } catch (\PDOException $e) {
                die('Pdo Error: ' . $e->getMessage());
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
                die('MongoDb Error: ' . $e->getMessage());
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

        // Elastic connection
        if(self::$elastic == null){
            try {
                $hosts = $this->elastic_parameters ?: [];
                self::$elastic = ClientBuilder::create()
                    ->setHosts($hosts)
                    ->build();
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
        $this->destroyElastic();
    }

    /**
     * destroyMySQLSchema
     */
    private function destroyMySQLSchema()
    {
        self::$pdo->query('DROP TABLE `events`;');
        self::$pdo->query('DROP TABLE `event_aggregates`;');
    }

    /**
     * destroyMongoDb
     */
    private function destroyMongoDb()
    {
        self::$mongo->events->drop();
        self::$mongo->event_aggregates->drop();
    }

    /**
     * destroyRedis
     */
    private function destroyRedis()
    {
        self::$redis->flushall();
    }

    /**
     * destroyElastic
     */
    private function destroyElastic()
    {
        $params = [
            'index' => 'dummy-aggregate'
        ];

        if(self::$elastic->indices()->exists($params)){
            self::$elastic->indices()->delete($params);
        }
    }
}

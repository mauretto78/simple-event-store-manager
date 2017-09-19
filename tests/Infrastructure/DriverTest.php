<?php
/**
 * This file is part of the EventStoreManager package.
 *
 * (c) Mauro Cassani<https://github.com/mauretto78>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use SimpleEventStoreManager\Infrastructure\Drivers\DbalDriver;
use SimpleEventStoreManager\Infrastructure\Drivers\InMemoryDriver;
use SimpleEventStoreManager\Infrastructure\Drivers\MongoDriver;
use SimpleEventStoreManager\Infrastructure\Drivers\PdoDriver;
use SimpleEventStoreManager\Infrastructure\Drivers\RedisDriver;
use SimpleEventStoreManager\Tests\BaseTestCase;

class DriverTest extends BaseTestCase
{
    /**
     * @test
     */
    public function it_should_return_the_correct_driver_instance()
    {
        $dbal = new DbalDriver($this->dbal_parameters);
        $this->assertInstanceOf(Doctrine\DBAL\Connection::class, $dbal->instance());

        $memory = new InMemoryDriver();
        $this->assertInstanceOf(InMemoryDriver::class, $memory->instance());

        $mongo = new MongoDriver($this->mongo_parameters);
        $this->assertInstanceOf(\MongoDB\Database::class, $mongo->instance());

        $pdo = new PdoDriver($this->pdo_parameters);
        $this->assertInstanceOf(\PDO::class, $pdo->instance());

        $redis = new RedisDriver($this->redis_parameters);
        $this->assertInstanceOf(\Predis\Client::class, $redis->instance());
    }
}

<?php
/**
 * This file is part of the Simple EventStore Manager package.
 *
 * (c) Mauro Cassani<https://github.com/mauretto78>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use SimpleEventStoreManager\Infrastructure\Drivers\InMemoryDriver;
use SimpleEventStoreManager\Infrastructure\Drivers\MongoDriver;
use SimpleEventStoreManager\Infrastructure\Drivers\PdoDriver;
use SimpleEventStoreManager\Infrastructure\Drivers\RedisDriver;
use SimpleEventStoreManager\Infrastructure\Services\HashGeneratorService;
use SimpleEventStoreManager\Tests\BaseTestCase;

class HashGeneratorServiceTest extends BaseTestCase
{
    /**
     * @test
     */
    public function it_should_return_the_correct_hash()
    {
        $simpleString = 'User 23';
        $simpleStringWithNoSpace = 'user-23';
        $simpleStringWithAsterisk = 'user-*';

        $this->assertEquals('user-23', HashGeneratorService::computeStringHash($simpleString));
        $this->assertEquals('user-23', HashGeneratorService::computeStringHash($simpleStringWithNoSpace));
        $this->assertEquals('user-*', HashGeneratorService::computeStringHash($simpleStringWithAsterisk));
    }
}

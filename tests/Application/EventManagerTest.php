<?php
/**
 * This file is part of the EventStoreManager package.
 *
 * (c) Mauro Cassani<https://github.com/mauretto78>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use SimpleEventStoreManager\Application\EventManager;
use SimpleEventStoreManager\Domain\EventStore\Contracts\EventRepositoryInterface;
use SimpleEventStoreManager\Tests\BaseTestCase;

class EventManagerTest extends BaseTestCase
{
    /**
     * @test
     * @expectedException \SimpleEventStoreManager\Application\Exceptions\NotSupportedDriverException
     * @expectedExceptionMessage not-allowed-driver is not a supported driver.
     */
    public function it_should_throw_NotSupportedDriverException_if_not_supported_driver_is_passed()
    {
        new EventManager('not-allowed-driver', []);
    }

    /**
     * @test
     */
    public function it_should_store_and_restore_events()
    {
        $streamManager = new EventManager('mongo', $this->mongo_parameters);
        $eventStore = $streamManager->eventStore();

        $this->assertEquals('mongo', $streamManager->driver());
        $this->assertInstanceOf(EventRepositoryInterface::class, $eventStore);
    }
}

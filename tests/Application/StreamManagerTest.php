<?php
/**
 * This file is part of the EventStoreManager package.
 *
 * (c) Mauro Cassani<https://github.com/mauretto78>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use SimpleEventStoreManager\Application\StreamManager;
use SimpleEventStoreManager\Domain\Model\Contracts\EventStoreInterface;
use SimpleEventStoreManager\Tests\BaseTestCase;

class StreamManagerTest extends BaseTestCase
{
    /**
     * @expectedException SimpleEventStoreManager\Application\Exception\NotSupportedDriverException
     * @expectedExceptionMessage not-allowed-driver is not a supported driver.
     * @test
     */
    public function it_should_throw_NotSupportedDriverException_if_not_supported_driver_is_passed()
    {
        new StreamManager('not-allowed-driver', []);
    }

    /**
     * @test
     */
    public function it_should_store_and_restore_events()
    {
        $streamManager = new StreamManager('mongo', $this->mongo_parameters);
        $eventStore = $streamManager->eventStore();

        $this->assertEquals('mongo', $streamManager->driver());
        $this->assertInstanceOf(eventStoreInterface::class, $eventStore);
    }
}

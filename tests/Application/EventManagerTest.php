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
use SimpleEventStoreManager\Domain\Model\Event;
use SimpleEventStoreManager\Domain\Model\EventId;
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
     * @expectedException \SimpleEventStoreManager\Application\Exceptions\NotValidEventException
     * @expectedExceptionMessage Not a valid instance of EventInterface was provided.
     */
    public function it_should_throw_NotValidEventException_if_not_valid_event_is_passed()
    {
        $notValidEvent = new NotValidEvent(1, 'Lorem Ipsum');

        $eventManager = new EventManager('mongo', $this->mongo_parameters);
        $eventManager->storeEvents(
            'Dummy Aggregate',
            [
                $notValidEvent
            ]
        );
    }

    /**
     * @test
     */
    public function it_should_store_and_restore_events_and_send_them_to_elastic()
    {
        $eventId = new EventId();
        $name = 'Doman\\Model\\SomeEvent';
        $body = [
            'id' => 1,
            'title' => 'Lorem Ipsum',
            'text' => 'Dolor lorem ipso facto dixit'
        ];

        $eventId2 = new EventId();
        $name2 = 'Doman\\Model\\SomeEvent2';
        $body2 = [
            'id' => 2,
            'title' => 'Lorem Ipsum',
            'text' => 'Dolor lorem ipso facto dixit'
        ];

        $event = new Event(
            $eventId,
            $name,
            $body
        );
        $event2 = new Event(
            $eventId2,
            $name2,
            $body2
        );

        $eventManager = new EventManager('mongo', $this->mongo_parameters, [
            'elastic' => true,
            'elastic_hosts' => $this->elastic_parameters
        ]);
        $eventManager->storeEvents(
            'Dummy Aggregate',
            [
                $event,
                $event2
            ]
        );

        $stream = $eventManager->stream('Dummy Aggregate');

        $this->assertEquals('mongo', $eventManager->driver());
        $this->assertCount(2, $stream);
    }
}

class NotValidEvent
{
    private $id;

    private $name;

    public function __construct($id, $name)
    {
        $this->id = $id;
        $this->name = $name;
    }
}

<?php
/**
 * This file is part of the EventStoreManager package.
 *
 * (c) Mauro Cassani<https://github.com/mauretto78>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use SimpleEventStoreManager\Application\Event\EventManager;
use SimpleEventStoreManager\Application\Event\EventQuery;
use SimpleEventStoreManager\Domain\Model\AggregateUuid;
use SimpleEventStoreManager\Domain\Model\Contracts\EventAggregateRepositoryInterface;
use SimpleEventStoreManager\Domain\Model\Contracts\EventStoreRepositoryInterface;
use SimpleEventStoreManager\Domain\Model\Event;
use SimpleEventStoreManager\Tests\BaseTestCase;

class EventQueryTest extends BaseTestCase
{
    /**
     * @test
     * @expectedException \SimpleEventStoreManager\Application\Event\Exceptions\NotSupportedDriverException
     * @expectedExceptionMessage not-allowed-driver is not a supported driver.
     */
    public function it_should_throw_NotSupportedDriverException_if_not_supported_driver_is_passed()
    {
        EventManager::build()
            ->setDriver('not-allowed-driver');
    }

    /**
     * @test
     * @expectedException \SimpleEventStoreManager\Application\Event\Exceptions\NotSupportedReturnTypeException
     * @expectedExceptionMessage not-allowed-return-type is not a valid returnType value.
     */
    public function it_should_throw_NotSupportedReturnTypeException_if_not_valid_returnType_is_passed()
    {
        EventManager::build()
            ->setReturnType('not-allowed-return-type');
    }

    /**
     * @test
     * @expectedException \SimpleEventStoreManager\Application\Event\Exceptions\NotValidEventException
     * @expectedExceptionMessage Not a valid instance of EventInterface was provided.
     */
    public function it_should_throw_NotValidEventException_if_not_valid_event_is_passed()
    {
        $notValidEvent = new NotValidEvent(1, 'Lorem Ipsum');

        $eventManager = EventManager::build()
            ->setDriver('mongo')
            ->setConnection($this->mongo_parameters);

        $eventManager->storeEvents(
            [
                $notValidEvent
            ]
        );
    }

    /**
     * @test
     */
    public function it_should_store_and_query_events_and_send_them_to_elastic()
    {
        $uuid = new AggregateUuid();
        $uuid2 = new AggregateUuid();

        $type = 'Doman\\Model\\SomeEvent';
        $body = [
            'id' => 1,
            'title' => 'Lorem Ipsum1',
            'text' => 'Dolor lorem ipso facto dixit'
        ];

        $type2 = 'Doman\\Model\\SomeEvent';
        $body2 = [
            'id' => 2,
            'title' => 'Lorem Ipsum2',
            'text' => 'Dolor lorem ipso facto dixit'
        ];

        $type3 = 'Doman\\Model\\AnotherEvent';
        $body3 = [
            'id' => 3,
            'title' => 'Lorem Ipsum3',
            'text' => 'Dolor lorem ipso facto dixit'
        ];

        $event = new Event(
            $uuid,
            $type,
            $body
        );
        $event2 = new Event(
            $uuid,
            $type2,
            $body2
        );
        $event3 = new Event(
            $uuid2,
            $type3,
            $body3
        );

        $returnTypes = [EventStoreRepositoryInterface::RETURN_AS_ARRAY, EventStoreRepositoryInterface::RETURN_AS_OBJECT];

        foreach ($returnTypes as $returnType) {
            $eventManager = EventManager::build()
                ->setDriver('mongo')
                ->setConnection($this->mongo_parameters)
                ->setElasticServer($this->elastic_parameters)
                ->setReturnType($returnType);

            $eventManager->storeEvents(
                [
                    $event,
                    $event2,
                    $event3
                ]
            );

            $eventQuery = new EventQuery($eventManager);

            $stream = $eventQuery->fromAggregate('Not existing aggregate');
            $this->assertCount(0, $stream);

            $stream = $eventQuery->fromAggregate((string) $uuid);
            $stream2 = $eventQuery->fromAggregate((string) $uuid2);

            $this->assertEquals(2, $eventQuery->streamCount((string) $uuid));
            $this->assertCount(2, $stream);
            $this->assertEquals(1, $eventQuery->streamCount((string) $uuid2));
            $this->assertCount(1, $stream2);

            $streams = $eventQuery->fromAggregates([
                (string) $uuid,
                (string) $uuid2
            ]);
            $this->assertCount(3, $streams);

            $queriedEvents = $eventQuery->query($streams, [
                'type' => 'Doman\\Model\\SomeEvent'
            ]);
            $this->assertCount(2, $queriedEvents);

            $this->tearDown();
        }
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

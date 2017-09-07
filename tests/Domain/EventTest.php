<?php
/**
 * This file is part of the EventStoreManager package.
 *
 * (c) Mauro Cassani<https://github.com/mauretto78>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use SimpleEventStoreManager\Domain\EventRecorder\EventRecorder;
use SimpleEventStoreManager\Domain\EventRecorder\EventRecorderCapabilities;
use SimpleEventStoreManager\Domain\Model\EventAggregate;
use SimpleEventStoreManager\Domain\Model\EventAggregateId;
use SimpleEventStoreManager\Domain\Model\Event;
use SimpleEventStoreManager\Domain\Model\EventUuid;
use SimpleEventStoreManager\Tests\BaseTestCase;

class EventTest extends BaseTestCase
{
    /**
     * @test
     */
    public function create_an_aggregate_with_some_events_and_record_them_with_EventRecorder()
    {
        $eventUuid = new EventUuid();
        $eventName = 'Doman\\Model\\SomeEvent';
        $eventBody = [
            'id' => 1,
            'title' => 'Lorem Ipsum',
            'text' => 'Dolor lorem ipso facto dixit'
        ];

        $event = new Event(
            $eventName,
            $eventBody,
            $eventUuid
        );

        $eventRecorder = new EventRecorder();
        $eventRecorder->record($event);

        $this->assertEquals($eventName, $event->type());
        $this->assertEquals($eventBody, $event->body());
        $this->assertInstanceOf(DateTimeImmutable::class, $event->occurredOn());
        $this->assertCount(1, $eventRecorder->releaseEvents());

        $eventRecorder->record($event);
        $eventRecorder->delete($event->uuid());

        $this->assertCount(0, $eventRecorder->releaseEvents());
    }

    /**
     * @test
     */
    public function create_an_aggregate_with_some_events_and_record_them_with_EventRecorderCapabilities_trait()
    {
        $dummyEntity = new DummyEntity(
            'John Doe',
            'johndoe@gmail.com'
        );

        $releasedEvents = $dummyEntity->releaseEvents();

        $this->assertCount(1, $releasedEvents);

        foreach ($releasedEvents as $event) {
            $this->assertInstanceOf(DummyEntityWasCreated::class, $event);
            $this->assertInstanceOf(DummyEntity::class, $event->body());
        }
    }
}

class DummyEntity
{
    use EventRecorderCapabilities;

    private $name;
    private $email;

    public function __construct(
        $name,
        $email
    ) {
        $this->name = $name;
        $this->email = $email;

        $this->record(
            new DummyEntityWasCreated(
                'DummyEntityWasCreated',
                $this
            )
        );
    }
}

class DummyEntityWasCreated extends Event
{
}

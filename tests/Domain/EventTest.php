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
use SimpleEventStoreManager\Domain\Model\Aggregate;
use SimpleEventStoreManager\Domain\Model\AggregateId;
use SimpleEventStoreManager\Domain\Model\Event;
use SimpleEventStoreManager\Domain\Model\EventId;
use SimpleEventStoreManager\Tests\BaseTestCase;

class EventTest extends BaseTestCase
{
    /**
     * @test
     */
    public function create_an_aggregate_with_some_events_and_record_them_with_EventRecorder()
    {
        $eventId = new EventId();
        $name = 'Doman\\Model\\SomeEvent';
        $body = [
            'id' => 1,
            'title' => 'Lorem Ipsum',
            'text' => 'Dolor lorem ipso facto dixit'
        ];

        $aggregate = new Aggregate(
            new AggregateId(),
            'Dummy Aggregate'
        );
        $aggregate->addEvent(
            $event = new Event(
                $eventId,
                $aggregate,
                $name,
                $body
            )
        );

        $eventRecorder = new EventRecorder();
        $eventRecorder->record($event);

        $this->assertCount(1, $aggregate->events());
        $this->assertEquals($eventId, $eventId->id());
        $this->assertEquals($eventId, $event->id());
        $this->assertEquals($event->aggregate()->name(), 'dummy-aggregate');
        $this->assertEquals($name, $event->name());
        $this->assertEquals($body, unserialize($event->body()));
        $this->assertInstanceOf(DateTimeImmutable::class, $event->occurredOn());
        $this->assertCount(1, $eventRecorder->releaseEvents());

        $eventRecorder->record($event);
        $eventRecorder->delete($eventId);

        $this->assertCount(0, $eventRecorder->releaseEvents());
    }

    /**
     * @test
     */
    public function create_an_aggregate_with_some_events_and_record_them_with_EventRecorderCapabilities_trait()
    {
        $dummyEntity = new DummyEntity(
            12,
            'John Doe',
            'johndoe@gmail.com'
        );

        $releasedEvents = $dummyEntity->releaseEvents();

        $this->assertCount(1, $releasedEvents);

        foreach ($releasedEvents as $event){
            $this->assertInstanceOf(DummyEntityWasCreated::class, $event);
            $this->assertInstanceOf(DummyEntity::class, unserialize($event->body()));
        }
    }
}

class DummyEntity
{
    use EventRecorderCapabilities;

    private $id;
    private $name;
    private $email;

    public function __construct(
        $id,
        $name,
        $email
    )
    {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;

        $this->record(
            new DummyEntityWasCreated(
                new EventId(),
                new Aggregate(
                    new AggregateId(),
                    'Dummy Aggregate'
                ),
                'DummyEntityWasCreated',
                $this
            )
        );
    }
}

class DummyEntityWasCreated extends Event
{
}

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
use SimpleEventStoreManager\Domain\Model\Contracts\EventInterface;
use SimpleEventStoreManager\Domain\Model\Event;
use SimpleEventStoreManager\Domain\Model\EventId;
use SimpleEventStoreManager\Tests\BaseTestCase;

class EventTest extends BaseTestCase
{
    /**
     * @test
     */
    public function create_an_event_and_record_it_with_EventRecorder()
    {
        $eventId = new EventId();
        $name = 'Doman\\Model\\SomeEvent';
        $body = [
            'id' => 1,
            'title' => 'Lorem Ipsum',
            'text' => 'Dolor lorem ipso facto dixit'
        ];

        $event = new Event(
            $eventId,
            $name,
            $body
        );

        $eventRecorder = new EventRecorder();
        $eventRecorder->record($event);

        $this->assertEquals($eventId, $eventId->id());
        $this->assertEquals($eventId, $event->id());
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
    public function create_an_event_in_an_entity_and_record_it_with_EventRecorderCapabilities_trait()
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
            $this->assertInstanceOf(DummyEntity::class, $event->body());
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
                $this
            )
        );
    }
}

class DummyEntityWasCreated implements EventInterface
{
    /**
     * @var EventId
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var mixed
     */
    private $body;

    /**
     * @var \DateTimeImmutable
     */
    private $occurred_on;

    /**
     * Event constructor.
     * @param EventId $id
     * @param $body
     */
    public function __construct(
        EventId $id,
        $body
    ) {
        $this->id = $id;
        $this->name = get_class($this);
        $this->body = serialize($body);
        $this->occurred_on = new \DateTimeImmutable();
    }

    /**
     * @return string
     */
    public function id()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function body()
    {
        return unserialize($this->body);
    }

    /**
     * @return \DateTimeImmutable
     */
    public function occurredOn()
    {
        return new \DateTimeImmutable('now');
    }
}

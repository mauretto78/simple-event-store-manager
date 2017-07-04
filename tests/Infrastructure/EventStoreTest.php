<?php
/**
 * This file is part of the EventStoreManager package.
 *
 * (c) Mauro Cassani<https://github.com/mauretto78>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use SimpleEventStoreManager\Domain\Model\Contracts\EventStoreInterface;
use SimpleEventStoreManager\Domain\Model\Event;
use SimpleEventStoreManager\Domain\Model\EventId;
use SimpleEventStoreManager\Infrastructure\Drivers\InMemoryDriver;
use SimpleEventStoreManager\Infrastructure\Drivers\MongoDriver;
use SimpleEventStoreManager\Infrastructure\Drivers\PDODriver;
use SimpleEventStoreManager\Infrastructure\Drivers\RedisDriver;
use SimpleEventStoreManager\Infrastructure\Persistence\InMemoryEventStore;
use SimpleEventStoreManager\Infrastructure\Persistence\MongoEventStore;
use SimpleEventStoreManager\Infrastructure\Persistence\PDOEventStore;
use SimpleEventStoreManager\Infrastructure\Persistence\RedisEventStore;
use SimpleEventStoreManager\Tests\BaseTestCase;

class EventStoreTest extends BaseTestCase
{
    /**
     * @var array
     */
    private $eventStores;

    public function setUp()
    {
        parent::setUp();

        $this->eventStores = [
            new InMemoryEventStore((new InMemoryDriver())->instance()),
            new MongoEventStore((new MongoDriver($this->mongo_parameters))->instance()),
            new PDOEventStore((new PDODriver($this->pdo_parameters))->instance()),
            new RedisEventStore((new RedisDriver($this->redis_parameters))->instance()),
        ];
    }

    /**
     * @test
     */
    public function it_should_store_and_restore_events()
    {
        /** @var EventStoreInterface $eventStore */
        foreach ($this->eventStores as $eventStore) {
            $now = (new \DateTimeImmutable())->format('Y-m-d H:i:s');

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

            $eventId2 = new EventId();
            $name2 = 'Doman\\Model\\SomeEvent2';
            $body2 = [
                'id' => 2,
                'title' => 'Lorem Ipsum',
                'text' => 'Dolor lorem ipso facto dixit'
            ];

            $event2 = new Event(
                $eventId2,
                $name2,
                $body2
            );

            $eventStore->store($event);
            $eventStore->store($event2);
            $storedEvent = $eventStore->restore($eventId);
            $events = $eventStore->eventsInRangeDate(
                new \DateTimeImmutable('yesterday'),
                new \DateTimeImmutable('now')
            );

            $this->assertEquals((string) $eventId, $storedEvent->id);
            $this->assertEquals($name, $storedEvent->name);
            $this->assertEquals($body, unserialize($storedEvent->body));
            $this->assertEquals($now, $storedEvent->occurred_on);
            $this->assertGreaterThan(0, $eventStore->eventsCount());
            $this->assertGreaterThan(0, count($events));
        }
    }
}

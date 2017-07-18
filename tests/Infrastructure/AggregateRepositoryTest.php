<?php
/**
 * This file is part of the EventStoreManager package.
 *
 * (c) Mauro Cassani<https://github.com/mauretto78>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use SimpleEventStoreManager\Domain\EventStore\Contracts\EventRepositoryInterface;
use SimpleEventStoreManager\Domain\Model\Aggregate;
use SimpleEventStoreManager\Domain\Model\AggregateId;
use SimpleEventStoreManager\Domain\Model\Contracts\AggregateRepositoryInterface;
use SimpleEventStoreManager\Domain\Model\Event;
use SimpleEventStoreManager\Domain\Model\EventId;
use SimpleEventStoreManager\Infrastructure\Drivers\InMemoryDriver;
use SimpleEventStoreManager\Infrastructure\Drivers\MongoDriver;
use SimpleEventStoreManager\Infrastructure\Drivers\PDODriver;
use SimpleEventStoreManager\Infrastructure\Drivers\RedisDriver;
use SimpleEventStoreManager\Infrastructure\Persistence\InMemoryAggregateRepository;
use SimpleEventStoreManager\Infrastructure\Persistence\InMemoryEventRepository;
use SimpleEventStoreManager\Infrastructure\Persistence\MongoEventRepository;
use SimpleEventStoreManager\Infrastructure\Persistence\PDOEventRepository;
use SimpleEventStoreManager\Infrastructure\Persistence\RedisEventRepository;
use SimpleEventStoreManager\Tests\BaseTestCase;

class AggregateRepositoryTest extends BaseTestCase
{
    /**
     * @var array
     */
    private $repos;

    public function setUp()
    {
        parent::setUp();

        $this->repos = [
            new InMemoryAggregateRepository((new InMemoryDriver())->instance()),
            //new MongoEventRepository((new MongoDriver($this->mongo_parameters))->instance()),
            //new PDOEventRepository((new PDODriver($this->pdo_parameters))->instance()),
            //new RedisEventRepository((new RedisDriver($this->redis_parameters))->instance()),
        ];
    }

    /**
     * @test
     */
    public function it_should_store_and_restore_events()
    {
        /** @var AggregateRepositoryInterface $repo */
        foreach ($this->repos as $repo) {




            /*$now = (new \DateTimeImmutable())->format('Y-m-d H:i:s');
            $eventId = new EventId();
            $name = 'Doman\\Model\\SomeEvent';
            $body = [
                'id' => 1,
                'title' => 'Lorem Ipsum',
                'text' => 'Dolor lorem ipso facto dixit'
            ];

            $event = new Event(
                $eventId,
                'Dummy Aggregate',
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
                'Dummy Aggregate',
                $name2,
                $body2
            );

            $eventStore->store($event);
            $eventStore->store($event2);
            $storedEvent = $eventStore->restore($eventId);

            sleep(1);

            $aggregateByName = $eventStore->findAggregateByName('Dummy Aggregate');

            $this->assertEquals('dummy-aggregate', $aggregateByName->name);
            $this->assertEquals($storedEvent->id, (string) $eventId);
            $this->assertEquals($name, $storedEvent->name);
            $this->assertEquals($body, unserialize($storedEvent->body));
            $this->assertEquals($now, $storedEvent->occurred_on);
            $this->assertGreaterThan(0, $eventStore->eventsCount());

            $events = $eventStore->query(
                [
                    'aggregate_name' => 'Dummy Aggregate',
                    'from' => 'yesterday',
                    'to' => 'now'
                ]
            );

            $this->assertGreaterThan(0, count($events));*/
        }
    }
}

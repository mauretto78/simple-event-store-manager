<?php
/**
 * This file is part of the EventStoreManager package.
 *
 * (c) Mauro Cassani<https://github.com/mauretto78>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use SimpleEventStoreManager\Domain\Model\Contracts\EventStoreRepositoryInterface;
use SimpleEventStoreManager\Domain\Model\EventAggregate;
use SimpleEventStoreManager\Domain\Model\EventAggregateId;
use SimpleEventStoreManager\Domain\Model\Contracts\EventAggregateRepositoryInterface;
use SimpleEventStoreManager\Domain\Model\Event;
use SimpleEventStoreManager\Domain\Model\EventUuid;
use SimpleEventStoreManager\Infrastructure\Drivers\InMemoryDriver;
use SimpleEventStoreManager\Infrastructure\Drivers\MongoDriver;
use SimpleEventStoreManager\Infrastructure\Drivers\PdoDriver;
use SimpleEventStoreManager\Infrastructure\Drivers\RedisDriver;
use SimpleEventStoreManager\Infrastructure\Persistence\InMemoryEventAggregateRepository;
use SimpleEventStoreManager\Infrastructure\Persistence\MongoEventAggregateRepository;
use SimpleEventStoreManager\Infrastructure\Persistence\PdoEventStoreRepository;
use SimpleEventStoreManager\Infrastructure\Persistence\RedisEventAggregateRepository;
use SimpleEventStoreManager\Tests\BaseTestCase;

class EventStoreRepositoryTest extends BaseTestCase
{
    /**
     * @var array
     */
    private $repos;

    public function setUp()
    {
        parent::setUp();

        $this->repos = [
            //new InMemoryEventAggregateRepository((new InMemoryDriver())->instance()),
            //new MongoEventAggregateRepository((new MongoDriver($this->mongo_parameters))->instance()),
            new PdoEventStoreRepository((new PdoDriver($this->pdo_parameters))->instance()),
            //new RedisEventAggregateRepository((new RedisDriver($this->redis_parameters))->instance()),
        ];
    }

    /**
     * @test
     */
    public function it_should_store_and_restore_aggregates()
    {
        /** @var EventStoreRepositoryInterface $repo */
        foreach ($this->repos as $repo) {
            $eventUuid = new EventUuid();
            $event1 = new Event(
                'Doman\\Model\\SomeEvent',
                [
                    'id' => 1,
                    'title' => 'Lorem Ipsum',
                    'text' => 'Dolor lorem ipso facto dixit'
                ],
                $eventUuid
            );
            $event2 = new Event(
                'Doman\\Model\\SomeEvent2',
                [
                    'id' => 1,
                    'title' => 'Lorem Ipsum',
                    'text' => 'Dolor lorem ipso facto dixit'
                ],
                $eventUuid
            );
            $event3 = new Event(
                'Doman\\Model\\SomeEvent3',
                [
                    'id' => 1,
                    'title' => 'Lorem Ipsum',
                    'text' => 'Dolor lorem ipso facto dixit'
                ],
                $eventUuid
            );

            $repo->save($event1);
            $repo->save($event2);
            $repo->save($event3);

            $eventAggregateAsArray = $repo->byUuid($eventUuid, EventStoreRepositoryInterface::RETURN_AS_ARRAY);
            $eventAggregateAsObject = $repo->byUuid($eventUuid, EventStoreRepositoryInterface::RETURN_AS_OBJECT);

            $this->assertNull($repo->byUuid(new EventUuid('432fdfdsfsdasd')));
            $this->assertEquals(3, $repo->count($eventUuid));
            $this->assertArrayHasKey('events', $eventAggregateAsArray);
            $this->assertCount(3, $eventAggregateAsArray['events']);
//            $this->assertEquals($aggregate, $aggregateAsObject);
//            $this->assertEquals($aggregate, $aggregateAsObjectByName);
        }
    }
}

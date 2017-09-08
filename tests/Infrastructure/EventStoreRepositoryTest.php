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
use SimpleEventStoreManager\Domain\Model\Event;
use SimpleEventStoreManager\Domain\Model\AggregateUuid;
use SimpleEventStoreManager\Infrastructure\Drivers\InMemoryDriver;
use SimpleEventStoreManager\Infrastructure\Drivers\MongoDriver;
use SimpleEventStoreManager\Infrastructure\Drivers\PdoDriver;
use SimpleEventStoreManager\Infrastructure\Drivers\RedisDriver;
use SimpleEventStoreManager\Infrastructure\Persistence\InMemoryEventStoreRepository;
use SimpleEventStoreManager\Infrastructure\Persistence\MongoEventStoreRepository;
use SimpleEventStoreManager\Infrastructure\Persistence\PdoEventStoreRepository;
use SimpleEventStoreManager\Infrastructure\Persistence\RedisEventStoreRepository;
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
            new InMemoryEventStoreRepository((new InMemoryDriver())->instance()),
            new MongoEventStoreRepository((new MongoDriver($this->mongo_parameters))->instance()),
            new PdoEventStoreRepository((new PdoDriver($this->pdo_parameters))->instance()),
            new RedisEventStoreRepository((new RedisDriver($this->redis_parameters))->instance()),
        ];
    }

    /**
     * @test
     */
    public function it_should_store_and_restore_event_aggregates()
    {
        /** @var EventStoreRepositoryInterface $repo */
        foreach ($this->repos as $repo) {
            $eventUuid = new AggregateUuid();
            $event1 = new Event(
                $eventUuid,
                'Doman\\Model\\SomeEvent',
                [
                    'id' => 1,
                    'title' => 'Lorem Ipsum',
                    'text' => 'Dolor lorem ipso facto dixit'
                ]
            );
            $event2 = new Event(
                $eventUuid,
                'Doman\\Model\\SomeEvent2',
                [
                    'id' => 1,
                    'title' => 'Lorem Ipsum',
                    'text' => 'Dolor lorem ipso facto dixit'
                ]
            );
            $event3 = new Event(
                $eventUuid,
                'Doman\\Model\\SomeEvent3',
                [
                    'id' => 1,
                    'title' => 'Lorem Ipsum',
                    'text' => 'Dolor lorem ipso facto dixit'
                ]
            );

            $repo->save($event1);
            $repo->save($event2);
            $repo->save($event3);

            $eventAggregateAsArray = $repo->byUuid($eventUuid, EventStoreRepositoryInterface::RETURN_AS_ARRAY);
            $eventAggregateAsObject = $repo->byUuid($eventUuid, EventStoreRepositoryInterface::RETURN_AS_OBJECT);

            $this->assertNull($repo->byUuid(new AggregateUuid('432fdfdsfsdasd')));
            $this->assertEquals(3, $repo->count($eventUuid));
            $this->assertCount(3, $eventAggregateAsArray);
            $this->assertCount(3, $eventAggregateAsObject);
            $this->assertEquals(0, $eventAggregateAsArray[0]['version']);
            $this->assertEquals(1, $eventAggregateAsArray[1]['version']);
            $this->assertEquals(2, $eventAggregateAsArray[2]['version']);
            $this->assertEquals(0, $eventAggregateAsObject[0]->version());
            $this->assertEquals(1, $eventAggregateAsObject[1]->version());
            $this->assertEquals(2, $eventAggregateAsObject[2]->version());
        }
    }
}

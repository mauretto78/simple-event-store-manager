<?php
/**
 * This file is part of the EventStoreManager package.
 *
 * (c) Mauro Cassani<https://github.com/mauretto78>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use SimpleEventStoreManager\Domain\Model\EventAggregate;
use SimpleEventStoreManager\Domain\Model\EventAggregateId;
use SimpleEventStoreManager\Domain\Model\Contracts\EventAggregateRepositoryInterface;
use SimpleEventStoreManager\Domain\Model\Event;
use SimpleEventStoreManager\Domain\Model\EventId;
use SimpleEventStoreManager\Infrastructure\Drivers\InMemoryDriver;
use SimpleEventStoreManager\Infrastructure\Drivers\MongoDriver;
use SimpleEventStoreManager\Infrastructure\Drivers\PdoDriver;
use SimpleEventStoreManager\Infrastructure\Drivers\RedisDriver;
use SimpleEventStoreManager\Infrastructure\Persistence\InMemoryEventAggregateRepository;
use SimpleEventStoreManager\Infrastructure\Persistence\MongoEventAggregateRepository;
use SimpleEventStoreManager\Infrastructure\Persistence\PdoEventAggregateRepository;
use SimpleEventStoreManager\Infrastructure\Persistence\RedisEventAggregateRepository;
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
            new InMemoryEventAggregateRepository((new InMemoryDriver())->instance()),
            new MongoEventAggregateRepository((new MongoDriver($this->mongo_parameters))->instance()),
            new PdoEventAggregateRepository((new PdoDriver($this->pdo_parameters))->instance()),
            new RedisEventAggregateRepository((new RedisDriver($this->redis_parameters))->instance()),
        ];
    }

    /**
     * @test
     */
    public function it_should_store_and_restore_aggregates()
    {
        /** @var EventAggregateRepositoryInterface $repo */
        foreach ($this->repos as $repo) {
            $name = 'Doman\\Model\\SomeEvent';
            $body = [
                'id' => 1,
                'title' => 'Lorem Ipsum',
                'text' => 'Dolor lorem ipso facto dixit'
            ];

            $name2 = 'Doman\\Model\\SomeEvent2';
            $body2 = [
                'id' => 2,
                'title' => 'Lorem Ipsum',
                'text' => 'Dolor lorem ipso facto dixit'
            ];

            $name3 = 'Doman\\Model\\SomeEvent3';
            $body3 = [
                'id' => 3,
                'title' => 'Lorem Ipsum',
                'text' => 'Dolor lorem ipso facto dixit'
            ];

            $name4 = 'Doman\\Model\\SomeEvent3';
            $body4 = [
                'id' => 4,
                'title' => 'Lorem Ipsum',
                'text' => 'Dolor lorem ipso facto dixit'
            ];

            $aggregate = new EventAggregate('EventAggregate-1');
            $aggregate->addEvent(
                new Event(
                    $name,
                    $body
                )
            );
            $aggregate->addEvent(
                new Event(
                    $name2,
                    $body2
                )
            );
            $aggregate2 = new EventAggregate('EventAggregate-2');
            $aggregate2->addEvent(
                new Event(
                    $name3,
                    $body3
                )
            );
            $aggregate2->addEvent(
                new Event(
                    $name4,
                    $body4
                )
            );

            $repo->save($aggregate);
            $repo->save($aggregate2);

            $aggregateAsArray = $repo->byId($aggregate->id(), EventAggregateRepositoryInterface::RETURN_AS_ARRAY);
            $aggregateAsObject = $repo->byId($aggregate->id(), EventAggregateRepositoryInterface::RETURN_AS_OBJECT);
            $aggregateAsObjectByName = $repo->byName('EventAggregate-1', EventAggregateRepositoryInterface::RETURN_AS_OBJECT);

            $this->assertNull($repo->byId(new EventAggregateId('432fdfdsfsdasd')));
            $this->assertFalse($repo->exists('not-existing-aggregate'));
            $this->assertEquals(2, $repo->eventsCount($aggregate));
            $this->assertArrayHasKey('id', $aggregateAsArray);
            $this->assertArrayHasKey('name', $aggregateAsArray);
            $this->assertArrayHasKey('events', $aggregateAsArray);
            $this->assertCount(2, $aggregateAsArray['events']);
            $this->assertEquals($aggregate, $aggregateAsObject);
            $this->assertEquals($aggregate, $aggregateAsObjectByName);
            $this->assertTrue($repo->exists('EventAggregate-1'));
            $this->assertNull($repo->byName('not existing aggregate'));
        }
    }
}

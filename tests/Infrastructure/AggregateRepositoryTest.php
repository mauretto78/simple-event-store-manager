<?php
/**
 * This file is part of the EventStoreManager package.
 *
 * (c) Mauro Cassani<https://github.com/mauretto78>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
use SimpleEventStoreManager\Infrastructure\Persistence\MongoAggregateRepository;
use SimpleEventStoreManager\Infrastructure\Persistence\PDOAggregateRepository;
use SimpleEventStoreManager\Infrastructure\Persistence\RedisAggregateRepository;
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
            //new InMemoryAggregateRepository((new InMemoryDriver())->instance()),
            //new MongoAggregateRepository((new MongoDriver($this->mongo_parameters))->instance()),
            //new PDOAggregateRepository((new PDODriver($this->pdo_parameters))->instance()),
            new RedisAggregateRepository((new RedisDriver($this->redis_parameters))->instance()),
        ];
    }

    /**
     * @test
     */
    public function it_should_store_and_restore_aggregates()
    {
        /** @var AggregateRepositoryInterface $repo */
        foreach ($this->repos as $repo) {
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

            $aggregate = new Aggregate(
                $aggregateId = new AggregateId(),
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
            $aggregate->addEvent(
                $event = new Event(
                    $eventId2,
                    $aggregate,
                    $name2,
                    $body2
                )
            );

            $repo->save($aggregate);

            $this->assertNull($repo->byId(new AggregateId('432fdfdsfsdasd'), false));
            $this->assertEquals($aggregate, $repo->byId($aggregateId));
            $this->assertEquals($aggregate, $repo->byName('Dummy Aggregate'));
            $this->assertNull($repo->byName('not existing aggregate'), false);
            $this->assertEquals(2, $repo->eventsCount($aggregate));

            sleep(1);

            $query = $repo->queryEvents(
                $aggregate,
                [
                    'from' => 'yesterday',
                    'to' => 'now',
                ]
            );

            $this->assertCount(2, $query);
            foreach ($query as $item){
                $this->assertInstanceOf(Event::class, $item);
            }
        }
    }
}

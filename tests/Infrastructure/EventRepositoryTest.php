<?php
/**
 * This file is part of the Simple EventStore Manager package.
 *
 * (c) Mauro Cassani<https://github.com/mauretto78>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


use SimpleEventStoreManager\Domain\Model\Aggregate;
use SimpleEventStoreManager\Domain\Model\AggregateId;
use SimpleEventStoreManager\Domain\Model\Contracts\EventRepositoryInterface;
use SimpleEventStoreManager\Domain\Model\Event;
use SimpleEventStoreManager\Domain\Model\EventId;
use SimpleEventStoreManager\Infrastructure\Drivers\InMemoryDriver;
use SimpleEventStoreManager\Infrastructure\Drivers\MongoDriver;
use SimpleEventStoreManager\Infrastructure\Drivers\PDODriver;
use SimpleEventStoreManager\Infrastructure\Persistence\InMemoryEventRepository;
use SimpleEventStoreManager\Infrastructure\Persistence\MongoEventRepository;
use SimpleEventStoreManager\Infrastructure\Persistence\PDOEventRepository;
use SimpleEventStoreManager\Tests\BaseTestCase;

class EventRepositoryTest extends BaseTestCase
{
    /**
     * @var array
     */
    private $repos;

    public function setUp()
    {
        parent::setUp();

        $this->repos = [
            new InMemoryEventRepository((new InMemoryDriver())->instance()),
            new MongoEventRepository((new MongoDriver($this->mongo_parameters))->instance()),
            new PDOEventRepository((new PDODriver($this->pdo_parameters))->instance()),
            //new RedisEventRepository((new RedisDriver($this->redis_parameters))->instance()),
        ];
    }

    /**
     * @test
     */
    public function it_should_store_and_restore_events()
    {
        /** @var EventRepositoryInterface $repo */
        foreach ($this->repos as $repo) {

            $aggregate = new Aggregate(
                new AggregateId(),
                'Dummy Aggregate'
            );

            $eventId = new EventId();
            $eventId2 = new EventId();

            $name = 'Doman\\Model\\SomeEvent';
            $body = [
                'id' => 1,
                'title' => 'Lorem Ipsum',
                'text' => 'Dolor lorem ipso facto dixit'
            ];
            $event = new Event(
                $eventId,
                $aggregate,
                $name,
                $body
            );
            $event2 = new Event(
                $eventId2,
                $aggregate,
                $name,
                $body
            );

            $repo->save($event);
            $repo->save($event2);

            $this->assertEquals($event, $repo->byId($eventId));
            $this->assertEquals($event2, $repo->byId($eventId2));
            $this->assertNull($repo->byId(new EventId()));
        }
    }
}

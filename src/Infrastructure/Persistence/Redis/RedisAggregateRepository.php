<?php
/**
 * This file is part of the Simple EventStore Manager package.
 *
 * (c) Mauro Cassani<https://github.com/mauretto78>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SimpleEventStoreManager\Infrastructure\Persistence\Redis;

use Cocur\Slugify\Slugify;
use Predis\Client;
use Predis\Collection\Iterator\SetKey;
use SimpleEventStoreManager\Domain\Model\Aggregate;
use SimpleEventStoreManager\Domain\Model\AggregateId;
use SimpleEventStoreManager\Domain\Model\Contracts\AggregateRepositoryInterface;
use SimpleEventStoreManager\Domain\Model\Event;
use SimpleEventStoreManager\Domain\Model\EventId;

class RedisAggregateRepository implements AggregateRepositoryInterface
{
    /**
     * @var Client
     */
    private $client;

    /**
     * RedisEventRepository constructor.
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param AggregateId $id
     *
     * @return Aggregate
     */
    public function byId(AggregateId $id)
    {
        if ($aggregate = $this->client->hgetall('aggregate:'.$id)) {
            return $this->buildAggregate($aggregate);
        }

        return null;
    }

    /**
     * @param $name
     * @param bool $hydrateEvents
     *
     * @return Aggregate
     */
    public function byName($name, $hydrateEvents = true)
    {
        $aggregate = new SetKey($this->client, 'aggregatesIndexByName:'.(new Slugify())->slugify($name));
        foreach ($aggregate as $aggregateKey) {
            if ($row = $this->client->hgetall($aggregateKey)) {
                return $this->buildAggregate($row);
            }

            return null;
        }
    }

    /**
     * @return int
     */
    public function eventsCount(Aggregate $aggregate)
    {
        return count($this->client->hgetall('aggregate:'.$aggregate->id()));
    }

    /**
     * @param $name
     *
     * @return bool
     */
    public function exists($name)
    {
        return (new SetKey($this->client, 'aggregatesIndexByName:'.(new Slugify())->slugify($name))) ? true : false;
    }

    /**
     * @param Aggregate $aggregate
     * @return mixed
     */
    public function save(Aggregate $aggregate)
    {
        $aggregateId = $aggregate->id();
        $aggregateName = $aggregate->name();
        $redisKey = 'aggregate:'.$aggregateId;

        $this->client->hset($redisKey, 'id', $aggregateId);
        $this->client->hset($redisKey, 'name', $aggregateName);

        $this->client->sadd('aggregatesIndexByName:'.$aggregate->name(), [$redisKey]);

        /** @var Event $event */
        foreach ($aggregate->events() as $event){
            $eventRepo = new RedisEventRepository($this->client);
            $eventRepo->save($event);
        }
    }

    /**
     * @param array $row
     *
     * @return Aggregate
     */
    public function buildAggregate(array $row)
    {
        $aggregate = new Aggregate(
            new AggregateId($row['id']),
            $row['name']
        );

        $events = new SetKey($this->client, 'eventsAggregatesIndexById:'.$row['id']);
        foreach ($events as $eventKey){
            $event = $this->client->hgetall($eventKey);
            $aggregate->addEvent(
                new Event(
                    new EventId($event['id']),
                    unserialize($event['aggregate']),
                    $event['name'],
                    unserialize($event['body']),
                    $event['occurred_on']
                )
            );
        }

        return $aggregate;
    }
}

<?php
/**
 * This file is part of the Simple EventStore Manager package.
 *
 * (c) Mauro Cassani<https://github.com/mauretto78>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SimpleEventStoreManager\Infrastructure\Persistence;

use Cocur\Slugify\Slugify;
use Predis\Client;
use Predis\Collection\Iterator\SetKey;
use SimpleEventStoreManager\Domain\Model\Aggregate;
use SimpleEventStoreManager\Domain\Model\AggregateId;
use SimpleEventStoreManager\Domain\Model\Contracts\AggregateRepositoryInterface;
use SimpleEventStoreManager\Domain\Model\Event;
use SimpleEventStoreManager\Domain\Model\EventId;

class RedisAggregateRepository extends AbstractAggregateRepository implements AggregateRepositoryInterface
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
     * @param bool $hydrateEvents
     *
     * @return Aggregate
     */
    public function byId(AggregateId $id, $hydrateEvents = true)
    {
        if ($aggregate = $this->client->hgetall('aggregate:'.$id)) {
            return $this->buildAggregate($aggregate, $hydrateEvents);
        }

        return null;
    }

    /**
     * @param $name
     * @param bool $hydrateEvents
     * @return Aggregate
     */
    public function byName($name, $hydrateEvents = true)
    {
        $aggregate = new SetKey($this->client, 'aggregatesIndexByName:'.(new Slugify())->slugify($name));

        foreach ($aggregate as $aggregateKey) {
            if ($row = $this->client->hgetall($aggregateKey)) {
                return $this->buildAggregate($row, $hydrateEvents);
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
     * @param Aggregate $aggregate
     * @param array $parameters
     *
     * @return Event[]
     */
    public function queryEvents(Aggregate $aggregate, array $parameters = [])
    {
        $results = [];

        $from = (isset($parameters['from'])) ? new \DateTimeImmutable($parameters['from']) : new \DateTimeImmutable();
        $to = (isset($parameters['to'])) ? new \DateTimeImmutable($parameters['to']) : new \DateTimeImmutable();

        $eventMicroTimeArray = ($from && $to) ? $this->client->zrangebyscore('eventsMtIndex', $from->format('U'), $to->format('U')) : $this->client->zrange('eventsMt', 0, -1);

        foreach ($this->client->smembers('eventsAggregatesIndexById:'.$aggregate->id()) as $eventKey){
            if(in_array($eventKey, $eventMicroTimeArray)){
                $event = $this->client->hgetall($eventKey);
                $eventRepo = new RedisEventRepository($this->client);
                $results[] = $eventRepo->buildEvent($event);
            }
        }

        return $results;
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
     * @param $hydrateEvents
     *
     * @return Aggregate
     */
    public function buildAggregate(array $row, $hydrateEvents)
    {
        $aggregate = new Aggregate(
            new AggregateId($row['id']),
            $row['name']
        );

        if($hydrateEvents){
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
        }

        return $aggregate;
    }
}

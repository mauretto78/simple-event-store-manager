<?php
/**
 * This file is part of the EventStoreManager package.
 *
 * (c) Mauro Cassani<https://github.com/mauretto78>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SimpleEventStoreManager\Infrastructure\Persistence\Redis;

use Predis\Client;
use SimpleEventStoreManager\Domain\Model\Contracts\EventInterface;
use SimpleEventStoreManager\Domain\Model\Contracts\EventRepositoryInterface;
use SimpleEventStoreManager\Domain\Model\Event;
use SimpleEventStoreManager\Domain\Model\EventId;

class RedisEventRepository implements EventRepositoryInterface
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
     * @param EventId $id
     *
     * @return Event
     */
    public function byId(EventId $id)
    {
        $redisKey = 'event:'.$id;
        if ($row = $this->client->hgetall($redisKey)) {
            return $this->buildEvent($row);
        }

        return null;
    }

    /**
     * @param EventInterface $event
     *
     * @return mixed
     */
    public function save(EventInterface $event)
    {
        $eventId = (string) $event->id();
        $eventAggregate = $event->aggregate();
        $eventName = $event->name();
        $eventBody = $event->body();
        $eventOccurredOn = $event->occurredOn()->format('Y-m-d H:i:s.u');
        $redisKey = 'event:'.$eventId;

        $this->client->hset($redisKey, 'id', $eventId);
        $this->client->hset($redisKey, 'aggregate', serialize($eventAggregate));
        $this->client->hset($redisKey, 'name', $eventName);
        $this->client->hset($redisKey, 'body', $eventBody);
        $this->client->hset($redisKey, 'occurred_on', $eventOccurredOn);

        $this->client->sadd('eventsAggregatesIndexById:'.$event->aggregate()->id(), [$redisKey]);
        $this->client->sadd('eventsAggregatesIndexByName:'.$event->aggregate()->name(), [$redisKey]);
    }

    /**
     * @param array $row
     *
     * @return Event
     */
    public function buildEvent(array $row)
    {
        return new Event(
            new EventId($row['id']),
            unserialize($row['aggregate']),
            $row['name'],
            unserialize($row['body']),
            $row['occurred_on']
        );
    }
}

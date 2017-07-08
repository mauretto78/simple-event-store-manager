<?php
/**
 * This file is part of the EventStoreManager package.
 *
 * (c) Mauro Cassani<https://github.com/mauretto78>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SimpleEventStoreManager\Infrastructure\Persistence;

use Predis\Client;
use SimpleEventStoreManager\Domain\Model\Contracts\EventInterface;
use SimpleEventStoreManager\Domain\Model\Contracts\EventStoreInterface;
use SimpleEventStoreManager\Domain\Model\EventId;

class RedisEventStore extends AbstractEventStore implements EventStoreInterface
{
    /**
     * @var Client
     */
    private $client;

    /**
     * RedisEventStore constructor.
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param EventInterface $event
     *
     * @return mixed
     */
    public function store(EventInterface $event)
    {
        $eventId = (string) $event->id();
        $eventName = $event->name();
        $eventBody = $event->body();
        $eventOccurredOn = $event->occurredOn()->format('Y-m-d H:i:s');

        $this->client->hset($eventId, 'id', $eventId);
        $this->client->hset($eventId, 'name', $eventName);
        $this->client->hset($eventId, 'body', $eventBody);
        $this->client->hset($eventId, 'occurred_on', $eventOccurredOn);

        $this->client->zadd(
            'eventsMt',
            [
                $eventId => number_format(microtime(true), 0, '.', '')
            ]
        );
    }

    /**
     * @param EventId $eventId
     *
     * @return object
     */
    public function restore(EventId $eventId)
    {
        return (object) $this->client->hgetall($eventId);
    }

    /**
     * @return int
     */
    public function eventsCount()
    {
        return count($this->client->zrange('eventsMt', 0, -1));
    }

    /**
     * @param \DateTimeImmutable|null $from
     * @param \DateTimeImmutable|null $to
     *
     * @return array
     */
    public function eventsInRangeDate(\DateTimeImmutable $from = null, \DateTimeImmutable $to = null)
    {
        $events = [];
        $indexArray = ($from && $to) ? $this->client->zrangebyscore('eventsMt', $from->format('U'), $to->format('U')) : $this->client->zrange('eventsMt', 0, -1);

        foreach ($indexArray as $index) {
            $events[$index] = (object) $this->client->hgetall($index);
        }

        return $events;
    }
}

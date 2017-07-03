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

use MongoDB\Database;
use SimpleEventStoreManager\Domain\Model\Contracts\EventInterface;
use SimpleEventStoreManager\Domain\Model\Contracts\EventStoreInterface;
use SimpleEventStoreManager\Domain\Model\EventId;
use SimpleEventStoreManager\Domain\Model\Event;

class MongoEventStore implements EventStoreInterface
{
    /**
     * @var Database
     */
    private $mongo;

    /**
     * @var \MongoDB\Collection
     */
    private $collection;

    /**
     * MongoEventStore constructor.
     *
     * @param Database $mongo
     */
    public function __construct(Database $mongo)
    {
        $this->mongo = $mongo;
        $this->collection = $this->mongo->events;
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

        $this->collection->insertOne([
            'id' => $eventId,
            'name' => $eventName,
            'body' => $eventBody,
            'occurred_on' => $eventOccurredOn
        ]);
    }

    /**
     * @param EventId $eventId
     *
     * @return mixed
     */
    public function restore(EventId $eventId)
    {
        $document = $this->collection->findOne(['id' => $eventId->id()]);

        return $document;
    }

    /**
     * @return int
     */
    public function eventsCount()
    {
        return $this->collection->count();
    }

    /**
     * @param \DateTimeImmutable|null $from
     * @param \DateTimeImmutable|null $to
     *
     * @return array
     */
    public function eventsInRangeDate(\DateTimeImmutable $from = null, \DateTimeImmutable $to = null)
    {
        $filterArray = [];

        if ($from && $to) {
            $filterArray = [
                'occurred_on' => [
                    '$gte' => $from->format('Y-m-d H:i:s'),
                    '$lte' => $to->format('Y-m-d H:i:s'),
                ]
            ];
        }

        $document = $this->collection->find($filterArray);

        return $document->toArray();
    }
}

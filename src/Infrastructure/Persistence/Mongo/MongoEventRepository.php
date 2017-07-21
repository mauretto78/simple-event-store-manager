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
use MongoDB\Model\BSONDocument;
use SimpleEventStoreManager\Domain\Model\Aggregate;
use SimpleEventStoreManager\Domain\Model\AggregateId;
use SimpleEventStoreManager\Domain\Model\Contracts\EventInterface;
use SimpleEventStoreManager\Domain\Model\Contracts\EventRepositoryInterface;
use SimpleEventStoreManager\Domain\Model\Event;
use SimpleEventStoreManager\Domain\Model\EventId;

class MongoEventRepository extends AbstractAggregateRepository implements EventRepositoryInterface
{
    /**
     * @var Database
     */
    private $mongo;

    /**
     * @var \MongoDB\Collection
     */
    private $events;

    /**
     * MongoEventRepository constructor.
     *
     * @param Database $mongo
     */
    public function __construct(Database $mongo)
    {
        $this->mongo = $mongo;
        $this->events = $this->mongo->events;
    }

    /**
     * @param EventId $id
     *
     * @return Event
     */
    public function byId(EventId $id)
    {
        if($document = $this->events->findOne(['id' => $id->id()])){
            return $this->buildEvent($document);
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
        $eventName = $event->name();
        $eventBody = $event->body();
        $eventOccurredOn = $event->occurredOn()->format('Y-m-d H:i:s.u');

        $this->events->insertOne([
            'id' => $eventId,
            'aggregate' => [
                'id' => (string) $event->aggregate()->id(),
                'name' => $event->aggregate()->name()
            ],
            'name' => $eventName,
            'body' => $eventBody,
            'occurred_on' => $eventOccurredOn
        ]);
    }

    /**
     * @param BSONDocument $document
     *
     * @return Event
     */
    public function buildEvent(BSONDocument $document)
    {
        return new Event(
            new EventId($document->id),
            new Aggregate(
                new AggregateId($document->aggregate->id),
                $document->aggregate->name
            ),
            $document->name,
            unserialize($document->body),
            $document->occurred_on
        );
    }
}

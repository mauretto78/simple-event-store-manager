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
use MongoDB\Database;
use MongoDB\Model\BSONDocument;
use SimpleEventStoreManager\Domain\Model\Aggregate;
use SimpleEventStoreManager\Domain\Model\AggregateId;
use SimpleEventStoreManager\Domain\Model\Contracts\AggregateRepositoryInterface;
use SimpleEventStoreManager\Domain\Model\Contracts\EventInterface;
use SimpleEventStoreManager\Domain\Model\Event;
use SimpleEventStoreManager\Domain\Model\EventId;

class MongoAggregateRepository implements AggregateRepositoryInterface
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
     * @var \MongoDB\Collection
     */
    private $aggregates;

    /**
     * MongoEventRepository constructor.
     *
     * @param Database $mongo
     */
    public function __construct(Database $mongo)
    {
        $this->mongo = $mongo;
        $this->events = $this->mongo->events;
        $this->aggregates = $this->mongo->event_aggregates;
    }

    /**
     * @param AggregateId $id
     *
     * @return Aggregate
     */
    public function byId(AggregateId $id)
    {
        if($document = $this->aggregates->findOne(['id' => $id->id()])){
            return $this->buildAggregate($document);
        }

        return null;
    }

    /**
     * @param $name
     *
     * @return Aggregate
     */
    public function byName($name)
    {
        if($document = $this->aggregates->findOne(['name' => (new Slugify())->slugify($name)])){
            return $this->buildAggregate($document);
        }

        return null;
    }

    /**
     * @return int
     */
    public function eventsCount(Aggregate $aggregate)
    {
        return $this->events->count(['aggregate.id' => (string) $aggregate->id()]);
    }

    /**
     * @param $name
     * @return bool
     */
    public function exists($name)
    {
        return ($this->aggregates->findOne(['name' => (new Slugify())->slugify($name)])) ? true : false;
    }

    /**
     * @param Aggregate $aggregate
     * @return mixed
     */
    public function save(Aggregate $aggregate)
    {
        if(null === $this->byName($aggregate->name())){
            $this->aggregates->insertOne([
                'id' => (string) $aggregate->id(),
                'name' => $aggregate->name()
            ]);
        }

        /** @var Event $event */
        foreach ($aggregate->events() as $event){
            $this->saveEvent($event, $aggregate);
        }
    }

    /**
     * @param EventInterface $event
     *
     * @return mixed
     */
    private function saveEvent(EventInterface $event, Aggregate $aggregate)
    {
        $eventId = (string) $event->id();
        $eventName = $event->name();
        $eventBody = $event->body();
        $eventOccurredOn = $event->occurredOn()->format('Y-m-d H:i:s.u');

        $this->events->insertOne([
            'id' => $eventId,
            'aggregate' => [
                'id' => (string) $aggregate->id(),
                'name' => $aggregate->name()
            ],
            'name' => $eventName,
            'body' => $eventBody,
            'occurred_on' => $eventOccurredOn
        ]);
    }

    /**
     * @param BSONDocument $document
     *
     * @return Aggregate
     */
    private function buildAggregate(BSONDocument $document)
    {
        $aggregate = new Aggregate(
            new AggregateId($document->id),
            $document->name
        );

        $events = $this->events->find(['aggregate.id' => (string) $aggregate->id()])->toArray();
        foreach ($events as $event){
            $aggregate->addEvent(
                new Event(
                    new EventId($event->id),
                    $event->name,
                    unserialize($event->body),
                    $event->occurred_on
                )
            );
        }

        return $aggregate;
    }
}

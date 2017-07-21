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
use SimpleEventStoreManager\Domain\Model\Event;
use SimpleEventStoreManager\Domain\Model\EventId;

class MongoAggregateRepository extends AbstractAggregateRepository implements AggregateRepositoryInterface
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
     * @param bool $hydrateEvents
     *
     * @return Aggregate
     */
    public function byId(AggregateId $id, $hydrateEvents = true)
    {
        if($document = $this->aggregates->findOne(['id' => $id->id()])){
            return $this->buildAggregate($document, $hydrateEvents);
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
        if($document = $this->aggregates->findOne(['name' => (new Slugify())->slugify($name)])){
            return $this->buildAggregate($document, $hydrateEvents);
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
     * @param Aggregate $aggregate
     * @param array $parameters
     *
     * @return Event[]
     */
    public function queryEvents(Aggregate $aggregate, array $parameters = [])
    {
        $filterArray = ['aggregate.id' => (string) $aggregate->id()];
        $filterArray['occurred_on'] = [];

        if (isset($parameters['from'])) {
            $from = new \DateTimeImmutable($parameters['from']);
            array_push($filterArray['occurred_on'], ['$gte' => $from->format('Y-m-d H:i:s.u')]);
        }

        if (isset($parameters['to'])) {
            $to = new \DateTimeImmutable($parameters['to']);
            array_push($filterArray['occurred_on'], ['$lte' => $to->format('Y-m-d H:i:s.u')]);
        }

        $document = $this->events->find([]);

        $results = [];
        foreach ($document->toArray() as $event){
            $eventRepo = new MongoEventRepository($this->mongo);
            $results[] = $eventRepo->buildEvent($event);
        }

        return $results;
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
            $eventRepo = new MongoEventRepository($this->mongo);
            $eventRepo->save($event);
        }
    }

    /**
     * @param BSONDocument $document
     *
     * @return Aggregate
     */
    public function buildAggregate(BSONDocument $document, $hydrateEvents)
    {
        $aggregate = new Aggregate(
            new AggregateId($document->id),
            $document->name
        );

        if($hydrateEvents){
            $events = $this->events->find(['aggregate.id' => (string) $aggregate->id()])->toArray();
            foreach ($events as $event){
                $aggregate->addEvent(
                    new Event(
                        new EventId($event->id),
                        $aggregate,
                        $event->name,
                        unserialize($event->body),
                        $event->occurred_on
                    )
                );
            }
        }

        return $aggregate;
    }
}

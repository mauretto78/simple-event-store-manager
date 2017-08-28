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

use MongoDB\Database;
use MongoDB\Model\BSONDocument;
use SimpleEventStoreManager\Domain\Model\EventAggregate;
use SimpleEventStoreManager\Domain\Model\EventAggregateId;
use SimpleEventStoreManager\Domain\Model\Contracts\EventAggregateRepositoryInterface;
use SimpleEventStoreManager\Domain\Model\Contracts\EventInterface;
use SimpleEventStoreManager\Domain\Model\Event;
use SimpleEventStoreManager\Domain\Model\EventId;
use SimpleEventStoreManager\Infrastructure\Services\HashGeneratorService;

class MongoEventAggregateRepository implements EventAggregateRepositoryInterface
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
     * MongoEventAggregateRepository constructor.
     * @param Database $mongo
     */
    public function __construct(Database $mongo)
    {
        $this->mongo = $mongo;
        $this->events = $this->mongo->events;
        $this->aggregates = $this->mongo->event_aggregates;
    }

    /**
     * @param EventAggregateId $eventAggregateId
     *
     * @return EventAggregate
     */
    public function byId(EventAggregateId $eventAggregateId, $returnType = self::RETURN_AS_ARRAY)
    {
        if($document = $this->aggregates->findOne(['id' => (string) $eventAggregateId])){
            return $this->buildAggregate($document, $returnType);
        }

        return null;
    }

    /**
     * @param $name
     * @param int $returnType
     *
     * @return array|null|EventAggregate
     */
    public function byName($name, $returnType = self::RETURN_AS_ARRAY)
    {
        if($document = $this->aggregates->findOne(['name' => HashGeneratorService::computeStringHash($name)])){
            return $this->buildAggregate($document, $returnType);
        }

        return null;
    }

    /**
     * @param $document
     * @return EventAggregate|array
     */
    private function buildAggregate($document, $returnType)
    {
        if($returnType === self::RETURN_AS_ARRAY){
            return $this->buildAggregateAsArray($document);
        }

        return $this->buildAggregateAsObject($document);
    }

    /**
     * @return int
     */
    public function eventsCount(EventAggregate $aggregate)
    {
        return $this->events->count(['aggregate.id' => (string) $aggregate->id()]);
    }

    /**
     * @param $name
     * @return bool
     */
    public function exists($name)
    {
        return ($this->aggregates->findOne(['name' => HashGeneratorService::computeStringHash($name)])) ? true : false;
    }

    /**
     * @param EventAggregate $aggregate
     * @return mixed
     */
    public function save(EventAggregate $aggregate)
    {
        if(false === $this->exists($aggregate->name())){
            $this->aggregates->insertOne([
                'id' => (string) $aggregate->id(),
                'name' => $aggregate->name()
            ]);
        }

        /** @var Event $event */
        foreach ($aggregate->events() as $event){
            if(false === $this->existsEvent($event)){
                $this->saveEvent($event, $aggregate);
            }
        }
    }

    /**
     * @param EventInterface $event
     *
     * @return mixed
     */
    private function saveEvent(EventInterface $event, EventAggregate $aggregate)
    {
        $eventId = (string) $event->id();
        $eventName = $event->name();
        $eventBody = serialize($event->body());
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
     * @param EventInterface $event
     *
     * @return bool
     */
    private function existsEvent(EventInterface $event)
    {
        return ($this->events->findOne(['id' => (string) $event->id()])) ? true : false;
    }

    /**
     * @param BSONDocument $document
     *
     * @return EventAggregate
     */
    private function buildAggregateAsArray(BSONDocument $document)
    {
        $returnArray['id'] = (string) $document->id;
        $returnArray['name'] = $document->name;

        $events = $this->events->find(['aggregate.id' => (string) $document->id])->toArray();
        foreach ($events as $event){
            $returnArray['events'][] = [
                'id' => (string) $event->id,
                'name' => $event->name,
                'body' => unserialize($event->body),
                'occurred_on' => $event->occurred_on,
            ];
        }

        return $returnArray;
    }

    /**
     * @param BSONDocument $document
     *
     * @return EventAggregate
     */
    private function buildAggregateAsObject(BSONDocument $document)
    {
        $aggregate = new EventAggregate(
            $document->name,
            new EventAggregateId($document->id)
        );

        $events = $this->events->find(['aggregate.id' => (string) $aggregate->id()])->toArray();
        foreach ($events as $event){
            $aggregate->addEvent(
                new Event(
                    $event->name,
                    unserialize($event->body),
                    new EventId($event->id),
                    $event->occurred_on
                )
            );
        }

        return $aggregate;
    }
}

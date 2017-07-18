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
use SimpleEventStoreManager\Domain\EventStore\Contracts\EventRepositoryInterface;
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
        parent::__construct();
        $this->mongo = $mongo;
        $this->events = $this->mongo->events;
        $this->aggregates = $this->mongo->event_aggregates;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function findAggregateByName($name)
    {
        $document = $this->aggregates->findOne(['name' => $this->slugify->slugify($name)]);

        return $document;
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

        if(!$aggregate = $this->findAggregateByName($event->aggregate()->name())){
            $aggregate = $this->aggregates->insertOne([
                'id' => (string) $event->aggregate()->id(),
                'name' => $event->aggregate()->name()
            ]);
        }

        $this->events->insertOne([
            'id' => $eventId,
            'aggregate' => $aggregate,
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
        $document = $this->events->findOne(['id' => $eventId->id()]);

        return $document;
    }

    /**
     * @return int
     */
    public function eventsCount()
    {
        return $this->events->count();
    }

    /**
     * @param array $parameters
     * @return mixed
     */
    public function query(array $parameters = [])
    {
        $filterArray = [];

        if (isset($parameters['from']) && isset($parameters['to'])) {
            $from = new \DateTimeImmutable($parameters['from']);
            $to = new \DateTimeImmutable($parameters['to']);

            $filterArray = [
                'occurred_on' => [
                    '$gte' => $from->format('Y-m-d H:i:s'),
                    '$lte' => $to->format('Y-m-d H:i:s'),
                ]
            ];
        }

        if(isset($parameters['aggregate_name'])){
            $filterArray = [
                'aggregate.name' => $this->slugify->slugify($parameters['aggregate_name'])
            ];
        }

        $document = $this->events->find($filterArray);

        return $document->toArray();
    }
}

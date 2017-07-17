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

use Cocur\Slugify\Slugify;
use SimpleEventStoreManager\Domain\Model\AggregateId;
use SimpleEventStoreManager\Domain\Model\Contracts\EventInterface;
use SimpleEventStoreManager\Domain\EventStore\Contracts\EventStoreInterface;
use SimpleEventStoreManager\Domain\Model\Event;
use SimpleEventStoreManager\Domain\Model\EventId;

class InMemoryEventStore extends AbstractEventStore implements EventStoreInterface
{
    /**
     * @var array
     */
    private $aggregates;

    /**
     * @var array
     */
    private $events;

    /**
     * InMemoryEventStore constructor.
     */
    public function __construct()
    {
        $this->aggregates = [];
        $this->events = [];
    }

    /**
     * @param AggregateId $aggregateId
     * @return mixed
     */
    public function findAggregateById(AggregateId $aggregateId)
    {
        return isset($this->aggregates[(string) $aggregateId]) ? $this->aggregates[(string) $aggregateId] : null;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function findAggregateByName($name)
    {
        $sluggify = new Slugify();
        $aggregateName = $sluggify->slugify($name);

        foreach ($this->aggregates as $aggregate){
            if($aggregate->name === $aggregateName){
                return $aggregate;
            }
        }
    }

    /**
     * @param EventInterface $event
     *
     * @return mixed
     */
    public function store(EventInterface $event)
    {
        $eventId = $event->id();
        $eventAggregateId = $event->aggregate()->id();
        $eventAggregateName = $event->aggregate()->name();
        $eventName = $event->name();
        $eventBody = $event->body();
        $eventOccurredOn = $event->occurredOn()->format('Y-m-d H:i:s');

        if(null === $this->findAggregateById($eventAggregateId)){
            $this->aggregates[(string) $eventAggregateId] = (object) [
                'id' => $eventAggregateId,
                'name' => $eventAggregateName,
            ];
        }

        $this->events[(string) $eventId] = (object) [
            'id' => $eventId,
            'aggregate_id' => $eventAggregateId,
            'name' => $eventName,
            'body' => $eventBody,
            'occurred_on' => $eventOccurredOn,
        ];

    }

    /**
     * @param EventId $eventId
     *
     * @return mixed
     */
    public function restore(EventId $eventId)
    {
        return isset($this->events[(string) $eventId]) ? $this->events[(string) $eventId] : null;
    }

    /**
     * @return int
     */
    public function eventsCount()
    {
        return count($this->events);
    }

    /**
     * @param array $parameters
     * @return mixed
     */
    public function query(array $parameters = [])
    {
        return $this->events;
    }
}

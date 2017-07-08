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

use SimpleEventStoreManager\Domain\Model\Contracts\EventInterface;
use SimpleEventStoreManager\Domain\Model\Contracts\EventStoreInterface;
use SimpleEventStoreManager\Domain\Model\EventId;

class InMemoryEventStore extends AbstractEventStore implements EventStoreInterface
{
    /**
     * @var array
     */
    private $events;

    /**
     * InMemoryEventStore constructor.
     */
    public function __construct()
    {
        $this->events = [];
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

        $this->events[$eventId] = (object) [
            'id' => $eventId,
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
        return (isset($this->events[$eventId->id()])) ? $this->events[$eventId->id()] : null;
    }

    /**
     * @return int
     */
    public function eventsCount()
    {
        return count($this->events);
    }

    /**
     * @param \DateTimeImmutable|null $from
     * @param \DateTimeImmutable|null $to
     *
     * @return array
     */
    public function eventsInRangeDate(\DateTimeImmutable $from = null, \DateTimeImmutable $to = null)
    {
        return $this->events;
    }
}

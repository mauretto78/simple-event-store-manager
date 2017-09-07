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

use SimpleEventStoreManager\Domain\Model\Contracts\EventInterface;
use SimpleEventStoreManager\Domain\Model\Contracts\EventStoreRepositoryInterface;
use SimpleEventStoreManager\Domain\Model\Event;
use SimpleEventStoreManager\Domain\Model\EventUuid;

class InMemoryEventStoreRepository implements EventStoreRepositoryInterface
{
    /**
     * @var array
     */
    private $events;

    /**
     * InMemoryEventRepository constructor.
     */
    public function __construct()
    {
        $this->events = [];
    }

    /**
     * @param EventUuid $eventUuid
     * @param int $returnType
     *
     * @return array|mixed|null
     */
    public function byUuid(EventUuid $eventUuid, $returnType = self::RETURN_AS_ARRAY)
    {
        return (isset($this->events[(string) $eventUuid])) ? $this->buildEventAggregate($eventUuid, $returnType) : null;
    }

    /**
     * @param EventUuid $eventUuid
     * @param $returnType
     *
     * @return array|mixed
     */
    private function buildEventAggregate(EventUuid $eventUuid, $returnType)
    {
        if ($returnType === self::RETURN_AS_ARRAY) {
            return $this->buildEventAggregateAsArray($eventUuid);
        }

        return $this->buildEventAggregateAsObject($eventUuid);
    }

    /**
     * @param EventUuid $eventUuid
     *
     * @return array
     */
    private function buildEventAggregateAsArray(EventUuid $eventUuid)
    {
        $returnArray = [];

        /** @var Event $event */
        foreach ($this->events[(string) $eventUuid] as $event) {
            $returnArray[] = [
                'uuid' => $event->uuid(),
                'version' => $event->version(),
                'type' => $event->type(),
                'body' => $event->body(),
                'occurred_on' => $event->occurredOn(),
            ];
        }

        return $returnArray;
    }

    /**
     * @param EventUuid $eventUuid
     *
     * @return mixed
     */
    private function buildEventAggregateAsObject(EventUuid $eventUuid)
    {
        return $this->events[(string) $eventUuid];
    }

    /**
     * @param EventUuid $eventUuid
     *
     * @return int
     */
    public function count(EventUuid $eventUuid)
    {
        return (isset($this->events[(string) $eventUuid])) ? count($this->events[(string) $eventUuid]) : 0;
    }

    /**
     * @param EventInterface $event
     *
     * @return mixed
     */
    public function save(EventInterface $event)
    {
        $this->events[(string) $event->uuid()][] = new Event(
            $event->type(),
            $event->body(),
            $event->uuid(),
            $this->count($event->uuid()),
            $event->occurredOn()->format('Y-m-d H:i:s.u')
        );
    }
}

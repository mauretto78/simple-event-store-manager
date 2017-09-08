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
use SimpleEventStoreManager\Domain\Model\AggregateUuid;

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
     * @param AggregateUuid $uuid
     * @param int $returnType
     *
     * @return array|mixed|null
     */
    public function byUuid(AggregateUuid $uuid, $returnType = self::RETURN_AS_ARRAY)
    {
        return (isset($this->events[(string) $uuid])) ? $this->buildEventAggregate($uuid, $returnType) : null;
    }

    /**
     * @param AggregateUuid $uuid
     * @param $returnType
     *
     * @return array|mixed
     */
    private function buildEventAggregate(AggregateUuid $uuid, $returnType)
    {
        if ($returnType === self::RETURN_AS_ARRAY) {
            return $this->buildEventAggregateAsArray($uuid);
        }

        return $this->buildEventAggregateAsObject($uuid);
    }

    /**
     * @param AggregateUuid $uuid
     *
     * @return array
     */
    private function buildEventAggregateAsArray(AggregateUuid $uuid)
    {
        $returnArray = [];

        /** @var Event $event */
        foreach ($this->events[(string) $uuid] as $event) {
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
     * @param AggregateUuid $uuid
     *
     * @return mixed
     */
    private function buildEventAggregateAsObject(AggregateUuid $uuid)
    {
        return $this->events[(string) $uuid];
    }

    /**
     * @param AggregateUuid $uuid
     *
     * @return int
     */
    public function count(AggregateUuid $uuid)
    {
        return (isset($this->events[(string) $uuid])) ? count($this->events[(string) $uuid]) : 0;
    }

    /**
     * @param EventInterface $event
     *
     * @return mixed
     */
    public function save(EventInterface $event)
    {
        $this->events[(string) $event->uuid()][] = new Event(
            $event->uuid(),
            $event->type(),
            $event->body(),
            $this->count($event->uuid()),
            $event->occurredOn()->format('Y-m-d H:i:s.u')
        );
    }
}

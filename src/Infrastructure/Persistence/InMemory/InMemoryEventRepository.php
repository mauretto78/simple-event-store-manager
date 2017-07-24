<?php
/**
 * This file is part of the EventStoreManager package.
 *
 * (c) Mauro Cassani<https://github.com/mauretto78>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SimpleEventStoreManager\Infrastructure\Persistence\InMemory;

use SimpleEventStoreManager\Domain\Model\Contracts\EventInterface;
use SimpleEventStoreManager\Domain\Model\Contracts\EventRepositoryInterface;
use SimpleEventStoreManager\Domain\Model\Event;
use SimpleEventStoreManager\Domain\Model\EventId;

class InMemoryEventRepository implements EventRepositoryInterface
{
    /**
     * @var array
     */
    private $events;

    /**
     * @param EventId $id
     *
     * @return Event
     */
    public function byId(EventId $id)
    {
        return (isset($this->events[(string) $id])) ? $this->events[(string) $id] : null;
    }

    /**
     * @param EventInterface $event
     *
     * @return mixed
     */
    public function save(EventInterface $event)
    {
        $event->aggregate()->addEvent($event);

        $this->events[(string) $event->id()] = $event;
    }
}

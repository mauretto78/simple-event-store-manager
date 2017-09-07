<?php
/**
 * This file is part of the Simple EventStore Manager package.
 *
 * (c) Mauro Cassani<https://github.com/mauretto78>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SimpleEventStoreManager\Domain\EventRecorder;

use SimpleEventStoreManager\Domain\Model\Contracts\EventInterface;

trait EventRecorderCapabilities
{
    /**
     * @var EventInterface[]
     */
    private $recordedEvents;

    /**
     * @return mixed
     */
    public function clear()
    {
        $this->recordedEvents = [];
    }

    /**
     * @param EventInterface $event
     *
     * @return mixed
     */
    public function record(EventInterface $event)
    {
        $eventUuid = $event->uuid();
        $this->recordedEvents[(string) $eventUuid] = $event;
    }

    /**
     * @return EventInterface[]
     */
    public function releaseEvents()
    {
        $events = $this->recordedEvents;
        $this->clear();

        return $events;
    }
}

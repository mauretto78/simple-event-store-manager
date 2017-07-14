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

use SimpleEventStoreManager\Domain\Model\Event;
use SimpleEventStoreManager\Domain\Model\EventId;
use SimpleEventStoreManager\Domain\EventRecorder\Contracts\EventRecorderInterface;

class EventRecorder implements EventRecorderInterface
{
    /**
     * @var Event[]
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
     * @param EventId $eventId
     * @return mixed
     */
    public function delete(EventId $eventId)
    {
        unset($this->recordedEvents[(string) $eventId]);
    }

    /**
     * @param Event $event
     *
     * @return mixed
     */
    public function record(Event $event)
    {
        $eventId = $event->id();
        $this->recordedEvents[(string) $eventId] = $event;
    }

    /**
     * @return Event[]
     */
    public function releaseEvents()
    {
        $events = $this->recordedEvents;
        $this->clear();

        return $events;
    }
}

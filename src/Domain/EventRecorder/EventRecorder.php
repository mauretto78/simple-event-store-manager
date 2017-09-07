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
use SimpleEventStoreManager\Domain\Model\EventUuid;
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
     * @param EventUuid $eventUuid
     * @return mixed
     */
    public function delete(EventUuid $eventUuid)
    {
        unset($this->recordedEvents[(string) $eventUuid]);
    }

    /**
     * @param Event $event
     *
     * @return mixed
     */
    public function record(Event $event)
    {
        $eventUuid = $event->uuid();
        $this->recordedEvents[(string) $eventUuid] = $event;
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

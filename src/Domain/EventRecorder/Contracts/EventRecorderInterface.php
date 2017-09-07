<?php
/**
 * This file is part of the Simple EventStore Manager package.
 *
 * (c) Mauro Cassani<https://github.com/mauretto78>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SimpleEventStoreManager\Domain\EventRecorder\Contracts;

use SimpleEventStoreManager\Domain\Model\Event;
use SimpleEventStoreManager\Domain\Model\EventUuid;

interface EventRecorderInterface
{
    /**
     * @return mixed
     */
    public function clear();

    /**
     * @param EventUuid $eventUuid
     * @return mixed
     */
    public function delete(EventUuid $eventUuid);

    /**
     * @param Event $event
     *
     * @return mixed
     */
    public function record(Event $event);

    /**
     * @return Event[]
     */
    public function releaseEvents();
}

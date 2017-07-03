<?php
/**
 * This file is part of the EventStoreManager package.
 *
 * (c) Mauro Cassani<https://github.com/mauretto78>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SimpleEventStoreManager\Domain\Model\Contracts;

use SimpleEventStoreManager\Domain\Model\Event;
use SimpleEventStoreManager\Domain\Model\EventId;

interface EventStoreInterface
{
    /**
     * @param EventInterface $event
     * @return mixed
     */
    public function store(EventInterface $event);

    /**
     * @param EventId $eventId
     * @return mixed
     */
    public function restore(EventId $eventId);

    /**
     * @return int
     */
    public function eventsCount();

    /**
     * @param \DateTimeImmutable|null $from
     * @param \DateTimeImmutable|null $to
     * @return mixed
     */
    public function eventsInRangeDate(\DateTimeImmutable $from = null, \DateTimeImmutable $to = null);
}

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

abstract class AbstractEventStore implements EventStoreInterface
{
    /**
     * @param EventInterface $event
     * @return mixed
     */
    public function store(EventInterface $event)
    {
    }

    /**
     * @param EventId $eventId
     * @return mixed
     */
    public function restore(EventId $eventId)
    {
    }

    /**
     * @return int
     */
    public function eventsCount()
    {
    }

    /**
     * @param \DateTimeImmutable|null $from
     * @param \DateTimeImmutable|null $to
     * @return mixed
     */
    public function eventsInRangeDate(\DateTimeImmutable $from = null, \DateTimeImmutable $to = null)
    {
    }

    /**
     * @param int $page
     * @param int $maxPerPage
     * @return mixed
     */
    public function all($page = 1, $maxPerPage = 25)
    {
        $events = $this->eventsInRangeDate();

        return array_slice($events, ($page - 1) * $maxPerPage, $maxPerPage);
    }
}

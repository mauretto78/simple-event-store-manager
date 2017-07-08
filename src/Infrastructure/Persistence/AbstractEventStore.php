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

abstract class AbstractEventStore
{
    /**
     * @param int $page
     * @param int $maxPerPage
     * @return mixed
     */
    public function paginate($page = 1, $maxPerPage = 25)
    {
        $events = $this->eventsInRangeDate();

        return array_slice($events, ($page - 1) * $maxPerPage, $maxPerPage);
    }
}

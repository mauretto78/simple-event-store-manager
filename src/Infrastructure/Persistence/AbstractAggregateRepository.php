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

use SimpleEventStoreManager\Domain\Model\Contracts\EventStoreInterface;

abstract class AbstractAggregateRepository
{
    /**
     * @param int $page
     * @param int $maxPerPage
     * @return mixed
     */
    public function paginate($page = 1, $maxPerPage = 25)
    {
        $events = $this->query();

        return array_slice($events, ($page - 1) * $maxPerPage, $maxPerPage);
    }
}

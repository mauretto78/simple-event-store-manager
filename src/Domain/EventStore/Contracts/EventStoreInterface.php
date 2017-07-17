<?php
/**
 * This file is part of the Simple EventStore Manager package.
 *
 * (c) Mauro Cassani<https://github.com/mauretto78>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SimpleEventStoreManager\Domain\EventStore\Contracts;

use SimpleEventStoreManager\Domain\Model\AggregateId;
use SimpleEventStoreManager\Domain\Model\Contracts\EventInterface;
use SimpleEventStoreManager\Domain\Model\EventId;

interface EventStoreInterface
{
    /**
     * @param AggregateId $aggregateId
     * @return mixed
     */
    public function findAggregateById(AggregateId $aggregateId);

    /**
     * @param string $name
     * @return mixed
     */
    public function findAggregateByName($name);

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
     * @param array $parameters
     * @return mixed
     */
    public function query(array $parameters = []);

    /**
     * @param int $page
     * @param int $maxPerPage
     * @return mixed
     */
    public function paginate($page = 1, $maxPerPage = 25);
}

<?php
/**
 * This file is part of the Simple EventStore Manager package.
 *
 * (c) Mauro Cassani<https://github.com/mauretto78>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SimpleEventStoreManager\Domain\Model\Contracts;

use SimpleEventStoreManager\Domain\Model\Aggregate;
use SimpleEventStoreManager\Domain\Model\AggregateId;

interface AggregateRepositoryInterface
{
    /**
     * @param AggregateId $id
     * @param bool $hydrateEvents
     * @return Aggregate
     */
    public function byId(AggregateId $id, $hydrateEvents = true);

    /**
     * @param $name
     * @param bool $hydrateEvents
     * @return Aggregate
     */
    public function byName($name, $hydrateEvents = true);

    /**
     * @return int
     */
    public function eventsCount(Aggregate $aggregate);

    /**
     * @param Aggregate $aggregate
     * @param array $parameters
     * @return mixed
     */
    public function queryEvents(Aggregate $aggregate, array $parameters = []);

    /**
     * @param Aggregate $aggregate
     * @return mixed
     */
    public function save(Aggregate $aggregate);
}

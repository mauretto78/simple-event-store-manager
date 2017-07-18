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
     * @return mixed
     */
    public function byId(AggregateId $id);

    /**
     * @param string $name
     * @return mixed
     */
    public function byName($name);

    /**
     * @return int
     */
    public function eventsCount(AggregateId $id);

    /**
     * @param array $parameters
     * @return mixed
     */
    public function query(array $parameters = []);

    /**
     * @param Aggregate $aggregate
     * @return mixed
     */
    public function save(Aggregate $aggregate);
}

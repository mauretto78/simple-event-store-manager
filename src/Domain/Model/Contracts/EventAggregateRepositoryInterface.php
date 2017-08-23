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

use SimpleEventStoreManager\Domain\Model\EventAggregate;
use SimpleEventStoreManager\Domain\Model\EventAggregateId;

interface EventAggregateRepositoryInterface
{
    const RETURN_AS_ARRAY = 1;
    const RETURN_AS_OBJECT = 2;

    /**
     * @param EventAggregateId $eventAggregateId
     * @param int $returnType
     *
     * @return EventAggregate
     */
    public function byId(EventAggregateId $eventAggregateId, $returnType = self::RETURN_AS_ARRAY);

    /**
     * @param $name
     * @param int $returnType
     *
     * @return EventAggregate
     */
    public function byName($name, $returnType = self::RETURN_AS_ARRAY);

    /**
     * @return int
     */
    public function eventsCount(EventAggregate $aggregate);

    /**
     * @param $name
     *
     * @return bool
     */
    public function exists($name);

    /**
     * @param EventAggregate $aggregate
     *
     * @return mixed
     */
    public function save(EventAggregate $aggregate);
}

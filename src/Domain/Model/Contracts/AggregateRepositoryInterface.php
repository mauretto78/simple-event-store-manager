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
    const RETURN_AS_ARRAY = 1;
    const RETURN_AS_OBJECT = 2;

    /**
     * @param AggregateId $id
     * @param int $returnType
     *
     * @return Aggregate
     */
    public function byId(AggregateId $id, $returnType = self::RETURN_AS_ARRAY);

    /**
     * @param $name
     * @param int $returnType
     *
     * @return Aggregate
     */
    public function byName($name, $returnType = self::RETURN_AS_ARRAY);

    /**
     * @return int
     */
    public function eventsCount(Aggregate $aggregate);

    /**
     * @param $name
     *
     * @return bool
     */
    public function exists($name);

    /**
     * @param Aggregate $aggregate
     *
     * @return mixed
     */
    public function save(Aggregate $aggregate);
}

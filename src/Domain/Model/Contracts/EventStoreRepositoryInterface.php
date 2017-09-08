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
use SimpleEventStoreManager\Domain\Model\AggregateUuid;

interface EventStoreRepositoryInterface
{
    const RETURN_AS_OBJECT = 1;
    const RETURN_AS_ARRAY = 2;

    public function byUuid(AggregateUuid $eventUuid, $returnType = self::RETURN_AS_ARRAY);

    public function count(AggregateUuid $eventUuid);

    public function save(EventInterface $event);
}

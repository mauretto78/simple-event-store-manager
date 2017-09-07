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
use SimpleEventStoreManager\Domain\Model\EventUuid;

interface EventStoreRepositoryInterface
{
    const RETURN_AS_OBJECT = 0;
    const RETURN_AS_ARRAY = 1;

    public function byUuid(EventUuid $eventUuid, $returnType = self::RETURN_AS_ARRAY);

    public function count(EventUuid $eventUuid);

    public function save(EventInterface $event);
}

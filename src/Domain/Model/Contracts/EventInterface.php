<?php
/**
 * This file is part of the EventStoreManager package.
 *
 * (c) Mauro Cassani<https://github.com/mauretto78>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SimpleEventStoreManager\Domain\Model\Contracts;

use SimpleEventStoreManager\Domain\Model\Aggregate;
use SimpleEventStoreManager\Domain\Model\EventId;

interface EventInterface
{
    /**
     * @return EventId
     */
    public function id();

    /**
     * @return string
     */
    public function name();

    /**
     * @return mixed
     */
    public function body();

    /**
     * @return \DateTimeImmutable
     */
    public function occurredOn();
}

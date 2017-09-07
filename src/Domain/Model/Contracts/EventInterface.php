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

use SimpleEventStoreManager\Domain\Model\EventAggregate;
use SimpleEventStoreManager\Domain\Model\EventUuid;

interface EventInterface
{
    /**
     * @return EventUuid
     */
    public function uuid();

    /**
     * @return string
     */
    public function type();

    /**
     * @return int
     */
    public function version();

    /**
     * @return mixed
     */
    public function body();

    /**
     * @return \DateTimeImmutable
     */
    public function occurredOn();
}

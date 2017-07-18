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

use SimpleEventStoreManager\Domain\Model\Event;
use SimpleEventStoreManager\Domain\Model\EventId;

interface EventRepositoryInterface
{
    /**
     * @param EventId $id
     * @return mixed
     */
    public function byId(EventId $id);

    /**
     * @param Event $event
     * @return mixed
     */
    public function save(Event $event);
}

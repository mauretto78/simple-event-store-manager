<?php
/**
 * This file is part of the Simple EventStore Manager package.
 *
 * (c) Mauro Cassani<https://github.com/mauretto78>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SimpleEventStoreManager\Infrastructure\DataTransformers\Representations;

use SimpleEventStoreManager\Domain\Model\Contracts\EventInterface;

class EventObjectRepresentation
{
    private $id;
    private $name;
    private $body;
    private $occurred_on;

    public function __construct(EventInterface $event)
    {
        $this->id = $event->id();
        $this->name = $event->name();
        $this->body = unserialize($event->body());
        $this->occurred_on = $event->occurredOn();
    }
}

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
    private $uuid;
    private $type;
    private $body;
    private $occurred_on;

    /**
     * EventObjectRepresentation constructor.
     * @param $event
     */
    public function __construct($event)
    {
        if (is_array($event)) {
            $this->fromArray($event);
        }

        if ($event instanceof EventInterface) {
            $this->fromEventInterface($event);
        }
    }

    /**
     * @param array $event
     */
    private function fromArray(array $event)
    {
        $this->uuid = $event['uuid'];
        $this->version = $event['version'];
        $this->type = $event['type'];
        $this->body = $event['body'];
        $this->occurred_on = $event['occurred_on'];
    }

    /**
     * @param EventInterface $event
     */
    private function fromEventInterface(EventInterface $event)
    {
        $this->uuid = $event->uuid();
        $this->version = $event->version();
        $this->type = $event->type();
        $this->body = $event->body();
        $this->occurred_on = $event->occurredOn();
    }
}

<?php
/**
 * This file is part of the EventStoreManager package.
 *
 * (c) Mauro Cassani<https://github.com/mauretto78>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SimpleEventStoreManager\Domain\Model;

use SimpleEventStoreManager\Domain\Model\Contracts\EventInterface;

class Event implements EventInterface
{
    /**
     * @var EventId
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var mixed
     */
    private $body;

    /**
     * @var \DateTimeImmutable
     */
    private $occurred_on;

    /**
     * Event constructor.
     *
     * @param $name
     * @param $body
     * @param EventId|null $eventId
     * @param null $occurred_on
     */
    public function __construct(
        $name,
        $body,
        EventId $eventId = null,
        $occurred_on = null
    ) {
        $this->id = ($eventId) ? $eventId : new EventId();
        $this->name = $name;
        $this->body = $body;
        $this->occurred_on = ($occurred_on) ? new \DateTimeImmutable($occurred_on) : new \DateTimeImmutable();
    }

    /**
     * @return EventId
     */
    public function id()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function body()
    {
        return $this->body;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function occurredOn()
    {
        return $this->occurred_on;
    }
}

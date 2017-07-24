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
     * @param EventId $id
     * @param $name
     * @param $body
     * @param $occurred_on
     */
    public function __construct(
        EventId $id,
        $name,
        $body,
        $occurred_on = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->body = serialize($body);
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

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
     * @var AggregateUuid
     */
    private $uuid;

    /**
     * @var string
     */
    private $type;

    /**
     * @var int
     */
    private $version;

    /**
     * @var string
     */
    private $payload;

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
     * @param AggregateUuid $eventId
     * @param $type
     * @param $body
     * @param null $version
     * @param null $occurred_on
     */
    public function __construct(
        AggregateUuid $eventId,
        $type,
        $body,
        $version = null,
        $occurred_on = null
    ) {
        $this->uuid = $eventId;
        $this->type = $type;
        $this->body = $body;
        $this->payload = get_class($this);
        $this->version = ($version) ?: 0;
        $this->occurred_on = ($occurred_on) ? new \DateTimeImmutable($occurred_on) : new \DateTimeImmutable();
    }

    /**
     * @return AggregateUuid
     */
    public function uuid()
    {
        return $this->uuid;
    }

    /**
     * @return string
     */
    public function type()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function version()
    {
        return $this->version;
    }

    /**
     * @return string
     */
    public function payload()
    {
        return $this->payload;
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

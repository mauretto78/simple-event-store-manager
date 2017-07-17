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
     * @var Aggregate
     */
    private $aggregate;

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
     * @param $aggregateName
     * @param $name
     * @param $body
     */
    public function __construct(
        EventId $id,
        $aggregateName,
        $name,
        $body
    ) {
        $this->id = $id;
        $this->setAggregate($aggregateName);
        $this->name = $name;
        $this->body = serialize($body);
        $this->occurred_on = new \DateTimeImmutable();
    }

    /**
     * @return EventId
     */
    public function id()
    {
        return $this->id;
    }

    /**
     * @return Aggregate
     */
    public function aggregate()
    {
        return $this->aggregate;
    }

    /**
     * @param $aggregateName
     */
    public function setAggregate($aggregateName)
    {
        $this->aggregate = new Aggregate(
            new AggregateId(),
            $aggregateName
        );
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

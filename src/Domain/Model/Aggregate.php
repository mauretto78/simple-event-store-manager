<?php
/**
 * This file is part of the Simple EventStore Manager package.
 *
 * (c) Mauro Cassani<https://github.com/mauretto78>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SimpleEventStoreManager\Domain\Model;

use Cocur\Slugify\Slugify;
use SimpleEventStoreManager\Domain\Model\Contracts\EventInterface;

class Aggregate
{
    /**
     * @var AggregateId
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var EventInterface[]
     */
    private $events;

    /**
     * Aggregate constructor.
     *
     * @param AggregateId $id
     * @param $name
     */
    public function __construct(
        AggregateId $id,
        $name
    ) {
        $this->id = $id;
        $this->setName($name);
    }

    /**
     * @return AggregateId
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
     * @param $name
     */
    private function setName($name)
    {
        $this->name = (new Slugify())->slugify($name);
    }

    /**
     * @return EventInterface[]
     */
    public function events()
    {
        return $this->events;
    }

    /**
     * @param EventInterface $event
     */
    public function addEvent(EventInterface $event)
    {
        $this->events[(string) $event->id()] = $event;
    }
}

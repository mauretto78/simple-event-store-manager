<?php
/**
 * This file is part of the Simple EventStore Manager package.
 *
 * (c) Mauro Cassani<https://github.com/mauretto78>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SimpleEventStoreManager\Infrastructure\Persistence;

use Cocur\Slugify\Slugify;
use SimpleEventStoreManager\Domain\Model\Aggregate;
use SimpleEventStoreManager\Domain\Model\AggregateId;
use SimpleEventStoreManager\Domain\Model\Contracts\AggregateRepositoryInterface;
use SimpleEventStoreManager\Domain\Model\Contracts\EventInterface;
use SimpleEventStoreManager\Domain\EventStore\Contracts\EventRepositoryInterface;
use SimpleEventStoreManager\Domain\Model\Event;
use SimpleEventStoreManager\Domain\Model\EventId;

class InMemoryAggregateRepository extends AbstractAggregateRepository implements AggregateRepositoryInterface
{
    /**
     * @var array
     */
    private $aggregates;

    /**
     * InMemoryEventRepository constructor.
     */
    public function __construct()
    {
        $this->aggregates = [];
    }

    /**
     * @param AggregateId $id
     *
     * @return Aggregate
     */
    public function byId(AggregateId $id, $hydrateEvents = true)
    {
        return (isset($this->aggregates[(string) $id])) ? $this->aggregates[(string) $id] : null;
    }

    /**
     * @param string $name
     *
     * @return Aggregate
     */
    public function byName($name, $hydrateEvents = true)
    {
        $aggregateName = (new Slugify())->slugify($name);
        foreach ($this->aggregates as $aggregate){
            if($aggregate->name() === $aggregateName){
                return $aggregate;
            }
        }

        return null;
    }

    /**
     * @param Aggregate $aggregate
     *
     * @return int
     */
    public function eventsCount(Aggregate $aggregate)
    {
        return count($aggregate->events());
    }

    /**
     * @param Aggregate $aggregate
     * @param array $parameters
     *
     * @return Event[]
     */
    public function queryEvents(Aggregate $aggregate, array $parameters = [])
    {
        return $aggregate->events();
    }

    /**
     * @param Aggregate $aggregate
     * @return mixed
     */
    public function save(Aggregate $aggregate)
    {
        $this->aggregates[(string) $aggregate->id()] = $aggregate;
    }
}

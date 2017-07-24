<?php
/**
 * This file is part of the Simple EventStore Manager package.
 *
 * (c) Mauro Cassani<https://github.com/mauretto78>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SimpleEventStoreManager\Infrastructure\Persistence\InMemory;

use Cocur\Slugify\Slugify;
use SimpleEventStoreManager\Domain\Model\Aggregate;
use SimpleEventStoreManager\Domain\Model\AggregateId;
use SimpleEventStoreManager\Domain\Model\Contracts\AggregateRepositoryInterface;
use SimpleEventStoreManager\Domain\Model\Event;

class InMemoryAggregateRepository implements AggregateRepositoryInterface
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
    public function byId(AggregateId $id)
    {
        return (isset($this->aggregates[(string) $id])) ? $this->aggregates[(string) $id] : null;
    }

    /**
     * @param string $name
     *
     * @return Aggregate
     */
    public function byName($name)
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
     * @param $name
     * @return bool
     */
    public function exists($name)
    {
        $aggregateName = (new Slugify())->slugify($name);
        foreach ($this->aggregates as $aggregate){
            if($aggregate->name() === $aggregateName){
                return true;
            }
        }

        return false;
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

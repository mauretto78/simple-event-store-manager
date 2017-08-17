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
     * @return Aggregate|array
     */
    public function byId(AggregateId $id, $returnType = self::RETURN_AS_ARRAY)
    {
        return (isset($this->aggregates[(string) $id])) ? $this->buildAggregate($this->aggregates[(string) $id], $returnType) : null;
    }

    /**
     * @param string $name
     *
     * @return Aggregate|array
     */
    public function byName($name, $returnType = self::RETURN_AS_ARRAY)
    {
        $aggregateName = (new Slugify())->slugify($name);
        foreach ($this->aggregates as $aggregate){
            if($aggregate->name() === $aggregateName){
                return $this->buildAggregate($aggregate, $returnType);
            }
        }

        return null;
    }

    /**
     * @param Aggregate $aggregate
     * @param int $returnType
     * @return Aggregate|array
     */
    private function buildAggregate(Aggregate $aggregate, $returnType)
    {
        switch ($returnType){
            case self::RETURN_AS_ARRAY:
                return $this->buildAggregateAsArray($aggregate);

            case self::RETURN_AS_OBJECT:
                return $this->buildAggregateAsObject($aggregate);
        }
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

    /**
     * @param Aggregate $aggregate
     * @return array
     */
    private function buildAggregateAsArray(Aggregate $aggregate)
    {
        $returnArray['id'] = (string) $aggregate->id();
        $returnArray['name'] = $aggregate->name();

        foreach ($aggregate->events() as $event){
            $returnArray['events'][] = [
                'id' => (string) $event->id(),
                'name' => $event->name(),
                'body' => $event->body(),
                'occurred_on' => $event->occurredOn()->format('Y-m-d H:i:s.u'),
            ];
        }

        return $returnArray;
    }

    /**
     * @param Aggregate $aggregate
     * @return Aggregate
     */
    private function buildAggregateAsObject(Aggregate $aggregate)
    {
        return $aggregate;
    }
}

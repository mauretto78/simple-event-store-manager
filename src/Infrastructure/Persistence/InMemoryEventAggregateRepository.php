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
use SimpleEventStoreManager\Domain\Model\EventAggregate;
use SimpleEventStoreManager\Domain\Model\EventAggregateId;
use SimpleEventStoreManager\Domain\Model\Contracts\EventAggregateRepositoryInterface;

class InMemoryEventAggregateRepository implements EventAggregateRepositoryInterface
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
     * @param EventAggregateId $eventAggregateId
     *
     * @return EventAggregate|array
     */
    public function byId(EventAggregateId $eventAggregateId, $returnType = self::RETURN_AS_ARRAY)
    {
        return (isset($this->aggregates[(string) $eventAggregateId])) ? $this->buildAggregate($this->aggregates[(string) $eventAggregateId], $returnType) : null;
    }

    /**
     * @param string $name
     *
     * @return EventAggregate|array
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
     * @param EventAggregate $aggregate
     * @param int $returnType
     * @return EventAggregate|array
     */
    private function buildAggregate(EventAggregate $aggregate, $returnType)
    {
        if($returnType === self::RETURN_AS_ARRAY){
            return $this->buildAggregateAsArray($aggregate);
        }

        return $this->buildAggregateAsObject($aggregate);
    }

    /**
     * @param EventAggregate $aggregate
     *
     * @return int
     */
    public function eventsCount(EventAggregate $aggregate)
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
     * @param EventAggregate $aggregate
     * @return mixed
     */
    public function save(EventAggregate $aggregate)
    {
        $this->aggregates[(string) $aggregate->id()] = $aggregate;
    }

    /**
     * @param EventAggregate $aggregate
     * @return array
     */
    private function buildAggregateAsArray(EventAggregate $aggregate)
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
     * @param EventAggregate $aggregate
     * @return EventAggregate
     */
    private function buildAggregateAsObject(EventAggregate $aggregate)
    {
        return $aggregate;
    }
}

<?php
/**
 * This file is part of the Simple EventStore Manager package.
 *
 * (c) Mauro Cassani<https://github.com/mauretto78>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SimpleEventStoreManager\Application\Event;

use ArrayQuery\QueryBuilder;
use SimpleEventStoreManager\Domain\Model\Contracts\EventAggregateRepositoryInterface;
use SimpleEventStoreManager\Domain\Model\Contracts\EventInterface;

class EventQuery
{
    /**
     * @var EventManager
     */
    private $eventManger;

    /**
     * @var EventAggregateRepositoryInterface
     */
    private $repo;

    /**
     * @var int
     */
    private $returnType;

    /**
     * EventQuery constructor.
     *
     * @param EventManager $eventManger
     */
    public function __construct(EventManager $eventManger)
    {
        $this->eventManger = $eventManger;
        $this->repo = $eventManger->getRepo();
        $this->returnType = $eventManger->getReturnType();
    }

    /**
     * @param $aggregateName
     *
     * @return array|EventInterface[]
     */
    public function fromAggregate($aggregateName)
    {
        $stream = $this->repo->byName($aggregateName, $this->returnType);

        if ($this->returnType === EventAggregateRepositoryInterface::RETURN_AS_ARRAY) {
            return (isset($stream['events'])) ? $stream['events'] : [];
        }

        return ($stream) ? $stream->events() : [];
    }

    /**
     * @param array $aggregates
     * @return array
     */
    public function fromAggregates(array $aggregates)
    {
        $streams = [];

        foreach ($aggregates as $aggregate) {
            $streams = array_merge($streams, $this->fromAggregate($aggregate));
        }

        return $streams;
    }

    /**
     * @param array $stream
     * @param array $filters
     *
     * @return array
     */
    public function query(array $stream, array $filters = [])
    {
        $qb = QueryBuilder::create((array)$stream);

        foreach ($filters as $key => $value) {
            $qb->addCriterion($key, $value);
        }

        $qb->sortedBy('occurred_on', 'ASC');

        return $qb->getResults();
    }

    /**
     * @param $aggregateName
     *
     * @return int
     */
    public function streamCount($aggregateName)
    {
        $stream = $this->repo->byName($aggregateName, $this->returnType);

        if ($this->returnType === EventAggregateRepositoryInterface::RETURN_AS_ARRAY) {
            return count($stream['events']);
        }

        return count($stream->events());
    }
}

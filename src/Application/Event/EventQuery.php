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
use SimpleEventStoreManager\Domain\Model\AggregateUuid;
use SimpleEventStoreManager\Domain\Model\Contracts\EventStoreRepositoryInterface;

class EventQuery
{
    /**
     * @var EventManager
     */
    private $eventManger;

    /**
     * @var EventStoreRepositoryInterface
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
     * @param $uuid
     *
     * @return array
     */
    public function fromAggregate($uuid)
    {
        $stream = $this->repo->byUuid(new AggregateUuid($uuid), $this->returnType);

        return ($stream) ?: [];
    }

    /**
     * @param array $uuids
     *
     * @return array
     */
    public function fromAggregates(array $uuids)
    {
        $streams = [];

        foreach ($uuids as $uuid) {
            $streams = array_merge($streams, $this->fromAggregate($uuid));
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
     * @param $uuid
     *
     * @return mixed
     */
    public function streamCount($uuid)
    {
        return $this->repo->count(new AggregateUuid($uuid));
    }
}

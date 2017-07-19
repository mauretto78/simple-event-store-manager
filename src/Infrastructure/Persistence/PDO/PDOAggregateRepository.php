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

class PDOAggregateRepository extends AbstractAggregateRepository implements AggregateRepositoryInterface
{
    /**
     * @var \PDO
     */
    private $pdo;

    /**
     * PDOEventRepository constructor.
     *
     * @param \PDO $pdo
     */
    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @param AggregateId $id
     *
     * @return Aggregate
     */
    public function byId(AggregateId $id, $hydrateEvents = true)
    {
        $aggregateId = $id->id();
        $stmt = $this->pdo->prepare($this->getAggregateByIdSql($hydrateEvents));
        $stmt->bindParam(':id', $aggregateId);
        $stmt->execute();

        if ($row = $stmt->fetchAll(\PDO::FETCH_ASSOC)) {
            return $this->buildAggregate($row, $hydrateEvents);
        }

        return null;
    }

    /**
     * @param bool $hydrateEvents
     * @return string
     */
    private function getAggregateByIdSql($hydrateEvents = true)
    {
        if($hydrateEvents){
            return 'SELECT 
                event_aggregates.id as aggregate_id,
                event_aggregates.name as aggregate_name,
                events.id as event_id,
                events.name as event_name,
                events.body as event_body,
                events.occurred_on as event_occurred_on
                FROM `event_aggregates` JOIN `events` ON event_aggregates.id=events.aggregate_id WHERE event_aggregates.id=:id';
        }

        return 'SELECT event_aggregates.id as aggregate_id, event_aggregates.name as aggregate_name FROM `event_aggregates` WHERE event_aggregates.id=:id';
    }

    /**
     * @param string $name
     *
     * @return Aggregate
     */
    public function byName($name, $hydrateEvents = true)
    {
        $name = (new Slugify())->slugify($name);

        $stmt = $this->pdo->prepare($this->getAggregateByNameSql($hydrateEvents));
        $stmt->bindParam(':name', $name);
        $stmt->execute();
        if ($rows = $stmt->fetchAll(\PDO::FETCH_ASSOC)) {
            return $this->buildAggregate($rows, $hydrateEvents);
        }

        return null;
    }

    /**
     * @param bool $hydrateEvents
     * @return string
     */
    private function getAggregateByNameSql($hydrateEvents = true)
    {
        if($hydrateEvents){
            return 'SELECT 
                event_aggregates.id as aggregate_id,
                event_aggregates.name as aggregate_name,
                events.id as event_id,
                events.name as event_name,
                events.body as event_body,
                events.occurred_on as event_occurred_on
                FROM `event_aggregates` JOIN `events` ON event_aggregates.id=events.aggregate_id WHERE event_aggregates.name=:name';
        }

        return 'SELECT event_aggregates.id as aggregate_id, event_aggregates.name as aggregate_name FROM `event_aggregates` WHERE event_aggregates.name=:name';
    }

    /**
     * @return int
     */
    public function eventsCount(Aggregate $aggregate)
    {
        $aggregateId = $aggregate->id();

        $sql = 'SELECT * FROM `events` WHERE aggregate_id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $aggregateId);
        $stmt->execute();

        return $stmt->rowCount();
    }

    /**
     * @param array $parameters
     *
     * @return Event[]
     */
    public function queryEvents(Aggregate $aggregate, array $parameters = [])
    {
        $aggregateId = $aggregate->id();
        $sql = 'SELECT * FROM `events`';

        $where = ['`aggregate_id` = :id'];

        if(isset($parameters['from'])) {
            $where[] = '`occurred_on` >= :from';
        }

        if(isset($parameters['to'])) {
            $where[] = '`occurred_on` <= :to';
        }

        foreach ($where as $index => $statement){
            $sql .= ($index === 0) ? ' WHERE ' . $statement : ' AND ' . $statement;
        }

        $sql .= ' ORDER BY `occurred_on` ASC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $aggregateId);

        if(isset($parameters['from'])) {
            $from = (new \DateTimeImmutable($parameters['from']))->format('Y-m-d H:i:s');
            $stmt->bindParam(':from', $from);
        }

        if(isset($parameters['to'])) {
            $to = (new \DateTimeImmutable($parameters['to']))->format('Y-m-d H:i:s');
            $stmt->bindParam(':to', $to);
        }

        $stmt->execute();
        $events = [];

        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row){
            $eventRepo = new PDOEventRepository($this->pdo);
            $events[] = $eventRepo->buildEvent($row);
        }

        return $events;
    }

    /**
     * @param Aggregate $aggregate
     * @return mixed
     */
    public function save(Aggregate $aggregate)
    {
        $AggregateId = $aggregate->id();
        $AggregateName = $aggregate->name();

        $sql = 'INSERT INTO `event_aggregates` (`id`, `name`) VALUES (:id, :name)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $AggregateId);
        $stmt->bindParam(':name', $AggregateName);
        $stmt->execute();

        /** @var Event $event */
        foreach ($aggregate->events() as $event){
            $eventRepo = new PDOEventRepository($this->pdo);
            $eventRepo->save($event);
        }
    }

    /**
     * @param array $rows
     *
     * @return Aggregate
     */
    public function buildAggregate(array $rows, $hydrateEvents)
    {
        $aggregate = new Aggregate(
            new AggregateId($rows[0]['aggregate_id']),
            $rows[0]['aggregate_name']
        );

        if($hydrateEvents){
            foreach ($rows as $row){
                $aggregate->addEvent(
                    new Event(
                        new EventId($row['event_id']),
                        $aggregate,
                        $row['event_name'],
                        unserialize($row['event_body']),
                        $row['event_occurred_on']
                    )
                );
            }
        }

        return $aggregate;
    }
}

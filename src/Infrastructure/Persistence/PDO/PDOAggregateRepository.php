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
     * @return mixed
     */
    public function byId(AggregateId $id)
    {
        // TODO: Implement byId() method.
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function byName($name)
    {
        // TODO: Implement byName() method.
    }

    /**
     * @param Aggregate $aggregate
     * @return mixed
     */
    public function save(Aggregate $aggregate)
    {
        // TODO: Implement save() method.
    }
















    /**
     * @param string $name
     * @return mixed
     */
    public function findAggregateByName($name)
    {
        $sluggify = new Slugify();
        $aggregateName = $sluggify->slugify($name);

        $sql = 'SELECT * FROM `event_aggregates` WHERE `name`=:name';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':name', $aggregateName);
        $stmt->execute();

        return $stmt->fetchObject();
    }

    /**
     * @param EventInterface $event
     *
     * @return mixed
     */
    public function store(EventInterface $event)
    {
        $eventId = $event->id();
        $eventAggregateId = $event->aggregate()->id();
        $eventAggregateName = $event->aggregate()->name();
        $eventName = $event->name();
        $eventBody = $event->body();
        $eventOccurredOn = $event->occurredOn()->format('Y-m-d H:i:s');

        if(!$aggregate = $this->findAggregateByName($eventAggregateName)){
            $sql = 'INSERT INTO `event_aggregates` (`id`, `name`) VALUES (:id, :name)';
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $eventAggregateId);
            $stmt->bindParam(':name', $eventAggregateName);
            $stmt->execute();
        } else {
            $eventAggregateId = $aggregate->id;
        }

        $sql = 'INSERT INTO `events` (`id`, `aggregate_id`, `aggregate_name`, `name`, `body`, `occurred_on`) VALUES (:id, :aggregate_id, :aggregate_name, :name, :body, :occurred_on)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $eventId);
        $stmt->bindParam(':aggregate_id', $eventAggregateId);
        $stmt->bindParam(':aggregate_name', $eventAggregateName);
        $stmt->bindParam(':name', $eventName);
        $stmt->bindParam(':body', $eventBody);
        $stmt->bindParam(':occurred_on', $eventOccurredOn);
        $stmt->execute();
    }

    /**
     * @param EventId $eventId
     *
     * @return mixed
     */
    public function restore(EventId $eventId)
    {
        $eventId = $eventId->id();

        $sql = 'SELECT * FROM `events` WHERE `id`=:id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $eventId);
        $stmt->execute();

        return $stmt->fetchObject();
    }

    /**
     * @return int
     */
    public function eventsCount()
    {
        $sql = 'SELECT * FROM `events`';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        return $stmt->rowCount();
    }

    /**
     * @param array $parameters
     * @return mixed
     */
    public function query(array $parameters = [])
    {
        $sql = 'SELECT * FROM `events`';
        $join = [];
        $where = [];

        if(isset($parameters['from']) && isset($parameters['to'])) {
            $where[] = '`occurred_on` BETWEEN :from AND :to';
        }

        if(isset($parameters['aggregate_id'])){
            $where[] = '`aggregate_id` = :aggregate_id';
        }

        if(isset($parameters['aggregate_name'])){
            $join[] = '`event_aggregates` ON event_aggregates.id = events.aggregate_id';
            $where[] = 'event_aggregates.name = :aggregate_name';
        }

        foreach ($join as $statement){
            $sql .= ' JOIN ' . $statement;
        }

        foreach ($where as $index => $statement){
            $sql .= ($index === 0) ? ' WHERE ' . $statement : ' AND ' . $statement;
        }

        $sql .= ' ORDER BY `occurred_on` ASC';
        $stmt = $this->pdo->prepare($sql);

        if(isset($parameters['from']) && isset($parameters['to'])) {
            $from = (new \DateTimeImmutable($parameters['from']))->format('Y-m-d H:i:s');
            $to = (new \DateTimeImmutable($parameters['to']))->format('Y-m-d H:i:s');

            $stmt->bindParam(':from', $from);
            $stmt->bindParam(':to', $to);
        }

        if(isset($parameters['aggregate_name'])){
            $aggregateName = $this->slugify->slugify($parameters['aggregate_name']);
            $stmt->bindParam(':aggregate_name', $aggregateName);
        }

        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }


}

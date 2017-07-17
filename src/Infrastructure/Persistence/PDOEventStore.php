<?php
/**
 * This file is part of the EventStoreManager package.
 *
 * (c) Mauro Cassani<https://github.com/mauretto78>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SimpleEventStoreManager\Infrastructure\Persistence;

use Cocur\Slugify\Slugify;
use SimpleEventStoreManager\Domain\Model\Contracts\EventInterface;
use SimpleEventStoreManager\Domain\EventStore\Contracts\EventStoreInterface;
use SimpleEventStoreManager\Domain\Model\EventId;

class PDOEventStore extends AbstractEventStore implements EventStoreInterface
{
    /**
     * @var \PDO
     */
    private $pdo;

    /**
     * PDOEventStore constructor.
     *
     * @param \PDO $pdo
     */
    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->createSchema();
    }

    /**
     * create schema.
     */
    private function createSchema()
    {
        $sqlArray = [];
        $sqlArray[] = 'CREATE TABLE IF NOT EXISTS `event_aggregates` (
          `id` varchar(255) NOT NULL DEFAULT \'\',
          `name` varchar(255) UNIQUE,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;';

        $sqlArray[] = 'CREATE TABLE IF NOT EXISTS `events` (
          `id` varchar(255) NOT NULL DEFAULT \'\',
          `aggregate_id` varchar(255),
          `name` varchar(255) DEFAULT NULL,
          `body` longtext,
          `occurred_on` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;';

        foreach ($sqlArray as $sql){
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
        }
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

        $sql = 'INSERT INTO `events` (`id`, `aggregate_id`, `name`, `body`, `occurred_on`) VALUES (:id, :aggregate_id, :name, :body, :occurred_on)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $eventId);
        $stmt->bindParam(':aggregate_id', $eventAggregateId);
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
            $sluggify = new Slugify();
            $aggregateName = $sluggify->slugify($parameters['aggregate_name']);
            $stmt->bindParam(':aggregate_name', $aggregateName);
        }

        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }
}

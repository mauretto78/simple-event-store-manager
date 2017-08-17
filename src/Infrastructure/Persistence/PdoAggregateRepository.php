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
use SimpleEventStoreManager\Domain\Model\Event;
use SimpleEventStoreManager\Domain\Model\EventId;

class PdoAggregateRepository implements AggregateRepositoryInterface
{
    /**
     * @var \PDO
     */
    private $pdo;

    /**
     * PdoEventRepository constructor.
     *
     * @param \PDO $pdo
     */
    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @param AggregateId $id
     * @param int $returnType
     * @return array|null|Aggregate
     */
    public function byId(AggregateId $id, $returnType = self::RETURN_AS_ARRAY)
    {
        $aggregateId = (string) $id->id();
        $query = 'SELECT
                `event_aggregates`.id AS `aggregate_id`,
                `event_aggregates`.name AS `aggregate_name`,
                `events`.id AS `event_id`,
                `events`.name AS `event_name`,
                `events`.body AS `event_body`,
                `events`.occurred_on AS `event_occurred_on`
                FROM `event_aggregates` INNER JOIN `events` 
                ON `event_aggregates`.id = `events`.aggregate_id 
                WHERE `event_aggregates`.id = :id';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':id', $aggregateId);
        $stmt->execute();

        $row = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if (!empty($row)) {
            return $this->buildAggregate($row, $returnType);
        }

        return null;
    }

    /**
     * @param $name
     * @param int $returnType
     * @return array|null|Aggregate
     */
    public function byName($name, $returnType = self::RETURN_AS_ARRAY)
    {
        $name = (new Slugify())->slugify($name);
        $query = 'SELECT 
            `event_aggregates`.id AS `aggregate_id`,
            `event_aggregates`.name AS `aggregate_name`,
            `events`.id AS `event_id`,
            `events`.name AS `event_name`,
            `events`.body AS `event_body`,
            `events`.occurred_on AS `event_occurred_on`
            FROM `event_aggregates` INNER JOIN `events` 
            ON `event_aggregates`.id = `events`.aggregate_id 
            WHERE `event_aggregates`.name = :name';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->execute();

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if (!empty($rows)) {
            return $this->buildAggregate($rows, $returnType);
        }

        return null;
    }

    /**
     * @param array $rows
     * @return Aggregate|array
     */
    private function buildAggregate(array $rows, $returnType)
    {
        switch ($returnType){
            case self::RETURN_AS_ARRAY:
                return $this->buildAggregateAsArray($rows);

            case self::RETURN_AS_OBJECT:
                return $this->buildAggregateAsObject($rows);
        }
    }

    /**
     * @return int
     */
    public function eventsCount(Aggregate $aggregate)
    {
        $aggregateId = $aggregate->id();
        $sql = 'SELECT id FROM `events` WHERE `aggregate_id` = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $aggregateId);
        $stmt->execute();

        return $stmt->rowCount();
    }

    /**
     * @param $name
     * @return bool
     */
    public function exists($name)
    {
        $name = (new Slugify())->slugify($name);
        $sql = 'SELECT id FROM `event_aggregates` WHERE `name` = :name';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->execute();

        return ($stmt->rowCount() > 0) ? true : false;
    }

    /**
     * @param Aggregate $aggregate
     * @return mixed
     */
    public function save(Aggregate $aggregate)
    {
        $AggregateId = (string) $aggregate->id();
        $AggregateName = $aggregate->name();

        $sql = 'INSERT INTO `event_aggregates` (`id`, `name`) VALUES (:id, :name)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $AggregateId);
        $stmt->bindParam(':name', $AggregateName);
        $stmt->execute();

        /** @var Event $event */
        foreach ($aggregate->events() as $event){
            $this->saveEvent($event, $aggregate);
        }
    }

    /**
     * @param EventInterface $event
     *
     * @return mixed
     */
    private function saveEvent(EventInterface $event, Aggregate $aggregate)
    {
        $eventId = (string) $event->id();
        $eventAggregateId = (string) $aggregate->id();
        $eventAggregateName = $aggregate->name();
        $eventName = $event->name();
        $eventBody = serialize($event->body());
        $eventOccurredOn = $event->occurredOn()->format('Y-m-d H:i:s.u');

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
     * @param array $rows
     *
     * @return Aggregate
     */
    private function buildAggregateAsArray(array $rows)
    {
        $returnArray['id'] = (string) $rows[0]['aggregate_id'];
        $returnArray['name'] = $rows[0]['aggregate_name'];

        foreach ($rows as $row){
            $returnArray['events'][] = [
                'id' => (string) $row['event_id'],
                'name' => $row['event_name'],
                'body' => unserialize($row['event_body']),
                'occurred_on' => $row['event_occurred_on'],
            ];
        }

        return $returnArray;
    }

    /**
     * @param array $rows
     *
     * @return Aggregate
     */
    private function buildAggregateAsObject(array $rows)
    {
        $aggregate = new Aggregate(
            new AggregateId($rows[0]['aggregate_id']),
            $rows[0]['aggregate_name']
        );

        foreach ($rows as $row){
            $aggregate->addEvent(
                new Event(
                    new EventId($row['event_id']),
                    $row['event_name'],
                    unserialize($row['event_body']),
                    $row['event_occurred_on']
                )
            );
        }

        return $aggregate;
    }
}

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

use SimpleEventStoreManager\Domain\Model\Aggregate;
use SimpleEventStoreManager\Domain\Model\AggregateId;
use SimpleEventStoreManager\Domain\Model\Contracts\EventInterface;
use SimpleEventStoreManager\Domain\Model\Contracts\EventRepositoryInterface;
use SimpleEventStoreManager\Domain\Model\Event;
use SimpleEventStoreManager\Domain\Model\EventId;

class PDOEventRepository extends AbstractAggregateRepository implements EventRepositoryInterface
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
     * @param EventId $id
     * @return mixed
     */
    public function byId(EventId $id)
    {
        $sql = 'SELECT * FROM `events` WHERE `id`=:id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        if ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            return $this->buildEvent($row);
        }

        return null;
    }

    /**
     * @param EventInterface $event
     *
     * @return mixed
     */
    public function save(EventInterface $event)
    {
        $eventId = $event->id();
        $eventAggregateId = $event->aggregate()->id();
        $eventAggregateName = $event->aggregate()->name();
        $eventName = $event->name();
        $eventBody = $event->body();
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
     * @param array $row
     *
     * @return Event
     */
    public function buildEvent(array $row)
    {
        return new Event(
            new EventId($row['id']),
            new Aggregate(
                new AggregateId($row['aggregate_id']),
                $row['aggregate_name']
            ),
            $row['name'],
            unserialize($row['body']),
            $row['occurred_on']
        );
    }
}

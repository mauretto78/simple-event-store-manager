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

use SimpleEventStoreManager\Domain\Model\Contracts\EventInterface;
use SimpleEventStoreManager\Domain\Model\Contracts\EventStoreInterface;
use SimpleEventStoreManager\Domain\Model\EventId;

class PDOEventStore extends AbstractEventStore
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
        $sql = 'CREATE TABLE IF NOT EXISTS `events` (
          `id` varchar(255) NOT NULL DEFAULT \'\',
          `name` varchar(255) DEFAULT NULL,
          `body` longtext,
          `occurred_on` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
    }

    /**
     * @param EventInterface $event
     *
     * @return mixed
     */
    public function store(EventInterface $event)
    {
        $eventId = $event->id();
        $eventName = $event->name();
        $eventBody = $event->body();
        $eventOccurredOn = $event->occurredOn()->format('Y-m-d H:i:s');

        $sql = 'INSERT INTO `events` (`id`, `name`, `body`, `occurred_on`) VALUES (:id, :name, :body, :occurred_on)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $eventId);
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
     * @param \DateTimeImmutable|null $from
     * @param \DateTimeImmutable|null $to
     *
     * @return mixed
     */
    public function eventsInRangeDate(\DateTimeImmutable $from = null, \DateTimeImmutable $to = null)
    {
        $sql = 'SELECT * FROM `events`';
        if ($from && $to) {
            $sql .= ' WHERE `occurred_on` BETWEEN :from AND :to';
        }
        $sql .= ' ORDER BY `occurred_on` ASC';

        $stmt = $this->pdo->prepare($sql);
        if ($from && $to) {
            $from = $from->format('Y-m-d H:i:s');
            $to = $to->format('Y-m-d H:i:s');

            $stmt->bindParam(':from', $from);
            $stmt->bindParam(':to', $to);
        }
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }
}

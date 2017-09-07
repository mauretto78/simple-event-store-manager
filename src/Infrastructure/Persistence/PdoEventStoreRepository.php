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

use SimpleEventStoreManager\Domain\Model\Contracts\EventStoreRepositoryInterface;
use SimpleEventStoreManager\Domain\Model\Contracts\EventInterface;
use SimpleEventStoreManager\Domain\Model\EventUuid;
use SimpleEventStoreManager\Infrastructure\Drivers\PdoDriver;

class PdoEventStoreRepository implements EventStoreRepositoryInterface
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


    public function byUuid(EventUuid $eventUuid, $returnType = self::RETURN_AS_ARRAY)
    {
        $eventUuid = (string) $eventUuid;
        $query = 'SELECT
                  `uuid`,
                  `version`,
                  `type`,
                  `body`,
                  `occurred_on`
                FROM `'.PdoDriver::EVENTSTORE_TABLE_NAME.'` 
                WHERE `uuid` = :uuid
                ORDER BY `occurred_on` ASC';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':uuid', $eventUuid);
        $stmt->execute();

        $row = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (!empty($row)) {
            return $this->buildEventAggregate($row, $returnType);
        }

        return null;
    }


    private function buildEventAggregate(array $rows, $returnType)
    {
        if ($returnType === self::RETURN_AS_ARRAY) {
            return $this->buildEventAggregateAsArray($rows);
        }

        return $this->buildEventAggregateAsObject($rows);
    }

    /**
     * @return int
     */
    public function count(EventUuid $eventUuid)
    {
        $sql = 'SELECT id FROM `'.PdoDriver::EVENTSTORE_TABLE_NAME.'` WHERE `uuid` = :uuid';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':uuid', $eventUuid);
        $stmt->execute();

        return $stmt->rowCount();
    }

    /**
     * @param EventInterface $event
     *
     * @return mixed
     */
    public function save(EventInterface $event)
    {
        $uuid = (string) $event->uuid();
        $version = ($this->count($event->uuid())) ?: 0;
        $type = $event->type();
        $body = serialize($event->body());
        $occurredOn = $event->occurredOn()->format('Y-m-d H:i:s.u');

        $sql = 'INSERT INTO `'.PdoDriver::EVENTSTORE_TABLE_NAME.'` (
                    `uuid`,
                    `version`,
                    `type`,
                    `body`,
                    `occurred_on`
                  ) VALUES (
                    :uuid,
                    :version, 
                    :type,
                    :body, 
                    :occurred_on
            )';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':uuid', $uuid);
        $stmt->bindParam(':version', $version);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':body', $body);
        $stmt->bindParam(':occurred_on', $occurredOn);
        $stmt->execute();
    }

    /**
     * @param array $rows
     * @return array
     */
    private function buildEventAggregateAsArray(array $rows)
    {
        $returnArray = [];

        foreach ($rows as $row) {
            $returnArray['events'][] = [
                'uuid' => $row['uuid'],
                'version' => $row['version'],
                'type' => $row['type'],
                'body' => unserialize($row['body']),
                'occurred_on' => $row['occurred_on'],
            ];
        }

        return $returnArray;
    }

    private function buildEventAggregateAsObject(array $rows)
    {
        return [];
    }
}

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

use MongoDB\Database;
use MongoDB\Model\BSONDocument;
use SimpleEventStoreManager\Domain\Model\Contracts\EventStoreRepositoryInterface;
use SimpleEventStoreManager\Domain\Model\Contracts\EventInterface;
use SimpleEventStoreManager\Domain\Model\Event;
use SimpleEventStoreManager\Domain\Model\AggregateUuid;

class MongoEventStoreRepository implements EventStoreRepositoryInterface
{
    /**
     * @var Database
     */
    private $mongo;

    /**
     * @var \MongoDB\Collection
     */
    private $events;

    /**
     * MongoEventAggregateRepository constructor.
     * @param Database $mongo
     */
    public function __construct(Database $mongo)
    {
        $this->mongo = $mongo;
        $this->events = $this->mongo->events;
    }

    /**
     * @param AggregateUuid $uuid
     * @param int $returnType
     *
     * @return array|null
     */
    public function byUuid(AggregateUuid $uuid, $returnType = self::RETURN_AS_ARRAY)
    {
        if ($document = $this->events->find(['uuid' => (string) $uuid])->toArray()) {
            return $this->buildEventAggregate($document, $returnType);
        }

        return null;
    }

    /**
     * @param $document
     * @param $returnType
     *
     * @return array
     */
    private function buildEventAggregate($document, $returnType)
    {
        if ($returnType === self::RETURN_AS_ARRAY) {
            return $this->buildAggregateAsArray($document);
        }

        return $this->buildAggregateAsObject($document);
    }

    /**
     * @param BSONDocument $document
     *
     * @return array
     */
    private function buildAggregateAsArray($document)
    {
        $returnArray = [];

        foreach ($document as $event) {
            $returnArray[] = [
                'uuid' => (string) $event->uuid,
                'version' => $event->version,
                'type' => $event->type,
                'body' => unserialize($event->body),
                'occurred_on' => $event->occurred_on
            ];
        }

        return $returnArray;
    }

    /**
     * @param BSONDocument $document
     *
     * @return array
     */
    private function buildAggregateAsObject($document)
    {
        $returnObject = [];

        foreach ($document as $event) {
            $returnObject[] = new Event(
                new AggregateUuid($event->uuid),
                $event->type,
                unserialize($event->body),
                $event->version,
                $event->occurred_on
            );
        }

        return $returnObject;
    }

    /**
     * @param AggregateUuid $uuid
     *
     * @return int
     */
    public function count(AggregateUuid $uuid)
    {
        return $this->events->count(['uuid' => (string) $uuid]);
    }

    /**
     * @param EventInterface $event
     */
    public function save(EventInterface $event)
    {
        $uuid = (string) $event->uuid();
        $version = ($this->count($event->uuid())) ?: 0;
        $type = $event->type();
        $body = serialize($event->body());
        $occurredOn = $event->occurredOn()->format('Y-m-d H:i:s.u');

        $this->events->insertOne([
            'uuid' => $uuid,
            'version' => $version,
            'type' => $type,
            'body' => $body,
            'occurred_on' => $occurredOn
        ]);
    }
}

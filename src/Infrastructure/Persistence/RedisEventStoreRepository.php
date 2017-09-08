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

use Predis\Client;
use SimpleEventStoreManager\Domain\Model\Contracts\EventInterface;
use SimpleEventStoreManager\Domain\Model\Contracts\EventStoreRepositoryInterface;
use SimpleEventStoreManager\Domain\Model\Event;
use SimpleEventStoreManager\Domain\Model\AggregateUuid;

class RedisEventStoreRepository implements EventStoreRepositoryInterface
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var int
     */
    private $return;

    /**
     * RedisEventRepository constructor.
     *
     * @param Client $client
     */
    public function __construct(Client $client, $return = self::RETURN_AS_ARRAY)
    {
        $this->client = $client;
        $this->return = $return;
    }

    /**
     * @param AggregateUuid $uuid
     * @param int $returnType
     * @return array|null
     */
    public function byUuid(AggregateUuid $uuid, $returnType = self::RETURN_AS_ARRAY)
    {
        $events = array_map(function($event){
            return unserialize($event);
        }, $this->client->hgetall((string) $uuid));

        if (!empty($events)) {
            ksort($events);

            return $this->buildEventAggregate($events, $returnType);
        }

        return null;
    }

    /**
     * @param array $events
     * @param $returnType
     *
     * @return array
     */
    private function buildEventAggregate(array $events, $returnType)
    {
        if ($returnType === self::RETURN_AS_ARRAY) {
            return $this->buildEventAggregateAsArray($events);
        }

        return $this->buildEventAggregateAsObject($events);
    }

    /**
     * @param array $events
     *
     * @return array
     */
    private function buildEventAggregateAsArray(array $events)
    {
        $returnArray = [];

        /** @var Event $event */
        foreach ($events as $event) {
            $returnArray[] = [
                'uuid' => (string) $event->uuid(),
                'version' => $event->version(),
                'type' => $event->type(),
                'body' => $event->body(),
                'occurred_on' => $event->occurredOn()->format('Y-m-d H:i:s.u')
            ];
        }

        return $returnArray;
    }

    /**
     * @param array $events
     *
     * @return array
     */
    private function buildEventAggregateAsObject(array $events)
    {
        $returnObject = [];

        /** @var Event $event */
        foreach ($events as $event) {
            $returnObject[] = $event;
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
        return count($this->client->hgetall((string) $uuid));
    }

    /**
     * @param EventInterface $event
     */
    public function save(EventInterface $event)
    {
        $this->client->hset(
            (string) $event->uuid(),
            $this->count($event->uuid()),
            serialize(
                new Event(
                    $event->uuid(),
                    $event->type(),
                    $event->body(),
                    $this->count($event->uuid()),
                    $event->occurredOn()->format('Y-m-d H:i:s.u')
                )
            )
        );
    }
}

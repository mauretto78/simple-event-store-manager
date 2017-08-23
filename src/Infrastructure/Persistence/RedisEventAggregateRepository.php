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
use Predis\Client;
use Predis\Collection\Iterator\SetKey;
use SimpleEventStoreManager\Domain\Model\EventAggregate;
use SimpleEventStoreManager\Domain\Model\EventAggregateId;
use SimpleEventStoreManager\Domain\Model\Contracts\EventAggregateRepositoryInterface;
use SimpleEventStoreManager\Domain\Model\Contracts\EventInterface;
use SimpleEventStoreManager\Domain\Model\Event;
use SimpleEventStoreManager\Domain\Model\EventId;

class RedisEventAggregateRepository implements EventAggregateRepositoryInterface
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
     * @param EventAggregateId $eventAggregateId
     *
     * @return EventAggregate
     */
    public function byId(EventAggregateId $eventAggregateId, $returnType = self::RETURN_AS_ARRAY)
    {
        if ($aggregateEvents = $this->client->lrange($this->computeEventsHash($eventAggregateId), 0, -1)) {
            return $this->buildAggregate($eventAggregateId, $aggregateEvents, $returnType);
        }

        return null;
    }

    /**
     * @param $name
     * @param int $returnType
     * @return array|null|EventAggregate
     */
    public function byName($name, $returnType = self::RETURN_AS_ARRAY)
    {
        $aggregateId = $this->client->get($this->computeAggregateNameHash($name));

        return $this->byId(new EventAggregateId($aggregateId), $returnType);
    }

    /**
     * @param array $aggregateEvents
     *
     * @return EventAggregate|array
     */
    private function buildAggregate($eventAggregateId, array $aggregateEvents, $returnType)
    {
        if($returnType === self::RETURN_AS_ARRAY){
            return $this->buildAggregateAsArray($eventAggregateId, $aggregateEvents);
        }

        return $this->buildAggregateAsObject($eventAggregateId, $aggregateEvents);
    }

    /**
     * @return int
     */
    public function eventsCount(EventAggregate $aggregate)
    {
        return count($this->client->lrange($this->computeEventsHash($aggregate->id()), 0, -1));
    }

    /**
     * @param $name
     *
     * @return bool
     */
    public function exists($name)
    {
        return ($this->byName($name)) ? true : false;
    }

    /**
     * @param EventAggregate $aggregate
     * @return mixed
     */
    public function save(EventAggregate $aggregate)
    {
        $this->client->set($this->computeAggregateHash($aggregate->id()), $aggregate->name());
        $this->client->set($this->computeAggregateNameHash($aggregate->name()), $aggregate->id());

        /** @var Event $event */
        foreach ($aggregate->events() as $event){
            $this->saveEvent($event, $aggregate);
        }
    }

    /**
     * @param EventAggregateId $eventAggregateId
     *
     * @return string
     */
    private function computeAggregateHash(EventAggregateId $eventAggregateId)
    {
        return sprintf('aggregate:%s', (string) $eventAggregateId);
    }

    /**
     * @param $aggregateName
     *
     * @return string
     */
    private function computeAggregateNameHash($aggregateName)
    {
        return sprintf('aggregate-name:%s', (new Slugify())->slugify($aggregateName));
    }

    /**
     * @param EventInterface $event
     * @param EventAggregate $aggregate
     */
    private function saveEvent(EventInterface $event, EventAggregate $aggregate)
    {
        $this->client->rpush($this->computeEventsHash($aggregate->id()),
            [
                serialize([
                    'id' => (string) $event->id(),
                    'name' => $event->name(),
                    'body' => serialize($event->body()),
                    'occurred_on' => $event->occurredOn()->format('Y-m-d H:i:s.u'),
                ])
            ]
        );
    }

    /**
     * @param EventAggregateId $eventAggregateId
     * @return string
     */
    private function computeEventsHash(EventAggregateId $eventAggregateId)
    {
        return sprintf('events:%s', (string) $eventAggregateId);
    }

    /**
     * @param $eventAggregateId
     * @param array $aggregateEvents
     *
     * @return mixed
     */
    private function buildAggregateAsArray($eventAggregateId, array $aggregateEvents)
    {
        $returnArray['id'] = $eventAggregateId;
        $returnArray['name'] = $this->client->get($this->computeAggregateHash($eventAggregateId));

        foreach ($aggregateEvents as $event){
            $event = unserialize($event);
            $returnArray['events'][] = [
                'id' => (string) $event['id'],
                'name' => $event['name'],
                'body' => unserialize($event['body']),
                'occurred_on' => $event['occurred_on'],
            ];
        }

        return $returnArray;
    }

    /**
     * @param $eventAggregateId
     * @param array $aggregateEvents
     *
     * @return EventAggregate
     */
    private function buildAggregateAsObject($eventAggregateId, array $aggregateEvents)
    {
        $aggregate = new EventAggregate(
            $this->client->get($this->computeAggregateHash($eventAggregateId)),
            new EventAggregateId($eventAggregateId)
        );

        foreach ($aggregateEvents as $event){
            $event = unserialize($event);
            $aggregate->addEvent(
                new Event(
                    $event['name'],
                    unserialize($event['body']),
                    new EventId($event['id']),
                    $event['occurred_on']
                )
            );
        }

        return $aggregate;
    }
}

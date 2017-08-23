<?php
/**
 * This file is part of the Simple EventStore Manager package.
 *
 * (c) Mauro Cassani<https://github.com/mauretto78>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SimpleEventStoreManager\Infrastructure\Services;

use Elasticsearch\Client;
use SimpleEventStoreManager\Domain\Model\EventAggregate;
use SimpleEventStoreManager\Domain\Model\Contracts\EventInterface;
use SimpleEventStoreManager\Infrastructure\Persistence\Exceptions\AggregateNotPersistedInElasticIndexException;

class ElasticService
{
    const EVENTS_INDEX = 'events';

    /**
     * @var Client
     */
    private $elastic;

    /**
     * ElasticService constructor.
     * @param Client $elastic
     */
    public function __construct(Client $elastic)
    {
        $this->elastic = $elastic;
    }

    /**
     * @param EventAggregate $aggregate
     * @throws AggregateNotPersistedInElasticIndexException
     */
    public function addAggregateToIndex(EventAggregate $aggregate)
    {
        $this->manageAggregateIndex($aggregate->name());

        foreach ($aggregate->events() as $event){
            $params = [
                'index' => self::EVENTS_INDEX,
                'type' => $aggregate->name(),
                'id' => (string) $event->id(),
                'body' => $this->buildEventBody($event)
            ];

            try {
                $this->elastic->index($params);
            } catch (\Exception $e){
                throw new AggregateNotPersistedInElasticIndexException($e->getMessage());
            }
        }
    }

    /**
     * @param $aggregate
     */
    private function manageAggregateIndex($aggregate)
    {
        if(false === $this->elastic->indices()->exists(['index' => self::EVENTS_INDEX])){
            $params = [
                'index' => self::EVENTS_INDEX,
                'body' => [
                    'mappings' => [
                        $aggregate => [
                            '_source' => [
                                'enabled' => true
                            ],
                            'properties' => [
                                'class' => [
                                    'type' => 'text'
                                ],
                                'occurred_on' => [
                                    'type' => 'date',
                                    'format' => 'yyyy-MM-dd HH:mm:ss.SSSSSS'
                                ]
                            ]
                        ]
                    ]
                ]
            ];

            $this->elastic->indices()->create($params);
        }
    }

    /**
     * @param EventInterface $event
     *
     * @return array
     */
    private function buildEventBody(EventInterface $event)
    {
        $body = (array) $event->body();
        $body['class'] = (new \ReflectionClass($event))->getShortName();
        $body['occurred_on'] = $event->occurredOn()->format('Y-m-d H:i:s.u');

        return $body;
    }
}

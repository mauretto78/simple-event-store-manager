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
use SimpleEventStoreManager\Domain\Model\Aggregate;

class ElasticService
{
    /**
     * @var Client
     */
    private $client;

    /**
     * ElasticService constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param Aggregate $aggregate
     */
    public function addAggregateToIndex(Aggregate $aggregate)
    {
        foreach ($aggregate->events() as $event){
            $fullBody = (array) unserialize($event->body());
            $fullBody['occurred_on'] = $event->occurredOn()->format('Y-m-d H:i:s.u');
            $params = [
                'index' => $aggregate->name(),
                'type' => (new \ReflectionClass($event))->getShortName(),
                'id' => (string) $event->id(),
                'body' => $fullBody
            ];

            $this->client->index($params);
        }
    }
}

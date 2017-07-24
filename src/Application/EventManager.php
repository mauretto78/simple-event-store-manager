<?php
/**
 * This file is part of the EventStoreManager package.
 *
 * (c) Mauro Cassani<https://github.com/mauretto78>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SimpleEventStoreManager\Application;

use Elasticsearch\ClientBuilder;
use SimpleEventStoreManager\Application\Exceptions\NotSupportedDriverException;
use SimpleEventStoreManager\Application\Exceptions\NotValidEventException;
use SimpleEventStoreManager\Domain\Model\Aggregate;
use SimpleEventStoreManager\Domain\Model\AggregateId;
use SimpleEventStoreManager\Domain\Model\Contracts\AggregateRepositoryInterface;
use SimpleEventStoreManager\Domain\Model\Contracts\EventInterface;
use SimpleEventStoreManager\Infrastructure\Services\ElasticService;

class EventManager
{
    /**
     * @var string
     */
    private $driver;

    /**
     * @var AggregateRepositoryInterface
     */
    private $repo;

    /**
     * @var ElasticService
     */
    private $elastic;

    /**
     * EventManager constructor.
     * @param string $driver
     * @param array $parameters
     * @param array $elasticConfig
     */
    public function __construct($driver = 'mongo', array $parameters = [], array $elasticConfig = [])
    {
        $this->setDriver($driver);
        $this->setRepo($driver, $parameters);

        if(isset($elasticConfig['elastic']) && $elasticConfig['elastic'] === true){
            $this->setElastic($elasticConfig['elastic_hosts']);
        }
    }

    /**
     * @param $driver
     *
     * @throws NotSupportedDriverException
     */
    private function setDriver($driver)
    {
        $allowedDrivers = [
            'in-memory',
            'mongo',
            'pdo',
            'redis',
        ];

        if (!in_array($driver, $allowedDrivers)) {
            throw new NotSupportedDriverException($driver.' is not a supported driver.');
        }

        $this->driver = $driver;
    }
    /**
     * @return string
     */
    public function driver()
    {
        return $this->driver;
    }

    /**
     * @param $driver
     *
     * @param array $parameters
     */
    private function setRepo($driver, array $parameters = [])
    {
        $aggregateRepo = 'SimpleEventStoreManager\Infrastructure\Persistence\\'.$this->normalizeDriverName($driver).'AggregateRepository';
        $driver = 'SimpleEventStoreManager\Infrastructure\Drivers\\'.$this->normalizeDriverName($driver).'Driver';
        $instance = (new $driver($parameters))->instance();
        $this->repo = new $aggregateRepo($instance);
    }

    /**
     * @param array $hosts
     */
    private function setElastic(array $hosts = [])
    {
        $this->elastic = new ElasticService(
            ClientBuilder::create()
                ->setHosts($hosts)
                ->build()
        );
    }

    /**
     * @param $driver
     *
     * @return string
     */
    private function normalizeDriverName($driver)
    {
        $driver = str_replace([' ', '-'], '', $driver);

        return ucwords($driver);
    }

    /**
     * @param $aggregateName
     * @param int $page
     * @param int $maxPerPage
     *
     * @return array
     */
    public function stream($aggregateName, $page = 1, $maxPerPage = 25)
    {
        return ($this->streamCount($aggregateName)) ? array_slice($this->repo->byName($aggregateName)->events(), ($page - 1) * $maxPerPage, $maxPerPage) : [];
    }

    /**
     * @param $aggregateName
     *
     * @return int
     */
    public function streamCount($aggregateName)
    {
        return ($this->repo->exists($aggregateName)) ? count($this->repo->byName($aggregateName)->events()) : 0;
    }

    /**
     * @param $aggregateName
     * @param array EventInterface[] $events
     * @throws NotValidEventException
     */
    public function storeEvents($aggregateName, array $events = [])
    {
        $aggregate = $this->getAggregateFromName($aggregateName);
        foreach ($events as $event){
            if(!$event instanceof EventInterface){
                throw new NotValidEventException('Not a valid instance of EventInterface was provided.');
            }

            $aggregate->addEvent($event);
        }

        $this->repo->save($aggregate);

        if($this->elastic){
            $this->elastic->addAggregateToIndex($aggregate);
        }
    }

    /**
     * @param $aggregateName
     *
     * @return Aggregate
     */
    private function getAggregateFromName($aggregateName)
    {
        if($this->repo->exists($aggregateName)){
            return $this->repo->byName($aggregateName);
        }

        return new Aggregate(
            new AggregateId(),
            $aggregateName
        );
    }
}

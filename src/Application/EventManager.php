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
use SimpleEventStoreManager\Domain\Model\EventAggregate;
use SimpleEventStoreManager\Domain\Model\EventAggregateId;
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
     * @var array
     */
    private $connectionParams;

    /**
     * @var AggregateRepositoryInterface
     */
    private $repo;

    /**
     * @var int
     */
    private $returnType;

    /**
     * @var ElasticService
     */
    private $elastic;

    /**
     * @return EventManager
     */
    public static function build()
    {
        return new self();
    }

    /**
     * @param $driver
     * @return $this
     * @throws NotSupportedDriverException
     */
    public function setDriver($driver)
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

        return $this;
    }

    /**
     * @param array $parameters
     * @return $this
     */
    public function setConnection(array $parameters = [])
    {
        $this->connectionParams = $parameters;
        $this->setRepo();

        return $this;
    }

    /**
     * setRepo.
     */
    private function setRepo()
    {
        $aggregateRepo = 'SimpleEventStoreManager\Infrastructure\Persistence\\'.$this->normalizeDriverName($this->driver).'AggregateRepository';
        $driver = 'SimpleEventStoreManager\Infrastructure\Drivers\\'.$this->normalizeDriverName($this->driver).'Driver';
        $instance = (new $driver($this->connectionParams))->instance();
        $this->repo = new $aggregateRepo($instance);
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
     * @param int $returnType
     * @return $this
     */
    public function setReturnType($returnType = AggregateRepositoryInterface::RETURN_AS_ARRAY)
    {
        $this->returnType = $returnType;

        return $this;
    }

    /**
     * @param array $hosts
     * @return $this
     */
    public function setElastic(array $hosts = [])
    {
        $this->elastic = new ElasticService(
            ClientBuilder::create()
                ->setHosts($hosts)
                ->build()
        );

        return $this;
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
        if($this->streamCount($aggregateName)) {
            switch ($this->returnType){
                case AggregateRepositoryInterface::RETURN_AS_ARRAY:
                    return array_slice($this->repo->byName($aggregateName, $this->returnType)['events'] , ($page - 1) * $maxPerPage, $maxPerPage);

                case AggregateRepositoryInterface::RETURN_AS_OBJECT:
                    return array_slice($this->repo->byName($aggregateName, $this->returnType)->events(), ($page - 1) * $maxPerPage, $maxPerPage);
            }
        }

        return [];
    }

    /**
     * @param $aggregateName
     *
     * @return int
     */
    public function streamCount($aggregateName)
    {
        if($this->repo->exists($aggregateName)) {
            switch ($this->returnType){
                case AggregateRepositoryInterface::RETURN_AS_ARRAY:
                    return count($this->repo->byName($aggregateName, $this->returnType)['events']);

                case AggregateRepositoryInterface::RETURN_AS_OBJECT:
                    return count($this->repo->byName($aggregateName, $this->returnType)->events());
            }
        }

        return 0;
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
     * @return EventAggregate
     */
    private function getAggregateFromName($aggregateName)
    {
        if($this->repo->exists($aggregateName)){
            return $this->repo->byName($aggregateName, AggregateRepositoryInterface::RETURN_AS_OBJECT);
        }

        return new EventAggregate(
            new EventAggregateId(),
            $aggregateName
        );
    }
}

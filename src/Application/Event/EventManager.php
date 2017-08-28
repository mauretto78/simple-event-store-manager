<?php
/**
 * This file is part of the Simple EventStore Manager package.
 *
 * (c) Mauro Cassani<https://github.com/mauretto78>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SimpleEventStoreManager\Application\Event;

use Elasticsearch\ClientBuilder;
use SimpleEventStoreManager\Application\Event\Exceptions\NotSupportedDriverException;
use SimpleEventStoreManager\Application\Event\Exceptions\NotSupportedReturnTypeException;
use SimpleEventStoreManager\Application\Event\Exceptions\NotValidEventException;
use SimpleEventStoreManager\Domain\Model\EventAggregate;
use SimpleEventStoreManager\Domain\Model\Contracts\EventAggregateRepositoryInterface;
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
     * @var EventAggregateRepositoryInterface
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
        $aggregateRepo = 'SimpleEventStoreManager\Infrastructure\Persistence\\'.$this->normalizeDriverName($this->driver).'EventAggregateRepository';
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
     * @return EventAggregateRepositoryInterface
     */
    public function getRepo()
    {
        return $this->repo;
    }

    /**
     * @param int $returnType
     * @return $this
     * @throws NotSupportedReturnTypeException
     */
    public function setReturnType($returnType = EventAggregateRepositoryInterface::RETURN_AS_ARRAY)
    {
        if (!in_array($returnType, [EventAggregateRepositoryInterface::RETURN_AS_ARRAY, EventAggregateRepositoryInterface::RETURN_AS_OBJECT])) {
            throw new NotSupportedReturnTypeException($returnType . ' is not a valid returnType value.');
        }

        $this->returnType = $returnType;

        return $this;
    }

    /**
     * @return int
     */
    public function getReturnType()
    {
        return $this->returnType;
    }

    /**
     * @param array $hosts
     * @return $this
     */
    public function setElasticServer(array $hosts = [])
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
     * @param array EventInterface[] $events
     * @throws NotValidEventException
     */
    public function storeEvents($aggregateName, array $events = [])
    {
        $aggregate = $this->checkIfAggregateExistsOrReturnNewInstance($aggregateName);
        foreach ($events as $event) {
            if (!$event instanceof EventInterface) {
                throw new NotValidEventException('Not a valid instance of EventInterface was provided.');
            }

            $aggregate->addEvent($event);
        }

        $this->repo->save($aggregate);

        if ($this->elastic) {
            $this->elastic->addAggregateToIndex($aggregate);
        }
    }

    /**
     * @param $aggregateName
     *
     * @return EventAggregate
     */
    private function checkIfAggregateExistsOrReturnNewInstance($aggregateName)
    {
        if ($this->repo->exists($aggregateName)) {
            return $this->repo->byName($aggregateName, EventAggregateRepositoryInterface::RETURN_AS_OBJECT);
        }

        return new EventAggregate($aggregateName);
    }
}

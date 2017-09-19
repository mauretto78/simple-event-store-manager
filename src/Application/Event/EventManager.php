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
use SimpleEventStoreManager\Domain\Model\Contracts\EventStoreRepositoryInterface;
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
     * @var EventStoreRepositoryInterface
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
            'dbal',
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
        $aggregateRepo = 'SimpleEventStoreManager\Infrastructure\Persistence\\'.$this->normalizeDriverName($this->driver).'EventStoreRepository';
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
     * @return EventStoreRepositoryInterface
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
    public function setReturnType($returnType = EventStoreRepositoryInterface::RETURN_AS_ARRAY)
    {
        if (false === $this->checkReturnType($returnType)) {
            throw new NotSupportedReturnTypeException($returnType . ' is not a valid returnType value.');
        }

        $this->returnType = $returnType;

        return $this;
    }

    /**
     * @param $returnType
     * @return bool
     */
    private function checkReturnType($returnType)
    {
        $allowedReturnTypeArray = [EventStoreRepositoryInterface::RETURN_AS_ARRAY, EventStoreRepositoryInterface::RETURN_AS_OBJECT];

        return in_array($returnType, $allowedReturnTypeArray);
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
     * @param array $events
     * @throws NotValidEventException
     */
    public function storeEvents(array $events = [])
    {
        /** @var EventInterface $event */
        foreach ($events as $event) {
            if (!$event instanceof EventInterface) {
                throw new NotValidEventException('Not a valid instance of EventInterface was provided.');
            }

            $this->repo->save($event);

            if ($this->elastic) {
                $this->elastic->addAggregateToIndex($event);
            }
        }
    }
}

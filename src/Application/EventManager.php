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

use SimpleEventStoreManager\Application\Exceptions\NotSupportedDriverException;
use SimpleEventStoreManager\Domain\Model\Contracts\AggregateRepositoryInterface;
use SimpleEventStoreManager\Domain\Model\Contracts\EventInterface;

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
     * StreamManager constructor.
     * @param string $driver
     * @param array $parameters
     */
    public function __construct($driver = 'mongo', array $parameters = [])
    {
        $this->setDriver($driver);
        $this->setRepo($driver, $parameters);
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
     * @param array $config
     */
    private function setRepo($driver, array $config = [])
    {
        $aggregateRepo = 'SimpleEventStoreManager\Infrastructure\Persistence\\'.$this->normalizeDriverName($driver).'\\'.$this->normalizeDriverName($driver).'AggregateRepository';
        $driver = 'SimpleEventStoreManager\Infrastructure\Drivers\\'.$this->normalizeDriverName($driver).'Driver';
        $instance = (new $driver($config))->instance();
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

    public function stream($aggregateName, $page = 1, $maxPerPage = 25)
    {
        /*$events = $this->query();

        return array_slice($events, ($page - 1) * $maxPerPage, $maxPerPage);*/
    }

    public function storeEvent($aggregateName, EventInterface $event)
    {
    }
}

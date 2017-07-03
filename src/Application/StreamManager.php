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

use SimpleEventStoreManager\Application\Exception\NotSupportedDriverException;
use SimpleEventStoreManager\Domain\Model\Contracts\EventStoreInterface;

class StreamManager
{
    /**
     * @var string
     */
    private $driver;

    /**
     * @var EventStoreInterface
     */
    private $eventStore;

    /**
     * StreamManager constructor.
     * @param string $driver
     * @param array $parameters
     */
    public function __construct($driver = 'mongo', array $parameters = [])
    {
        $this->setDriver($driver);
        $this->setEventStore($driver, $parameters);
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
    private function setEventStore($driver, array $config = [])
    {
        $eventStore = 'SimpleEventStoreManager\Infrastructure\Persistence\\'.$this->normalizeDriverName($driver).'EventStore';
        $driver = 'SimpleEventStoreManager\Infrastructure\Drivers\\'.$this->normalizeDriverName($driver).'Driver';
        $instance = (new $driver($config))->instance();
        $this->eventStore = new $eventStore($instance);
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
     * @return EventStoreInterface
     */
    public function eventStore()
    {
        return $this->eventStore;
    }
}

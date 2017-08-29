<?php
/**
 * This file is part of the Simple EventStore Manager package.
 *
 * (c) Mauro Cassani<https://github.com/mauretto78>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SimpleEventStoreManager\Infrastructure\Projector;

use SimpleEventStoreManager\Domain\Model\Contracts\EventInterface;
use SimpleEventStoreManager\Infrastructure\Projector\Exceptions\ProjectorHandleMethodDoesNotExistsException;
use SimpleEventStoreManager\Infrastructure\Projector\Exceptions\ProjectorRollbackMethodDoesNotExistsException;

abstract class Projector
{
    /**
     * @return array
     */
    abstract public function subcribedEvents();

    /**
     * {@inheritDoc}
     */
    public function handle(EventInterface $event)
    {
        $method = $this->getHandleMethod($event);
        if (! method_exists($this, $method)) {
            throw new ProjectorHandleMethodDoesNotExistsException(get_class($this) . ' does not implement ' . $method . ' method.');
        }

        $this->$method($event);
    }

    /**
     * @param $event
     * @return string
     */
    private function getHandleMethod($event)
    {
        return 'apply' . $this->getLastClassParts($event);
    }

    /**
     * @param $class
     * @return array
     */
    private function getLastClassParts($class)
    {
        $classParts = explode('\\', get_class($class));

        return end($classParts);
    }

    /**
     * {@inheritDoc}
     */
    public function rollback(EventInterface $event)
    {
        $method = $this->getRollbackMethod($event);
        if (! method_exists($this, $method)) {
            throw new ProjectorRollbackMethodDoesNotExistsException(get_class($this) . ' does not implement ' . $method . ' method.');
        }

        $this->$method($event);
    }

    /**
     * @param $event
     * @return string
     */
    private function getRollbackMethod($event)
    {
        return 'rollback' . $this->getLastClassParts($event);
    }
}

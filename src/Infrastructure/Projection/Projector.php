<?php
/**
 * This file is part of the Simple EventStore Manager package.
 *
 * (c) Mauro Cassani<https://github.com/mauretto78>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SimpleEventStoreManager\Infrastructure\Projection;

use SimpleEventStoreManager\Domain\Model\Contracts\EventInterface;
use SimpleEventStoreManager\Infrastructure\Projection\Exceptions\ProjectorHandleMethodDoesNotExistsException;

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
            throw new ProjectorHandleMethodDoesNotExistsException(get_class($event) . ' does not implement ' . $method . ' method.');
        }

        $this->$method($event);
    }

    /**
     * @param $event
     * @return string
     */
    private function getHandleMethod($event)
    {
        $classParts = explode('\\', get_class($event));

        return 'apply' . end($classParts);
    }
}

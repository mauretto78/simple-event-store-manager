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
use SimpleEventStoreManager\Domain\Model\EventAggregate;
use SimpleEventStoreManager\Infrastructure\Projector\Exceptions\ProjectorDoesNotExistsException;

class ProjectionManager
{
    /**
     * @var Projector[]
     */
    private $projectors;

    /**
     * @param Projector $projector
     */
    public function register(Projector $projector)
    {
        foreach ($projector->subcribedEvents() as $subcribedEvent) {
            $this->projectors[$subcribedEvent] = $projector;
        }
    }

    /**
     * @param EventInterface $event
     * @throws ProjectorDoesNotExistsException
     */
    public function project(EventInterface $event)
    {
        if (!isset($this->projectors[get_class($event)])) {
            throw new ProjectorDoesNotExistsException('No Projector found for event ' . get_class($event) . '.');
        }

        $this->projectors[get_class($event)]->handle($event);
    }

    /**
     * @param EventAggregate $aggregate
     * @throws ProjectorDoesNotExistsException
     */
    public function projectFromAnEventAggregate(EventAggregate $aggregate)
    {
        foreach ($aggregate->events() as $event) {
            $this->project($event);
        }
    }
}

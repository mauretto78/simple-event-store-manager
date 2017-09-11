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
use SimpleEventStoreManager\Domain\Model\Contracts\EventStoreRepositoryInterface;
use SimpleEventStoreManager\Domain\Model\EventAggregate;
use SimpleEventStoreManager\Domain\Model\AggregateUuid;
use SimpleEventStoreManager\Infrastructure\Projector\Exceptions\ProjectorDoesNotExistsException;

class ProjectionManager
{
    /**
     * @var Projector[]
     */
    private $projectors;

    /**
     * @var EventStoreRepositoryInterface
     */
    private $repo;

    /**
     * ProjectionManager constructor.
     * @param EventStoreRepositoryInterface $repo
     */
    public function __construct(EventStoreRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

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
     * @param AggregateUuid $uuid
     */
    public function projectFromAnEventAggregate(AggregateUuid $uuid)
    {
        /** @var EventInterface $event */
        foreach ($this->repo->byUuid($uuid, EventStoreRepositoryInterface::RETURN_AS_OBJECT) as $event) {
            try {
                $this->project($event);
            } catch (ProjectorDoesNotExistsException $e) {
                $this->rollbackAnEventAggregate($uuid);
            }
        }
    }

    /**
     * @param EventInterface $event
     * @throws ProjectorDoesNotExistsException
     */
    public function rollback(EventInterface $event)
    {
        if (!isset($this->projectors[get_class($event)])) {
            throw new ProjectorDoesNotExistsException('No Projector found for event ' . get_class($event) . '.');
        }

        $this->projectors[get_class($event)]->rollback($event);
    }

    /**
     * @param AggregateUuid $eventUuid
     */
    public function rollbackAnEventAggregate(AggregateUuid $eventUuid)
    {
        foreach ($this->repo->byUuid($eventUuid, EventStoreRepositoryInterface::RETURN_AS_OBJECT) as $event) {
            $this->rollback($event);
        }
    }
}

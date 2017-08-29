<?php
/**
 * This file is part of the Simple EventStore Manager package.
 *
 * (c) Mauro Cassani<https://github.com/mauretto78>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use SimpleEventStoreManager\Infrastructure\Projector\Projector;
use SimpleEventStoreManager\Domain\Model\Event;
use SimpleEventStoreManager\Domain\Model\EventAggregate;
use SimpleEventStoreManager\Infrastructure\Projector\ProjectionManager;
use SimpleEventStoreManager\Tests\BaseTestCase;

class ProjectorManagerTest extends BaseTestCase
{
    /**
     * @test
     * @expectedException \SimpleEventStoreManager\Infrastructure\Projector\Exceptions\ProjectorDoesNotExistsException
     * @expectedExceptionMessage No Projector found for event UserWasCreated.
     */
    public function it_throws_ProjectorDoesNotExistsException_if_projector_does_not_subscribe_the_event()
    {
        $userProjector = new UserProjectorWithNoSubcribedEvents();
        $userWasCreatedEvent = new UserWasCreated(
            'UserWasCreated',
            [
                'id' => 23,
                'name' => 'Mauro Cassani',
                'email' => 'mauro@gmail.com',
            ]
        );

        $userEventAggregate = new EventAggregate('user-23');
        $userEventAggregate->addEvent($userWasCreatedEvent);

        $projectorManger = new ProjectionManager();
        $projectorManger->register($userProjector);
        $projectorManger->projectFromAnEventAggregate($userEventAggregate);
    }

    /**
     * @test
     * @expectedException \SimpleEventStoreManager\Infrastructure\Projector\Exceptions\ProjectorHandleMethodDoesNotExistsException
     * @expectedExceptionMessage UserProjectorWithNoApplyMethod does not implement applyUserWasCreated method.
     */
    public function it_throws_ProjectorHandleMethodDoesNotExistsException_if_projector_does_not_implement_expeceted_apply_method()
    {
        $userProjector = new UserProjectorWithNoApplyMethod();
        $userWasCreatedEvent = new UserWasCreated(
            'UserWasCreated',
            [
                'id' => 23,
                'name' => 'Mauro Cassani',
                'email' => 'mauro@gmail.com',
            ]
        );

        $userEventAggregate = new EventAggregate('user-23');
        $userEventAggregate->addEvent($userWasCreatedEvent);

        $projectorManger = new ProjectionManager();
        $projectorManger->register($userProjector);
        $projectorManger->projectFromAnEventAggregate($userEventAggregate);
    }

    /**
     * @test
     * @expectedException \SimpleEventStoreManager\Infrastructure\Projector\Exceptions\ProjectorRollbackMethodDoesNotExistsException
     * @expectedExceptionMessage UserProjectorWithNoRollbackMethod does not implement rollbackUserWasCreated method.
     */
    public function it_throws_ProjectorHandleMethodDoesNotExistsException_if_projector_does_not_implement_expeceted_rollback_method()
    {
        $userProjector = new UserProjectorWithNoRollbackMethod();
        $userWasCreatedEvent = new UserWasCreated(
            'UserWasCreated',
            [
                'id' => 23,
                'name' => 'Mauro Cassani',
                'email' => 'mauro@gmail.com',
            ]
        );

        $userEventAggregate = new EventAggregate('user-23');
        $userEventAggregate->addEvent($userWasCreatedEvent);

        $projectorManger = new ProjectionManager();
        $projectorManger->register($userProjector);
        $projectorManger->projectFromAnEventAggregate($userEventAggregate);
        $projectorManger->rollbackAnEventAggregate($userEventAggregate);
    }

    /**
     * @test
     */
    public function it_should_correcly_handle_events_from_the_event_aggregate()
    {
        $userProjector = new UserProjector();
        $userWasCreatedEvent = new UserWasCreated(
            'UserWasCreated',
            [
                'id' => 23,
                'name' => 'Mauro Cassani',
                'email' => 'mauro@gmail.com',
            ]
        );

        $userEventAggregate = new EventAggregate('user-23');
        $userEventAggregate->addEvent($userWasCreatedEvent);

        $projectorManger = new ProjectionManager();
        $projectorManger->register($userProjector);
        $projectorManger->projectFromAnEventAggregate($userEventAggregate);

        $this->assertCount(1, $userProjector->getUsers());

        $projectorManger->rollbackAnEventAggregate($userEventAggregate);

        $this->assertCount(0, $userProjector->getUsers());
    }
}

class UserProjector extends Projector
{
    private $users = [];

    public function subcribedEvents()
    {
        return [
            UserWasCreated::class
        ];
    }

    public function applyUserWasCreated(UserWasCreated $event)
    {
        $eventId = (string) $event->id();

        $this->users[$eventId] = $event->body();
    }

    public function rollbackUserWasCreated(UserWasCreated $event)
    {
        $eventId = (string) $event->id();

        unset($this->users[$eventId]);
    }

    public function getUsers()
    {
        return $this->users;
    }
}

class UserProjectorWithNoRollbackMethod extends Projector
{
    private $users = [];

    public function subcribedEvents()
    {
        return [
            UserWasCreated::class
        ];
    }

    public function applyUserWasCreated(UserWasCreated $event)
    {
        $eventId = (string) $event->id();

        $this->users[$eventId] = $event->body();
    }

    public function getUsers()
    {
        return $this->users;
    }
}

class UserProjectorWithNoSubcribedEvents extends Projector
{
    private $users = [];

    public function subcribedEvents()
    {
        return [];
    }

    public function applyUserWasCreated(UserWasCreated $event)
    {
        $eventId = (string) $event->id();

        $this->users[$eventId] = $event->body();
    }

    public function rollbackUserWasCreated(UserWasCreated $event)
    {
        $eventId = (string) $event->id();

        unset($this->users[$eventId]);
    }

    public function getUsers()
    {
        return $this->users;
    }
}

class UserProjectorWithNoApplyMethod extends Projector
{
    private $users = [];

    public function subcribedEvents()
    {
        return [
            UserWasCreated::class
        ];
    }

    public function getUsers()
    {
        return $this->users;
    }
}

class UserWasCreated extends Event
{
}

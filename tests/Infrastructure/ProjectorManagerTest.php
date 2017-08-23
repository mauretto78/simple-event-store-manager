<?php
/**
 * This file is part of the EventStoreManager package.
 *
 * (c) Mauro Cassani<https://github.com/mauretto78>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use SimpleEventStoreManager\Domain\Model\Event;
use SimpleEventStoreManager\Domain\Model\EventAggregate;
use SimpleEventStoreManager\Domain\Model\EventAggregateId;
use SimpleEventStoreManager\Domain\Model\EventId;
use SimpleEventStoreManager\Infrastructure\Projection\ProjectionManager;
use SimpleEventStoreManager\Infrastructure\Projection\Projector;
use SimpleEventStoreManager\Tests\BaseTestCase;

class ProjectorManagerTest extends BaseTestCase
{
    /**
     * @test
     * @expectedException \SimpleEventStoreManager\Infrastructure\Projection\Exceptions\ProjectorDoesNotExistsException
     * @expectedExceptionMessage No Projector found for event UserWasCreated.
     */
    public function it_throws_ProjectorDoesNotExistsException_if_projector_does_not_subscribe_the_event()
    {
        $userProjector = new UserProjectorWithNoSubcribedEvents();
        $userWasCreatedEvent = new UserWasCreated(
            new EventId(),
            'UserWasCreated',
            [
                'id' => 23,
                'name' => 'Mauro Cassani',
                'email' => 'mauro@gmail.com',
            ]
        );

        $userEventAggregate = new EventAggregate(
            new EventAggregateId(),
            'user-23'
        );
        $userEventAggregate->addEvent($userWasCreatedEvent);

        $projectorManger = new ProjectionManager();
        $projectorManger->register($userProjector);
        $projectorManger->projectFromAnEventAggregate($userEventAggregate);
    }

    /**
     * @test
     * @expectedException \SimpleEventStoreManager\Infrastructure\Projection\Exceptions\ProjectorHandleMethodDoesNotExistsException
     * @expectedExceptionMessage UserWasCreated does not implement applyUserWasCreated method.
     */
    public function it_throws_ProjectorHandleMethodDoesNotExistsException_if_projector_does_not_implement_expeceted_apply_method()
    {
        $userProjector = new UserProjectorWithNoApplyMethod();
        $userWasCreatedEvent = new UserWasCreated(
            new EventId(),
            'UserWasCreated',
            [
                'id' => 23,
                'name' => 'Mauro Cassani',
                'email' => 'mauro@gmail.com',
            ]
        );

        $userEventAggregate = new EventAggregate(
            new EventAggregateId(),
            'user-23'
        );
        $userEventAggregate->addEvent($userWasCreatedEvent);

        $projectorManger = new ProjectionManager();
        $projectorManger->register($userProjector);
        $projectorManger->projectFromAnEventAggregate($userEventAggregate);
    }

    /**
     * @test
     */
    public function it_should_correcly_handle_events_from_the_event_aggregate()
    {
        $userProjector = new UserProjector();
        $userWasCreatedEvent = new UserWasCreated(
            new EventId(),
            'UserWasCreated',
            [
                'id' => 23,
                'name' => 'Mauro Cassani',
                'email' => 'mauro@gmail.com',
            ]
        );

        $userEventAggregate = new EventAggregate(
            new EventAggregateId(),
            'user-23'
        );
        $userEventAggregate->addEvent($userWasCreatedEvent);

        $projectorManger = new ProjectionManager();
        $projectorManger->register($userProjector);
        $projectorManger->projectFromAnEventAggregate($userEventAggregate);

        $this->assertCount(1, $userProjector->getUsers());
        $this->assertArrayHasKey('id', $userProjector->getUsers()[0]);
        $this->assertArrayHasKey('name', $userProjector->getUsers()[0]);
        $this->assertArrayHasKey('email', $userProjector->getUsers()[0]);
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
        $this->users[] = $event->body();
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
        $this->users[] = $event->body();
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

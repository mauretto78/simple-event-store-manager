[Back to index](https://github.com/mauretto78/simple-event-store-manager/blob/master/README.md)

## Event Projections

### Extending Projector Class

Create a Projector and extend `Projector` abstract class. 

You must implement three methods:
 
 * `subcribedEvents` method to subscribe for events to handle
 * `applyNameOfEvent` method for handling a subscribed event
 * `rollbackNameOfEvent` method for rollback changes made by s subscribed event

Consider this example:

```php
use SimpleEventStoreManager\Infrastructure\Projector\Projector;

class UserProjector extends Projector
{
    private $repo;
    
    public function __construct($repo)
    {
        $this->repo = $repo;
    }

    public function subcribedEvents()
    {
        return [
            UserWasCreated::class
        ];
    }

    public function applyUserWasCreated(UserWasCreated $event)
    {
        $userData = $event->body();
        $user = new User(
            $userData['id'],
            $userData['name'],
            $userData['email'],
        );
    
        $this->repo->save($user);
    }
    
    public function rollbackUserWasCreated(UserWasCreated $event)
    {
        $userData = $event->body();
    
        $this->repo->delete($userData['id']);
    }
}

class UserWasCreated extends Event
{
}
```

### Projecting Events from an Event

You can project events from an entire aggregate using `ProjectionManager` class:

```php
// ..
$userProjector = new UserProjector();
$userWasCreatedEvent = new UserWasCreated(
    new AggregateUuid(), 
    'UserWasCreated',
    [
        'id' => 23,
        'name' => 'Mauro Cassani',
        'email' => 'mauro@gmail.com',
    ]
);

$repo = new InMemoryEventStoreRepository();

$projectorManger = new ProjectionManager($repo);
$projectorManger->register($userProjector);
$projectorManger->project($userWasCreatedEvent);

```

### Projecting Events from an Aggregate

You can project events from an entire aggregate:

```php
$uuid = new AggregateUuid();

$userProjector = new UserProjector();
$userWasCreatedEvent = new UserWasCreated(
    $uuid, 
    'UserWasCreated',
    [
        'id' => 23,
        'name' => 'Mauro Cassani',
        'email' => 'mauro@gmail.com',
    ]
);

// .. 
$userEventAggregate = $eventQuery->fromAggregate($uuid);

$projectorManger = new ProjectionManager();
$projectorManger->register($userProjector);
$projectorManger->projectFromAnEventAggregate($userEventAggregate);

```

### Rollback Events from an Aggregate

You can rollback the changes made from aggregate events on your read model:

```php
// .. 

$projectorManger->rollbackAnEventAggregate($userEventAggregate);

```

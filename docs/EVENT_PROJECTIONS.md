[Back to index](https://github.com/mauretto78/simple-event-store-manager/blob/master/README.md)

## Event Projections

### Extending Projector Class

Create a Projector and extend `Projector` abstract class. You must implement `subcribedEvents` method to subscribe for events to handle. Please note that you must implement `applyNameOfEvent` method for handling subscribed events:

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
}

class UserWasCreated extends Event
{
}
```

### Projecting Events from an Event

You can project events from an entire aggregate:

```php
$userProjector = new UserProjector();
$userWasCreatedEvent = new UserWasCreated(
    'UserWasCreated',
    [
        'id' => 23,
        'name' => 'Mauro Cassani',
        'email' => 'mauro@gmail.com',
    ]
);

$projectorManger = new ProjectionManager();
$projectorManger->register($userProjector);
$projectorManger->project($userWasCreatedEvent);

```

### Projecting Events from an Aggregate

You can project events from an entire aggregate:

```php
$userProjector = new UserProjector();
$userWasCreatedEvent = new UserWasCreated(
    'UserWasCreated',
    [
        'id' => 23,
        'name' => 'Mauro Cassani',
        'email' => 'mauro@gmail.com',
    ]
);

// .. 
$userEventAggregate = $eventQuery->fromAggregate('user-23');

$projectorManger = new ProjectionManager();
$projectorManger->register($userProjector);
$projectorManger->projectFromAnEventAggregate($userEventAggregate);

```

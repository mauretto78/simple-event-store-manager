[Back to index](https://github.com/mauretto78/simple-event-store-manager/blob/master/README.md)

## Recording Events

### Using EventRecorder

Use `record` method to register your Events, and then release them with `releaseEvents` method:

```php
// ..

$event = new Event(
    $name,
    $body
);

$eventRecorder = new EventRecorder();
$eventRecorder->record($event);

// ..
$eventRecorder->releaseEvents();

```

### Using EventRecorderCapabilities trait

Consider this example:

```php
class DummyEntity
{
    use EventRecorderCapabilities;

    private $id;
    private $name;
    private $email;

    public function __construct(
        $id,
        $name,
        $email
    )
    {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;

        $this->record(
            new DummyEntityWasCreated($this)
        );
    }
}

// ...
class DummyEntityWasCreated implements EventInterface
{
    private $id;
    private $name;
    private $body;
    private $occurred_on;
        
    public function __construct(
        $body
    ) {
        $this->id = new EventId();
        $this->name = get_class($this);
        $this->body = $body;
        $this->occurred_on = ($occurred_on) ? new \DateTimeImmutable($occurred_on) : new \DateTimeImmutable();
    }
    
    public function id()
    {
        return $this->id;
    }

    public function name()
    {
        return $this->name;
    }

    public function body()
    {
        return $this->body;
    }

    public function occurredOn()
    {
        return $this->occurred_on;
    }
}
```

Finally, to release events:

```php

$dummyEntity = new DummyEntity(
    'John Doe',
    'johndoe@gmail.com'
);

$releasedEvents = $dummyEntity->releaseEvents();

```
[Back to index](https://github.com/mauretto78/simple-event-store-manager/blob/master/README.md)

## Recording Events

### Using EventRecorder

Use `record` method to register your Events, and then release them with `releaseEvents` method:

```php
// ..

$event = new Event(
    $uuid,
    $type,
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
class DummyEntityWasCreated extends Event
{
}
```

Finally, to release events:

```php

$dummyEntity = new DummyEntity(
    23,
    'John Doe',
    'johndoe@gmail.com'
);

$releasedEvents = $dummyEntity->releaseEvents();

```
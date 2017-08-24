# Simple EventStore Manager

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/3d6db2b3-db42-4155-97ed-2c28cec1c998/mini.png)](https://insight.sensiolabs.com/projects/3d6db2b3-db42-4155-97ed-2c28cec1c998)
[![Build Status](https://travis-ci.org/mauretto78/simple-event-store-manager.svg?branch=master)](https://travis-ci.org/mauretto78/simple-event-store-manager)
[![Coverage Status](https://coveralls.io/repos/github/mauretto78/simple-event-store-manager/badge.svg?branch=master)](https://coveralls.io/github/mauretto78/simple-event-store-manager?branch=master)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/ad9fb8b8c1304a149a8507926a03d44b)](https://www.codacy.com/app/mauretto78/simple-event-store-manager?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=mauretto78/simple-event-store-manager&amp;utm_campaign=Badge_Grade)
[![license](https://img.shields.io/github/license/mauretto78/simple-event-store-manager.svg)]()
[![Packagist](https://img.shields.io/packagist/v/mauretto78/simple-event-store-manager.svg)]()

Simple EventStore Manager allows you to store your Domain Events and easlity retrieve them.

## Basic Usage

### Instantiate EventManager

To use `EventManager`:

```php
use SimpleEventStoreManager\Application\EventManager;
use SimpleEventStoreManager\Domain\Model\Contracts\AggregateRepositoryInterface;

$eventManager = EventManager::build()
    ->setDriver('mongo')
    ->setConnection($params)
    ->setReturnType(AggregateRepositoryInterface::RETURN_AS_OBJECT);
    
```

Avaliable drivers:

* `in-memory` 
* `mongo`
* `pdo` 
* `redis` 

You can choose if you want to return event aggregates as objects or as simple associative arrays.

### Store Events

Please note that your events MUST implement `EventInterface`. You can use the standard `Event` class or create your own events.

You MUST specify the **name of the aggregate** to which the events belong.

Consider this full example:

```php
use SimpleEventStoreManager\Application\EventManager;
use SimpleEventStoreManager\Domain\Model\EventId;
use SimpleEventStoreManager\Domain\Model\Event;

$myEvent = new Event(
    new EventId(),
    'Fully\\Qualified\\Event\\Name',
    [
        'key' => 'value',
        'key2' => 'value2',
        'key3' => 'value3',
    ]
);

$myEvent2 = new Event(
    new EventId(),
    'Fully\\Qualified\\Event\\Name2',
    [
        'key' => 'value',
        'key2' => 'value2',
        'key3' => 'value3',
    ]
);

// ..
$eventManager->storeEvents(
    'Your Aggregate Name',
    [
        $myEvent,
        $myEvent2
    ]
);

```

### Get Event Streams

You can get access to stored events only by the name of the aggregate they belongs. 

```php
$stream = $eventManager->stream('Your Aggregate Name', $page, $maxPerPage);
foreach($stream as $event){
    // ..
}

```
Please note you can pass to `stream` method two optional arguments, `$page` and `$maxPerPage`.

## Sending events to Elastic

You can send events to an ElasticSearch server. Please refer to [Elastic PHP official page](https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/_configuration.html) to get more details about hosts configuration.

Take a look at this example:

```php
$eventManager = EventManager::build()
    ->setDriver('mongo')
    ->setConnection($params)
    ->setReturnType(AggregateRepositoryInterface::RETURN_AS_ARRAY)
    ->setElastic([
         'host' => 'localhost',
         'port' => '9200'
     ]);


// ..
$eventManager->storeEvents(
    'Your Aggregate Name',
    [
        $myEvent,
        $myEvent2
    ]
);

```

Now events will automatically be sent to Elastic server. 

Events are indexed this way, look at the example:

```php
Array
(
    'index' => 'aggregate-name' // Aggregate name
    'type' => 'UserWasCreated', // Event class
    'id' => 'c4a760a8-dbcf-5254-a0d9-6a4474bd1b62', // eventId
    'body' => [
            'name' => 'Mauro', 
            'email' => 'mauretto@gmail.com' 
            ...
        ] // Full event body
)
```

## Recording Events

You can record your Domain Events using `EventRecorder` class or `EventRecorderCapabilities` trait directly into your Entities.

### EventRecorder

Use `record` method to register your Events, and then release them with `releaseEvents` method:

```php
// ..

$event = new Event(
    $eventId,
    $name,
    $body
);

$eventRecorder = new EventRecorder();
$eventRecorder->record($event);

// ..
$eventRecorder->releaseEvents();

```

### EventRecorderCapabilities trait

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
            new DummyEntityWasCreated(
                new EventId(),
                $this
            )
        );
    }
}

// ...
class DummyEntityWasCreated implements EventInterface
{
    private $id;
    
    private $body;
        
    public function __construct(
        EventId $id,
        $body
    ) {
        $this->id = $id;
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
    12,
    'John Doe',
    'johndoe@gmail.com'
);

$releasedEvents = $dummyEntity->releaseEvents();

```

## Get Event Streams

You can use `EventQuery` to easily create a simple API endpoint to get event streams.

In [examples folder](https://github.com/mauretto78/simple-event-store-manager/tree/master/examples) you will find a simple example of an API implementation. Here is the full code:

```php
use JMS\Serializer\SerializerBuilder;
use SimpleEventStoreManager\Application\EventApiBuilder;
use SimpleEventStoreManager\Application\EventManager;
use SimpleEventStoreManager\Domain\Model\Event;
use SimpleEventStoreManager\Domain\Model\EventId;
use SimpleEventStoreManager\Infrastructure\DataTransformers\JsonEventDataTransformer;
use Symfony\Component\HttpFoundation\Request;

require __DIR__.'/../app/bootstrap.php';

$request = Request::createFromGlobals();

// instantiate $eventsQuery
$eventManager = EventManager::build()
    ->setDriver('mongo')
    ->setConnection($parameters)
    ->setReturnType(AggregateRepositoryInterface::RETURN_AS_ARRAY)
    ->setElastic($elastic);

$eventRepresentation = new EventQuery(
    $eventManager,
    // here you can use:
    // - JsonEventDataTransformer
    // - YamlEventDataTransformer
    // - XMLEventDataTransformer
    new JsonEventDataTransformer(
        SerializerBuilder::create()->build(),
        $request
    )
);

// send Response
$page = (null !== $page = $request->query->get('page')) ? $page : 1;
$response = $eventRepresentation->aggregate($request->query->get('aggregate'), $page);
$response->send();

```

When a page is full, an **infinite cache** is automatically set.

Please note you can choose JSON, XML or YAML format for data representation.

## Requirements

* PHP 5.6.6 +
* MySQL 5.6 + (if you use `pdo` driver)
* MongoDB 3.0 + (if you use `mongo` driver)
* Redis 3.2 + (if you use `redis` driver)

## Support

If you found an issue or had an idea please refer [to this section](https://github.com/mauretto78/simple-event-store-manager/issues).

## Authors

* **Mauro Cassani** - [github](https://github.com/mauretto78)

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details

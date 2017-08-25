[Back to index](https://github.com/mauretto78/simple-event-store-manager/blob/master/README.md)

## Basic Usage

### Instantiate EventManager

To use `EventManager`:

```php
use SimpleEventStoreManager\Application\Event\EventManager;
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
use SimpleEventStoreManager\Application\Event\EventManager;
use SimpleEventStoreManager\Domain\Model\EventId;
use SimpleEventStoreManager\Domain\Model\Event;

$myEvent = new Event(
    'Fully\\Qualified\\Event\\Name',
    [
        'key' => 'value',
        'key2' => 'value2',
        'key3' => 'value3',
    ]
);

$myEvent2 = new Event(
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

### Sending events to an ElasticSearch server

You can send events to an ElasticSearch server. Please refer to [Elastic PHP official page](https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/_configuration.html) to get more details about hosts configuration.

Take a look at this example:

```php
$eventManager = EventManager::build()
    ->setDriver('mongo')
    ->setConnection($params)
    ->setReturnType(AggregateRepositoryInterface::RETURN_AS_ARRAY)
    ->setElasticServer([
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
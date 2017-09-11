[Back to index](https://github.com/mauretto78/simple-event-store-manager/blob/master/README.md)

## Event Query

### Get a stream from a single Event Aggregate

You can get an event stream from a single aggregate:

```php
use SimpleEventStoreManager\Application\Event\EventQuery;

$eventQuery = new EventQuery($eventManger);

$stream = $eventQuery->fromAggregate('your-aggregate-uuid');
foreach($stream as $event){
    // ..
}

```

### Get a stream from more Event Aggregates

You can get an event stream from an array of aggregates: 

```php
// ..
$stream = $eventQuery->fromAggregates([
    'your-aggregate-uuid',
    'another-aggregate-uuid'
]);

foreach($stream as $event){
    // ..
}

```

### Query Events

You can perform queries on events from one or more aggregates:

```php
// ..
$stream = $eventQuery->fromAggregate('your-aggregate-uuid');
$query = $eventQuery->query($stream, [
    'type' => 'Fully\\Qualified\\Event\\Name',
    'body.title' => 'Event Name',
]);

foreach($query as $event){
    // ..
}

```

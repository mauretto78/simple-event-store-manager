# Simple EventStore Manager

Simple EventStore Manager allows you to store your Domain Events and easlity retrieve them.

## Drivers

Avaliable drivers:

* `in-memory` 
* `mongo` (default driver) 
* `pdo` 
* `redis` 

## Basic Usage

Your Events MUST be an instance `SimpleEventStoreManager\Domain\Model\Contracts\Event`.

Example:

```php
use SimpleEventStoreManager\Domain\Model\Contracts\EventId;
use SimpleEventStoreManager\Domain\Model\Contracts\Event;

$myEvent = new Event(
    new EventId(),
    $name,
    $body
);

```

To use StreamManager:

```php
use SimpleEventStoreManager\Application\StreamManager;
use SimpleEventStoreManager\Domain\Model\Contracts\EventId;
use SimpleEventStoreManager\Domain\Model\Contracts\Event;

$myEventId = new EventId();
$myEvent = new Event(
    $myEventId,
    $name,
    $body
);

// instance of StreamManager
$streamManager = new StreamManager('pdo', $params);

// store an Event
$streamManager->eventStore()->store($myEvent);

// restore an Event
$streamManager->eventStore()->restore($myEventId);

// get Events count
$streamManager->eventStore()->eventsCount();

// retrive Event Stream
// you can pass a $from and $to dates
$streamManager->eventStore()->eventsInRangeDate();

```

## Simple API Example

In [examples folder](https://github.com/mauretto78/simple-event-store-manager/tree/master/examples) you will find a simple example of an API implementation.

## Support

If you found an issue or had an idea please refer [to this section](https://github.com/mauretto78/simple-event-store-manager/issues).

## Authors

* **Mauro Cassani** - [github](https://github.com/mauretto78)

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details

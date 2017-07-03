# Simple EventStore Manager

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/5bf086af-e45e-48f6-98dd-b7d5ea074130/mini.png)](https://insight.sensiolabs.com/projects/5bf086af-e45e-48f6-98dd-b7d5ea074130)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/ad9fb8b8c1304a149a8507926a03d44b)](https://www.codacy.com/app/mauretto78/simple-event-store-manager?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=mauretto78/simple-event-store-manager&amp;utm_campaign=Badge_Grade)
[![license](https://img.shields.io/github/license/mauretto78/simple-event-store-manager.svg)]()
[![Packagist](https://img.shields.io/packagist/v/mauretto78/simple-event-store-manager.svg)]()

Simple EventStore Manager allows you to store your Domain Events and easlity retrieve them.

## Drivers

Avaliable drivers:

* `in-memory` 
* `mongo` (default driver) 
* `pdo` 
* `redis` 

## Basic Usage

To use StreamManager:

```php
// $params connection array
$streamManager = new StreamManager('pdo', $pdo_params);

```

Please note that your events MUST be an instance `SimpleEventStoreManager\Domain\Model\Contracts\Event`.

Consider this full example:

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

# Simple EventStore Manager

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/3d6db2b3-db42-4155-97ed-2c28cec1c998/mini.png)](https://insight.sensiolabs.com/projects/3d6db2b3-db42-4155-97ed-2c28cec1c998)
[![Build Status](https://travis-ci.org/mauretto78/simple-event-store-manager.svg?branch=master)](https://travis-ci.org/mauretto78/simple-event-store-manager)
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

To use `EventsManager`:

```php
use SimpleEventStoreManager\Application\EventsManager;

// $driver driver
// $params connection array
$eventsManager = new EventsManager('pdo', $pdo_params);

```

Please note that your events MUST be an instance `SimpleEventStoreManager\Domain\Model\Event`.

Consider this full example:

```php
use SimpleEventStoreManager\Application\EventsManager;
use SimpleEventStoreManager\Domain\Model\EventId;
use SimpleEventStoreManager\Domain\Model\Event;

$myEventId = new EventId();
$myEvent = new Event(
    $myEventId,
    'Fully\\Qualified\\Event\\Name',
    [
        'key' => 'value',
        'key2' => 'value2',
        'key3' => 'value3',
    ]
);

// store an event
$eventsManager->eventStore()->store($myEvent);

// restore an event
$eventsManager->eventStore()->restore($myEventId);

// get events count
$eventsManager->eventStore()->eventsCount();

```

## Query Stored Events

You can query events in a range of dates:

```php
// ..

$eventsManager->eventStore()->eventsInRangeDate(
    new \DateTimeImmutable('yesterday')
    new \DateTimeImmutable('now')
);

```

## API Implementation

In [examples folder](https://github.com/mauretto78/simple-event-store-manager/tree/master/examples) you will find a simple example of an API implementation. Here is the full code:

```php
use JMS\Serializer\SerializerBuilder;
use SimpleEventStoreManager\Application\EventsQuery;
use SimpleEventStoreManager\Application\StreamManager;
use SimpleEventStoreManager\Infrastructure\DataTransformer\JsonEventDataTransformer;

require __DIR__.'/../app/bootstrap.php';

// instantiate EventsQuery
$eventManager = new StreamManager('mongo', $config['mongo']);
$eventsQuery = new EventsQuery(
    $eventManager->eventStore(),
    new JsonEventDataTransformer(
        SerializerBuilder::create()->build()
    )
);

// send Response
$page = (isset($_GET['pag'])) ? (int) $_GET['pag'] : 1;
$maxPerPage = 10;
$response = $eventsQuery->query($page, $maxPerPage);
$response->send();

```

## Support

If you found an issue or had an idea please refer [to this section](https://github.com/mauretto78/simple-event-store-manager/issues).

## Authors

* **Mauro Cassani** - [github](https://github.com/mauretto78)

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details

[Back to index](https://github.com/mauretto78/simple-event-store-manager/blob/master/README.md)

## Event Representation

You can use `EventRepresentation` class to create a representation of stored events, so you can easily create a simple API endpoint to get event streams.

In [examples folder](https://github.com/mauretto78/simple-event-store-manager/tree/master/examples) you will find a simple example of an API implementation. Here is the full code:

```php
use JMS\Serializer\SerializerBuilder;
use SimpleEventStoreManager\Application\Event\EventManager;
use SimpleEventStoreManager\Application\Event\EventRepresentation;
use SimpleEventStoreManager\Domain\Model\Contracts\EventAggregateRepositoryInterface;
use SimpleEventStoreManager\Infrastructure\DataTransformers\JsonEventDataTransformer;
use Symfony\Component\HttpFoundation\Request;

require __DIR__.'/../app/bootstrap.php';

$request = Request::createFromGlobals();

// instantiate $eventsQuery
$eventManager = EventManager::build()
    ->setDriver('mongo')
    ->setConnection($config['mongo'])
    ->setReturnType(EventAggregateRepositoryInterface::RETURN_AS_ARRAY)
    ->setElasticServer($config['elastic']);

// instantiate $eventRepresentation
$eventRepresentation = new EventRepresentation(
    $eventManager,
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

<?php
/**
 * This file is part of the EventStoreManager package.
 *
 * (c) Mauro Cassani<https://github.com/mauretto78>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use JMS\Serializer\SerializerBuilder;
use SimpleEventStoreManager\Application\EventQuery;
use SimpleEventStoreManager\Application\EventManager;
use SimpleEventStoreManager\Domain\Model\Event;
use SimpleEventStoreManager\Domain\Model\EventId;
use SimpleEventStoreManager\Infrastructure\DataTransformers\JsonEventDataTransformer;
use Symfony\Component\HttpFoundation\Request;

require __DIR__.'/../app/bootstrap.php';

$request = Request::createFromGlobals();

// instantiate $eventsQuery
$eventManager = new EventManager('mongo', $config['mongo'], [
    'elastic' => true,
    'elastic_hosts' => $config['elastic']
]);

$eventQuery = new EventQuery(
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
$response = $eventQuery->aggregate($request->query->get('aggregate'), $page);
$response->send();

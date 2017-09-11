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
use SimpleEventStoreManager\Application\Event\EventManager;
use SimpleEventStoreManager\Application\Event\EventRepresentation;
use SimpleEventStoreManager\Domain\Model\Contracts\EventStoreRepositoryInterface;
use SimpleEventStoreManager\Infrastructure\DataTransformers\JsonEventDataTransformer;
use Symfony\Component\HttpFoundation\Request;

require __DIR__.'/../app/bootstrap.php';

$request = Request::createFromGlobals();

// instantiate $eventsQuery
$eventManager = EventManager::build()
    ->setDriver('mongo')
    ->setConnection($config['mongo'])
    ->setReturnType(EventStoreRepositoryInterface::RETURN_AS_ARRAY)
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

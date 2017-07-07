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
use SimpleEventStoreManager\Application\EventsQuery;
use SimpleEventStoreManager\Application\EventsManager;
use SimpleEventStoreManager\Infrastructure\DataTransformer\JsonEventDataTransformer;

require __DIR__.'/../app/bootstrap.php';

// instantiate $eventsQuery
$eventsManager = new EventsManager('mongo', $config['mongo']);
$eventsQuery = new EventsQuery(
    $eventsManager->eventStore(),
    new JsonEventDataTransformer(
        SerializerBuilder::create()->build()
    )
);

// send Response
$page = (isset($_GET['pag'])) ? (int) $_GET['pag'] : 1;
$maxPerPage = 10;
$response = $eventsQuery->query($page, $maxPerPage);
$response->send();

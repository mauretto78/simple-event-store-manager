<?php
/**
 * This file is part of the EventStoreManager package.
 *
 * (c) Mauro Cassani<https://github.com/mauretto78>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use SimpleEventStoreManager\Application\StreamManager;

require __DIR__.'/../app/bootstrap.php';

$streamManager = new StreamManager('pdo', $config['pdo']);
$events = $streamManager->eventStore()->eventsInRangeDate();
$eventsCount = $streamManager->eventStore()->eventsCount();

$maxPerPage = 20;
$page = (isset($_GET['pag'])) ? (int) $_GET['pag'] : 1;
$total = $streamManager->eventStore()->eventsCount();
$numberOfPages = ceil($total / $maxPerPage);
$prevPage = ($page > 1 && $page <= $numberOfPages) ? $page - 1 : null;
$nextPage = ($page < $numberOfPages) ? ($page + 1) : null;

$paginatedEvents = array_slice($events, ($page - 1) * $maxPerPage, $maxPerPage);

$meta = [
    'page' => $page,
    'prev-page' => $prevPage,
    'next-page' => $nextPage,
    'number-of-pages' => $numberOfPages,
    'records-per-page' => $maxPerPage,
    'total' => $total,
];

header('Content-Type: application/json');
echo json_encode(
    [
        'meta' => $meta,
        'events' => $paginatedEvents,
    ]
);

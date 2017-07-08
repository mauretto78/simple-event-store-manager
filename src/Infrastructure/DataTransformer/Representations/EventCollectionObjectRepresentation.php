<?php
/**
 * This file is part of the Simple EventStore Manager package.
 *
 * (c) Mauro Cassani<https://github.com/mauretto78>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SimpleEventStoreManager\Infrastructure\DataTransformer\Representations;

class EventCollectionObjectRepresentation
{
    private $page;
    private $recordsPerPage;
    private $totalPages;
    private $totalCount;
    private $currentLink;
    private $prevLink;
    private $nextLink;
    private $lastLink;

    /**
     * EventsCollection constructor.
     *
     * @param $page
     * @param $recordsPerPage
     * @param $totalPages
     * @param $totalCount
     * @param $links
     */
    public function __construct(
        $page,
        $recordsPerPage,
        $totalPages,
        $totalCount,
        $links
    ) {
        $this->page = $page;
        $this->recordsPerPage = $recordsPerPage;
        $this->totalPages = $totalPages;
        $this->totalCount = $totalCount;
        $this->currentLink = $links['current'];
        $this->prevLink = $links['prev'];
        $this->nextLink = $links['next'];
        $this->lastLink = $links['last'];
    }

    /**
     * @var array
     */
    private $events;

    /**
     * @param EventObjectRepresentation $event
     */
    public function addEvent(EventObjectRepresentation $event)
    {
        $this->events[] = $event;
    }
}

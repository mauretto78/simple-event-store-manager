<?php
/**
 * This file is part of the EventStoreManager package.
 *
 * (c) Mauro Cassani<https://github.com/mauretto78>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SimpleEventStoreManager\Infrastructure\DataTransformer;

use SimpleEventStoreManager\Infrastructure\DataTransformer\Contracts\DataTransformerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use JMS\Serializer\Serializer;

class JsonEventDataTransformer implements DataTransformerInterface
{
    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var bool
     */
    private $paginationLink;

    /**
     * JsonEventDataTransformer constructor.
     *
     * @param Serializer $serializer
     * @param bool $paginationLink
     */
    public function __construct(Serializer $serializer, $paginationLink = false)
    {
        $this->serializer = $serializer;
        $this->paginationLink = $paginationLink;
    }

    /**
     * @param array $events
     * @param int $eventsCount
     * @param int $page
     * @param int $maxPerPage
     *
     * @return Response
     */
    public function transform($events, $eventsCount, $page, $maxPerPage)
    {
        $pageCount = count($events);
        $jsonResponse = new JsonResponse(
            $this->serializer->serialize(
                [
                    '_meta' => [
                        'page' => $page,
                        'records_per_page' => $maxPerPage,
                        'total_pages' => $numberOfPages = ceil($eventsCount/$maxPerPage),
                        'total_count' => $eventsCount
                    ],
                    '_links' => [
                        $this->calculateLinks($page, $numberOfPages)
                    ],
                    'events' => $this->convertEventsDataToArray($events)
                ], 'json'),
                $this->getHttpStatusCode($pageCount),
            [],
            true
        );

        // set infinite cache if page is complete
        if ($maxPerPage === $pageCount) {
            $jsonResponse
                ->setMaxAge(60 * 60 * 24 * 365)
                ->setSharedMaxAge(60 * 60 * 24 * 365);
        }

        return $jsonResponse;
    }

    /**
     * @param $events
     *
     * @return array
     */
    private function convertEventsDataToArray($events)
    {
        return array_map(
            function ($event) {
                return [
                    'id' => $event->id,
                    'name' => $event->name,
                    'body' => unserialize($event->body),
                    'occurred_on' => $event->occurred_on,
                ];
            },
            $events
        );
    }

    /**
     * @param int $currentPage
     * @param int $numberOfPages
     *
     * @return array
     */
    private function calculateLinks($currentPage, $numberOfPages)
    {
        $root = $this->rootLink();
        $prev = ($currentPage > 1) ? $currentPage - 1 : null;
        $next = ($currentPage < $numberOfPages) ? $currentPage + 1 : null;

        $separator = ($this->paginationLink) ? '/' : '?pag=';

        return [
            'current' => $root.$separator.$currentPage,
            'prev' => ($prev) ? $root.$separator.$prev : null,
            'next' => ($next) ? $root.$separator.$next : null,
            'last' => $root.$separator.$numberOfPages,
        ];
    }

    /**
     * rootLink
     */
    private function rootLink()
    {
        if (isset($_SERVER['HTTP_HOST'])) {
            return (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] .parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        }

        return (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . 'localhost';
    }

    /**
     * @param $pageCount
     *
     * @return int
     */
    private function getHttpStatusCode($pageCount)
    {
        if ($pageCount <= 0) {
            return Response::HTTP_NOT_FOUND;
        }

        return Response::HTTP_OK;
    }
}

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

use SimpleEventStoreManager\Domain\Model\Event;
use SimpleEventStoreManager\Infrastructure\DataTransformer\Contracts\DataTransformerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use JMS\Serializer\Serializer;

class JsonEventDataTransformer implements DataTransformerInterface
{
    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var bool
     */
    private $paginationLink;

    /**
     * JsonEventDataTransformer constructor.
     *
     * @param Serializer $serializer
     * @param Request $request
     * @param bool $paginationLink
     */
    public function __construct(Serializer $serializer, Request $request, $paginationLink = false)
    {
        $this->serializer = $serializer;
        $this->request = $request;
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
        $baseUrl = $this->getBaseUrl();
        $prev = ($currentPage > 1) ? $currentPage - 1 : null;
        $next = ($currentPage < $numberOfPages) ? $currentPage + 1 : null;

        $separator = ($this->paginationLink) ? '/' : '?page=';

        return [
            'current' => $baseUrl.$separator.$currentPage,
            'prev' => ($prev) ? $baseUrl.$separator.$prev : null,
            'next' => ($next) ? $baseUrl.$separator.$next : null,
            'last' => ($numberOfPages > 0) ? $baseUrl.$separator.$numberOfPages : null,
        ];
    }

    /**
     * @return string
     */
    private function getBaseUrl()
    {
        if ($this->request->getScheme()) {
            $url = (null !== $page = $this->request->attributes->get('page')) ? str_replace('/'.$page, '', $this->request->getUri()) : str_replace('/?'.$this->request->getQueryString(), '', $this->request->getUri());

            return $url;
        }

        return 'http://localhost';
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

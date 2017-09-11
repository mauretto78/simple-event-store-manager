<?php
/**
 * This file is part of the Simple EventStore Manager package.
 *
 * (c) Mauro Cassani<https://github.com/mauretto78>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SimpleEventStoreManager\Infrastructure\DataTransformers;

use SimpleEventStoreManager\Domain\Model\Contracts\EventInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use JMS\Serializer\Serializer;

abstract class AbstractEventDataTransformer
{
    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var bool
     */
    protected $paginationLink;

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
     * @param $events
     *
     * @return array
     */
    protected function convertEventsDataToArray($events)
    {
        return array_map(
            function (EventInterface $event) {
                return [
                    'uuid' => $event->uuid(),
                    'version' => $event->version(),
                    'payload' => $event->payload(),
                    'type' => $event->type(),
                    'body' => $event->body(),
                    'occurred_on' => $event->occurredOn(),
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
    protected function calculateLinks($currentPage, $numberOfPages)
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
    protected function getBaseUrl()
    {
        return (null !== $page = $this->request->attributes->get('page')) ? str_replace('/'.$page, '', $this->request->getUri()) : str_replace('/?'.$this->request->getQueryString(), '', $this->request->getUri());
    }

    /**
     * @param $pageCount
     *
     * @return int
     */
    protected function getHttpStatusCode($pageCount)
    {
        if ($pageCount <= 0) {
            return Response::HTTP_NOT_FOUND;
        }

        return Response::HTTP_OK;
    }
}

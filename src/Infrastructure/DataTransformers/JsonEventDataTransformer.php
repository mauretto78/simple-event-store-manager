<?php
/**
 * This file is part of the EventStoreManager package.
 *
 * (c) Mauro Cassani<https://github.com/mauretto78>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SimpleEventStoreManager\Infrastructure\DataTransformers;

use SimpleEventStoreManager\Infrastructure\DataTransformers\Contracts\DataTransformerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use JMS\Serializer\Serializer;

class JsonEventDataTransformer extends AbstractEventDataTransformer implements DataTransformerInterface
{
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
                    'events' => (is_array($events)) ? $events : $this->convertEventsDataToArray($events)
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
}

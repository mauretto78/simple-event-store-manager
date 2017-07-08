<?php
/**
 * This file is part of the Simple EventStore Manager package.
 *
 * (c) Mauro Cassani<https://github.com/mauretto78>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SimpleEventStoreManager\Infrastructure\DataTransformer;

use SimpleEventStoreManager\Infrastructure\DataTransformer\Contracts\DataTransformerInterface;
use SimpleEventStoreManager\Infrastructure\DataTransformer\Representations\EventCollectionObjectRepresentation;
use SimpleEventStoreManager\Infrastructure\DataTransformer\Representations\EventObjectRepresentation;
use SimpleEventStoreManager\Infrastructure\DataTransformer\Representations\EventsObjectRepresentation;
use Symfony\Component\HttpFoundation\Response;

class XMLEventDataTransformer extends AbstractEventDataTransformer implements DataTransformerInterface
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
        $eventsCollection = new EventCollectionObjectRepresentation(
            (int) $page,
            (int) $maxPerPage,
            (int) $numberOfPages = ceil($eventsCount/$maxPerPage),
            (int) $eventsCount,
            $this->calculateLinks($page, $numberOfPages)
        );
        foreach ($events as $event) {
            $eventsCollection->addEvent(new EventObjectRepresentation($event));
        }

        $response = new Response(
            $this->serializer->serialize(
                [
                    $eventsCollection
                ], 'xml'),
            $this->getHttpStatusCode($pageCount)
        );

        $response->headers->set('Content-type', 'text/xml');
        $response->setCharset('utf-8');

        // set infinite cache if page is complete
        if ($maxPerPage === $pageCount) {
            $response
                ->setMaxAge(60 * 60 * 24 * 365)
                ->setSharedMaxAge(60 * 60 * 24 * 365);
        }

        return $response;
    }
}

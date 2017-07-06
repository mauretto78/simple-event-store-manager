<?php
/**
 * This file is part of the EventStoreManager package.
 *
 * (c) Mauro Cassani<https://github.com/mauretto78>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SimpleEventStoreManager\Infrastructure\DataTransformer\Contracts;

use Symfony\Component\HttpFoundation\Response;

interface DataTransformerInterface
{
    /**
     * Transform $events array into a response
     *
     * @param array $events
     * @param int $eventsCount
     * @param int $page
     * @param int $maxPerPage
     *
     * @return Response
     */
    public function transform($events, $eventsCount, $page, $maxPerPage);
}

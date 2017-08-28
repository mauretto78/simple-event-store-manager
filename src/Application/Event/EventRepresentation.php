<?php
/**
 * This file is part of the Simple EventStore Manager package.
 *
 * (c) Mauro Cassani<https://github.com/mauretto78>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SimpleEventStoreManager\Application\Event;

use SimpleEventStoreManager\Infrastructure\DataTransformers\Contracts\DataTransformerInterface;
use Symfony\Component\HttpFoundation\Response;

class EventRepresentation
{
    /**
     * @var DataTransformerInterface
     */
    private $dataTransformer;

    /**
     * @var EventManager
     */
    private $eventManger;

    /**
     * @var EventQuery
     */
    private $eventQuery;

    /**
     * EventRepresentation constructor.
     * @param EventManager $eventManger
     * @param DataTransformerInterface $dataTransformer
     */
    public function __construct(
        EventManager $eventManger,
        DataTransformerInterface $dataTransformer
    ) {
        $this->eventManger = $eventManger;
        $this->eventQuery = new EventQuery($eventManger);
        $this->dataTransformer = $dataTransformer;
    }

    /**
     * @param int $page
     * @param int $maxPerPage
     *
     * @return Response
     */
    public function aggregate($aggregateName, $page = 1, $maxPerPage = 25)
    {
        return $this->dataTransformer->transform(
            $this->paginateAggregate($this->eventQuery->fromAggregate($aggregateName), $page, $maxPerPage),
            $this->eventQuery->streamCount($aggregateName),
            $page,
            $maxPerPage
        );
    }

    /**
     * @param $array
     * @param $page
     * @param $maxPerPage
     *
     * @return array
     */
    private function paginateAggregate($array, $page, $maxPerPage)
    {
        return array_slice($array, ($page - 1) * $maxPerPage, $maxPerPage);
    }
}

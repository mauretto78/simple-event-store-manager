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

class EventQuery
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
     * EventQuery constructor.
     * @param EventManager $eventManger
     * @param DataTransformerInterface $dataTransformer
     */
    public function __construct(
        EventManager $eventManger,
        DataTransformerInterface $dataTransformer
    ) {
        $this->eventManger = $eventManger;
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
            $this->eventManger->stream($aggregateName, $page, $maxPerPage),
            $this->eventManger->streamCount($aggregateName),
            $page,
            $maxPerPage
        );
    }
}

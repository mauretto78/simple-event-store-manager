<?php
/**
 * This file is part of the EventStoreManager package.
 *
 * (c) Mauro Cassani<https://github.com/mauretto78>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SimpleEventStoreManager\Application;

use SimpleEventStoreManager\Domain\Model\Contracts\EventStoreInterface;
use SimpleEventStoreManager\Infrastructure\DataTransformer\Contracts\DataTransformerInterface;
use Symfony\Component\HttpFoundation\Response;

class EventQuery
{
    /**
     * @var DataTransformerInterface
     */
    private $dataTransformer;

    /**
     * @var EventStoreInterface
     */
    private $eventStore;

    /**
     * EventQuery constructor.
     * @param EventStoreInterface $eventStore
     * @param DataTransformerInterface $dataTransformer
     */
    public function __construct(
        EventStoreInterface $eventStore,
        DataTransformerInterface $dataTransformer
    ) {
        $this->eventStore = $eventStore;
        $this->dataTransformer = $dataTransformer;
    }

    /**
     * @param int $page
     * @param int $maxPerPage
     *
     * @return Response
     */
    public function query($page = 1, $maxPerPage = 25)
    {
        return $this->dataTransformer->transform(
            $this->eventStore->all($page, $maxPerPage),
            $this->eventStore->eventsCount(),
            $page,
            $maxPerPage
        );
    }
}

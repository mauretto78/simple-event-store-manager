<?php
/**
 * This file is part of the EventStoreManager package.
 *
 * (c) Mauro Cassani<https://github.com/mauretto78>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use JMS\Serializer\SerializerBuilder;
use SimpleEventStoreManager\Application\EventsQuery;

use SimpleEventStoreManager\Application\EventsManager;
use SimpleEventStoreManager\Domain\Model\Event;
use SimpleEventStoreManager\Domain\Model\EventId;
use SimpleEventStoreManager\Infrastructure\DataTransformer\JsonEventDataTransformer;
use SimpleEventStoreManager\Tests\BaseTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EventsQueryTest extends BaseTestCase
{
    /**
     * @test
     */
    public function it_should_store_events_perform_queries_and_retrive_json_response()
    {
        $streamManager = new EventsManager('mongo', $this->mongo_parameters);
        $eventStore = $streamManager->eventStore();

        // store events
        $eventId = new EventId();
        $name = 'Doman\\Model\\SomeEvent';
        $body = [
            'id' => 1,
            'title' => 'Lorem Ipsum',
            'text' => 'Dolor lorem ipso facto dixit'
        ];

        $event = new Event(
            $eventId,
            $name,
            $body
        );

        $eventId2 = new EventId();
        $name2 = 'Doman\\Model\\SomeEvent2';
        $body2 = [
            'id' => 2,
            'title' => 'Lorem Ipsum',
            'text' => 'Dolor lorem ipso facto dixit'
        ];

        $event2 = new Event(
            $eventId2,
            $name2,
            $body2
        );

        $eventStore->store($event);
        $eventStore->store($event2);

        // query events
        $eventsQuery = new EventsQuery(
            $streamManager->eventStore(),
            new JsonEventDataTransformer(
                SerializerBuilder::create()->build(),
                Request::createFromGlobals()
            )
        );

        $response = $eventsQuery->query(1, 1);
        $content = json_decode($response->getContent());

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals($response->headers->get('content-type'), 'application/json');
        $this->assertEquals($response->headers->get('cache-control'), 'max-age=31536000, public, s-maxage=31536000');
        $this->assertEquals(2, $content->_meta->total_count);

        $response = $eventsQuery->query(5);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }
}

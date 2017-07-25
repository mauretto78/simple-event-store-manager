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
use SimpleEventStoreManager\Application\EventQuery;

use SimpleEventStoreManager\Application\EventManager;
use SimpleEventStoreManager\Domain\Model\Event;
use SimpleEventStoreManager\Domain\Model\EventId;
use SimpleEventStoreManager\Infrastructure\DataTransformers\JsonEventDataTransformer;
use SimpleEventStoreManager\Infrastructure\DataTransformers\XmlEventDataTransformer;
use SimpleEventStoreManager\Infrastructure\DataTransformers\YamlEventDataTransformer;
use SimpleEventStoreManager\Tests\BaseTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Yaml\Yaml;

class EventQueryTest extends BaseTestCase
{
    /**
     * @var EventManager
     */
    private $eventManager;


    public function setUp()
    {
        parent::setUp();

        $this->eventManager = new EventManager('mongo', $this->mongo_parameters);

        $eventId = new EventId();
        $name = 'Doman\\Model\\SomeEvent';
        $body = [
            'id' => 1,
            'title' => 'Lorem Ipsum',
            'text' => 'Dolor lorem ipso facto dixit'
        ];

        $eventId2 = new EventId();
        $name2 = 'Doman\\Model\\SomeEvent2';
        $body2 = [
            'id' => 2,
            'title' => 'Lorem Ipsum',
            'text' => 'Dolor lorem ipso facto dixit'
        ];

        $event = new Event(
            $eventId,
            $name,
            $body
        );
        $event2 = new Event(
            $eventId2,
            $name2,
            $body2
        );

        $this->eventManager->storeEvents(
            'Dummy Aggregate',
            [
                $event,
                $event2
            ]
        );
    }

    /**
     * @test
     */
    public function it_should_store_events_perform_queries_and_retrive_json_response()
    {
        // json representation events
        $eventQuery = new EventQuery(
            $this->eventManager,
            new JsonEventDataTransformer(
                SerializerBuilder::create()->build(),
                Request::createFromGlobals()
            )
        );

        $response = $eventQuery->aggregate('Dummy Aggregate', 1, 1);
        $content = json_decode($response->getContent());

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals($response->headers->get('content-type'), 'application/json');
        $this->assertEquals($response->headers->get('cache-control'), 'max-age=31536000, public, s-maxage=31536000');
        $this->assertEquals(2, $content->_meta->total_count);

        $response = $eventQuery->aggregate('Dummy Aggregate',5);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function it_should_store_events_perform_queries_and_retrive_xml_response()
    {
        // xml representation events
        $eventsQuery = new EventQuery(
            $this->eventManager,
            new XmlEventDataTransformer(
                SerializerBuilder::create()->build(),
                Request::createFromGlobals()
            )
        );

        $response = $eventsQuery->aggregate('Dummy Aggregate', 1, 1);
        $content = simplexml_load_string($response->getContent());

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals($response->headers->get('content-type'), 'text/xml');
        $this->assertEquals($response->headers->get('cache-control'), 'max-age=31536000, public, s-maxage=31536000');
        $this->assertEquals(2, (string) $content->entry->total_count);

        $response = $eventsQuery->aggregate('Dummy Aggregate',5);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function it_should_store_events_perform_queries_and_retrive_yaml_response()
    {
        // yaml representation events
        $eventsQuery = new EventQuery(
            $this->eventManager,
            new YamlEventDataTransformer(
                SerializerBuilder::create()->build(),
                Request::createFromGlobals()
            )
        );

        $response = $eventsQuery->aggregate('Dummy Aggregate', 1, 1);
        $content = Yaml::parse($response->getContent());

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals($response->headers->get('content-type'), 'text/yaml');
        $this->assertEquals($response->headers->get('cache-control'), 'max-age=31536000, public, s-maxage=31536000');
        $this->assertEquals(1, $content['_meta']['page']);
        $this->assertEquals(1, $content['_meta']['records_per_page']);
        $this->assertEquals(2, $content['_meta']['total_pages']);
        $this->assertEquals(2, $content['_meta']['total_count']);

        $response = $eventsQuery->aggregate('Dummy Aggregate',5);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }
}

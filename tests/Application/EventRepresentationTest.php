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
use SimpleEventStoreManager\Application\Event\EventRepresentation;

use SimpleEventStoreManager\Application\Event\EventManager;
use SimpleEventStoreManager\Domain\Model\Contracts\EventAggregateRepositoryInterface;
use SimpleEventStoreManager\Domain\Model\Event;
use SimpleEventStoreManager\Domain\Model\EventId;
use SimpleEventStoreManager\Infrastructure\DataTransformers\JsonEventDataTransformer;
use SimpleEventStoreManager\Infrastructure\DataTransformers\XmlEventDataTransformer;
use SimpleEventStoreManager\Infrastructure\DataTransformers\YamlEventDataTransformer;
use SimpleEventStoreManager\Tests\BaseTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Yaml\Yaml;

class EventRepresentationTest extends BaseTestCase
{

    /**
     * @var EventManager
     */
    private $emAsArray;

    /**
     * @var EventManager
     */
    private $emAsObject;

    public function setUp()
    {
        parent::setUp();

        $eventManager = EventManager::build()
            ->setDriver('mongo')
            ->setConnection($this->mongo_parameters);

        $name = 'Doman\\Model\\SomeEvent';
        $body = [
            'id' => 1,
            'title' => 'Lorem Ipsum',
            'text' => 'Dolor lorem ipso facto dixit'
        ];

        $name2 = 'Doman\\Model\\SomeEvent2';
        $body2 = [
            'id' => 2,
            'title' => 'Lorem Ipsum',
            'text' => 'Dolor lorem ipso facto dixit'
        ];

        $event = new Event(
            $name,
            $body
        );
        $event2 = new Event(
            $name2,
            $body2
        );

        $eventManager->storeEvents(
            'Dummy EventAggregate',
            [
                $event,
                $event2
            ]
        );

        $this->emAsArray = $eventManager->setReturnType(EventAggregateRepositoryInterface::RETURN_AS_ARRAY);
        $this->emAsObject = $eventManager->setReturnType(EventAggregateRepositoryInterface::RETURN_AS_OBJECT);
    }

    /**
     * @test
     */
    public function it_should_store_events_perform_queries_and_retrive_json_response()
    {
        $eventQueryAsArray = new EventRepresentation(
            $this->emAsArray,
            new JsonEventDataTransformer(
                SerializerBuilder::create()->build(),
                Request::createFromGlobals()
            )
        );

        $eventQueryAsObject = new EventRepresentation(
            $this->emAsObject,
            new JsonEventDataTransformer(
                SerializerBuilder::create()->build(),
                Request::createFromGlobals()
            )
        );

        $eventQueries = [$eventQueryAsArray, $eventQueryAsObject];

        /** @var EventRepresentation $eventQuery */
        foreach ($eventQueries as $eventQuery) {
            $response = $eventQuery->aggregate('Dummy EventAggregate', 1, 1);
            $content = json_decode($response->getContent());

            $this->assertInstanceOf(Response::class, $response);
            $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
            $this->assertEquals($response->headers->get('content-type'), 'application/json');
            $this->assertEquals($response->headers->get('cache-control'), 'max-age=31536000, public, s-maxage=31536000');
            $this->assertEquals(2, $content->_meta->total_count);

            $response = $eventQuery->aggregate('Dummy EventAggregate', 5);
            $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        }
    }

    /**
     * @test
     */
    public function it_should_store_events_perform_queries_and_retrive_xml_response()
    {
        $eventQueryAsArray = new EventRepresentation(
            $this->emAsArray,
            new XmlEventDataTransformer(
                SerializerBuilder::create()->build(),
                Request::createFromGlobals()
            )
        );

        $eventQueryAsObject = new EventRepresentation(
            $this->emAsObject,
            new XmlEventDataTransformer(
                SerializerBuilder::create()->build(),
                Request::createFromGlobals()
            )
        );

        $eventQueries = [$eventQueryAsArray, $eventQueryAsObject];

        /** @var EventRepresentation $eventQuery */
        foreach ($eventQueries as $eventQuery) {
            $response = $eventQuery->aggregate('Dummy EventAggregate', 1, 1);
            $content = simplexml_load_string($response->getContent());

            $this->assertInstanceOf(Response::class, $response);
            $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
            $this->assertEquals($response->headers->get('content-type'), 'text/xml');
            $this->assertEquals($response->headers->get('cache-control'), 'max-age=31536000, public, s-maxage=31536000');
            $this->assertEquals(2, (string) $content->entry->total_count);

            $response = $eventQuery->aggregate('Dummy EventAggregate', 5);
            $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        }
    }

    /**
     * @test
     */
    public function it_should_store_events_perform_queries_and_retrive_yaml_response()
    {
        $eventQueryAsArray = new EventRepresentation(
            $this->emAsArray,
            new YamlEventDataTransformer(
                SerializerBuilder::create()->build(),
                Request::createFromGlobals()
            )
        );

        $eventQueryAsObject = new EventRepresentation(
            $this->emAsObject,
            new YamlEventDataTransformer(
                SerializerBuilder::create()->build(),
                Request::createFromGlobals()
            )
        );

        $eventQueries = [$eventQueryAsArray, $eventQueryAsObject];

        /** @var EventRepresentation $eventQuery */
        foreach ($eventQueries as $eventQuery) {
            $response = $eventQuery->aggregate('Dummy EventAggregate', 1, 1);
            $content = Yaml::parse($response->getContent());

            $this->assertInstanceOf(Response::class, $response);
            $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
            $this->assertEquals($response->headers->get('content-type'), 'text/yaml');
            $this->assertEquals($response->headers->get('cache-control'), 'max-age=31536000, public, s-maxage=31536000');
            $this->assertEquals(1, $content['_meta']['page']);
            $this->assertEquals(1, $content['_meta']['records_per_page']);
            $this->assertEquals(2, $content['_meta']['total_pages']);
            $this->assertEquals(2, $content['_meta']['total_count']);

            $response = $eventQuery->aggregate('Dummy EventAggregate', 5);
            $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        }
    }
}

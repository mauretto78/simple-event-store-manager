<?php
/**
 * This file is part of the EventStoreManager package.
 *
 * (c) Mauro Cassani<https://github.com/mauretto78>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use SimpleEventStoreManager\Domain\Model\Event;
use SimpleEventStoreManager\Domain\Model\EventId;
use SimpleEventStoreManager\Tests\BaseTestCase;

class EventTest extends BaseTestCase
{
    /**
     * @test
     */
    public function create_event_and_stores_the_values()
    {
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

        $this->assertEquals($eventId, $eventId->id());
        $this->assertEquals($eventId, $event->id());
        $this->assertEquals($name, $event->name());
        $this->assertEquals($body, unserialize($event->body()));
        $this->assertInstanceOf(DateTimeImmutable::class, $event->occurredOn());
    }
}

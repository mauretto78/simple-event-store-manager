<?php
/**
 * This file is part of the EventStoreManager package.
 *
 * (c) Mauro Cassani<https://github.com/mauretto78>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SimpleEventStoreManager\Domain\Model;

use Ramsey\Uuid\Uuid;

class EventId
{
    /**
     * @var string
     */
    private $id;

    /**
     * EventId constructor.
     *
     * @param null $id
     */
    public function __construct($id = null)
    {
        $this->id = ($id) ?: Uuid::uuid4()->toString();
    }

    /**
     * @return mixed
     */
    public function id()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->id();
    }
}

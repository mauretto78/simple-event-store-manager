<?php
/**
 * This file is part of the EventStoreManager package.
 *
 * (c) Mauro Cassani<https://github.com/mauretto78>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SimpleEventStoreManager\Infrastructure\Drivers;

use SimpleEventStoreManager\Infrastructure\Drivers\Contracts\DriverInterface;

class InMemoryDriver implements DriverInterface
{
    /**
     * InMemoryDriver constructor.
     */
    public function __construct()
    {
        $this->connect();
    }

    /**
     * @codeCoverageIgnore
     *
     * @return bool
     */
    public function check()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function connect()
    {
        return true;
    }

    /**
     * @return mixed
     */
    public function instance()
    {
        return $this;
    }
}

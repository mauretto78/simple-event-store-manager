<?php
/**
 * This file is part of the EventStoreManager package.
 *
 * (c) Mauro Cassani<https://github.com/mauretto78>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SimpleEventStoreManager\Tests;

use PHPUnit\Framework\TestCase;

class BaseTestCase extends TestCase
{
    /**
     * @var array
     */
    protected $mongo_parameters;

    /**
     * @var array
     */
    protected $pdo_parameters;

    /**
     * @var array
     */
    protected $redis_parameters;

    /**
     * setup configuration.
     */
    public function setUp()
    {
        $config = require __DIR__.'/../app/bootstrap.php';

        $this->mongo_parameters= $config['mongo'];
        $this->pdo_parameters = $config['pdo'];
        $this->redis_parameters = $config['redis'];
    }
}
